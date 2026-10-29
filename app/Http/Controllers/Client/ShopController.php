<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Hiển thị danh mục sản phẩm (Shop/All Products) từ Database.
     * Eager Loading with(['category', 'brand']) để tối ưu hóa truy vấn và chống N+1.
     */
    public function index(Request $request): View
    {
        // 1. Khởi tạo query sản phẩm đang hoạt động kèm Eager Loading quan hệ
        $query = Product::query()
            ->active()
            ->with(['category', 'brand']);

        // 2. Lấy danh sách Categories trực tiếp từ Database kèm số lượng sản phẩm
        $productCounts = Product::active()
            ->selectRaw('category_id, count(*) as count')
            ->groupBy('category_id')
            ->pluck('count', 'category_id');

        $categories = Category::query()
            ->with(['children'])
            ->orderBy('name')
            ->get()
            ->map(function ($cat) use ($productCounts) {
                $childIds = $cat->children->pluck('id');
                $count = ($productCounts[$cat->id] ?? 0) + $childIds->sum(fn ($id) => $productCounts[$id] ?? 0);

                return [
                    'id'    => $cat->id,
                    'name'  => $cat->name,
                    'slug'  => $cat->slug,
                    'count' => $count,
                ];
            })
            ->filter(fn ($c) => $c['count'] > 0)
            ->values()
            ->all();

        // Fallback nếu chưa có sản phẩm nào được gán thì vẫn hiển thị các danh mục hiện có
        if (empty($categories)) {
            $categories = Category::query()
                ->orderBy('name')
                ->get()
                ->map(fn ($cat) => [
                    'id'    => $cat->id,
                    'name'  => $cat->name,
                    'slug'  => $cat->slug,
                    'count' => 0,
                ])
                ->values()
                ->all();
        }

        // 3. Lấy danh sách Brands trực tiếp từ Database kèm số lượng sản phẩm
        $brands = Brand::query()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->get()
            ->map(fn ($brand) => [
                'id'    => $brand->id,
                'name'  => $brand->name,
                'slug'  => $brand->slug,
                'count' => $brand->products_count,
            ])
            ->values()
            ->all();

        // 4. Lọc theo Categories (Hỗ trợ cả mảng categories[] và chuỗi đơn category từ banner trang chủ)
        $selectedCategories = (array) $request->input('categories', []);
        if ($request->filled('category') && empty($selectedCategories)) {
            $selectedCategories = [$request->input('category')];
        }

        if (!empty($selectedCategories)) {
            $categoryIds = Category::where(function ($q) use ($selectedCategories) {
                $q->whereIn('name', $selectedCategories)
                  ->orWhereIn('slug', $selectedCategories)
                  ->orWhereIn('id', $selectedCategories);
            })->pluck('id');

            $allCategoryIds = Category::whereIn('id', $categoryIds)
                ->orWhereIn('parent_id', $categoryIds)
                ->pluck('id')
                ->all();

            if (!empty($allCategoryIds)) {
                $query->whereIn('category_id', $allCategoryIds);
            } else {
                // Hỗ trợ tìm kiếm theo từ khóa danh mục (vd: 'headphones', 'earphone', 'laptop', 'mouse'...)
                $query->whereHas('category', function ($q) use ($selectedCategories) {
                    $q->where(function ($sq) use ($selectedCategories) {
                        foreach ($selectedCategories as $term) {
                            $sq->orWhere('name', 'like', "%{$term}%")
                               ->orWhere('slug', 'like', "%{$term}%");
                        }
                    });
                });
            }
        }

        // 5. Lọc theo Brands (Hỗ trợ cả mảng brands[] và chuỗi đơn brand)
        $selectedBrands = (array) $request->input('brands', []);
        if ($request->filled('brand') && empty($selectedBrands)) {
            $selectedBrands = [$request->input('brand')];
        }

        if (!empty($selectedBrands)) {
            $brandIds = Brand::whereIn('name', $selectedBrands)
                ->orWhereIn('slug', $selectedBrands)
                ->orWhereIn('id', $selectedBrands)
                ->pluck('id');

            if ($brandIds->isNotEmpty()) {
                $query->whereIn('brand_id', $brandIds);
            } else {
                $query->whereHas('brand', function ($q) use ($selectedBrands) {
                    $q->whereIn('name', $selectedBrands);
                });
            }
        }

        // 6. Lọc theo Khoảng giá min_price và max_price
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $query->where(function ($q) use ($minPrice) {
                $q->where(function ($sq) use ($minPrice) {
                    $sq->whereNotNull('sale_price')
                       ->where('sale_price', '>=', (int) $minPrice);
                })->orWhere(function ($sq) use ($minPrice) {
                    $sq->whereNull('sale_price')
                       ->where('price', '>=', (int) $minPrice);
                });
            });
        }

        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $query->where(function ($q) use ($maxPrice) {
                $q->where(function ($sq) use ($maxPrice) {
                    $sq->whereNotNull('sale_price')
                       ->where('sale_price', '<=', (int) $maxPrice);
                })->orWhere(function ($sq) use ($maxPrice) {
                    $sq->whereNull('sale_price')
                       ->where('price', '<=', (int) $maxPrice);
                });
            });
        }

        // 7. Lọc theo tình trạng còn hàng (Stock Status)
        $stockStatus = $request->input('stock_status');
        if ($request->has('in_stock') && !$request->has('stock_status')) {
            $stockStatus = $request->boolean('in_stock') ? 'in_stock' : null;
        }

        if ($stockStatus === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        }

        // 8. Tìm kiếm theo từ khóa (name, sku, description, brand, category)
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        // 9. Sắp xếp kết quả (Sort)
        $sort = $request->input('sort', 'newest');
        if ($sort === 'price_asc') {
            $query->orderByRaw('COALESCE(sale_price, price) ASC');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw('COALESCE(sale_price, price) DESC');
        } elseif ($sort === 'popular' || $sort === 'best_selling') {
            $query->withSum('orderItems', 'quantity')
                  ->orderByDesc('order_items_sum_quantity')
                  ->orderByDesc('stock_quantity')
                  ->latest('id');
        } else {
            // Mặc định: Mới nhất
            $query->latest('id');
        }

        // 10. Phân trang chuẩn 12 sản phẩm/trang kèm giữ nguyên Query String
        $perPage = 12;
        $products = $query->paginate($perPage)->withQueryString();
        $totalProducts = $products->total();

        return view('client.shop.index', compact(
            'categories',
            'brands',
            'products',
            'selectedCategories',
            'selectedBrands',
            'minPrice',
            'maxPrice',
            'stockStatus',
            'search',
            'sort',
            'totalProducts'
        ));
    }

    /**
     * Hiển thị chi tiết 1 sản phẩm (Product Detail) từ Database.
     * Eager Loading with(['category', 'brand', 'images', 'reviews.user']) chống N+1.
     */
    public function show(string $slug): View
    {
        $dbProduct = Product::query()
            ->with(['category', 'brand', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $thumb = $dbProduct->thumbnail 
            ? (str_starts_with($dbProduct->thumbnail, 'http') 
                ? $dbProduct->thumbnail 
                : (str_starts_with($dbProduct->thumbnail, ['images/', 'storage/']) 
                    ? asset($dbProduct->thumbnail) 
                    : asset('storage/' . $dbProduct->thumbnail)))
            : asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png');

        $gallery = collect([$thumb]);
        foreach ($dbProduct->images as $img) {
            $gallery->push(str_starts_with($img->path, 'http') 
                ? $img->path 
                : (str_starts_with($img->path, ['images/', 'storage/']) 
                    ? asset($img->path) 
                    : asset('storage/' . $img->path)));
        }

        $reviewsCount = $dbProduct->reviews->count();
        $averageRating = $reviewsCount > 0 
            ? number_format($dbProduct->reviews->avg('rating'), 1) 
            : '4.9';

        $product = [
            'id'             => $dbProduct->id,
            'name'           => $dbProduct->name,
            'slug'           => $dbProduct->slug,
            'sku'            => $dbProduct->sku,
            'category'       => $dbProduct->category?->name ?? 'Phụ kiện',
            'brand'          => $dbProduct->brand?->name ?? 'GearHub',
            'price'          => (int) $dbProduct->price,
            'sale_price'     => $dbProduct->sale_price ? (int) $dbProduct->sale_price : null,
            'rating'         => $averageRating,
            'reviews_count'  => $reviewsCount ?: 128,
            'description'    => $dbProduct->description ?? 'Sản phẩm gaming gear và phụ kiện chính hãng, phân phối bởi GearHub.',
            'thumbnail'      => $thumb,
            'gallery'        => $gallery->unique()->values()->all(),
            'stock_quantity' => (int) $dbProduct->stock_quantity,
            'specs'          => is_array($dbProduct->specs) ? $dbProduct->specs : (json_decode($dbProduct->specs, true) ?? []),
        ];

        // Lấy 4 sản phẩm liên quan trong cùng danh mục, Eager Load category & brand chống N+1
        $related = Product::query()
            ->with(['category', 'brand'])
            ->where('category_id', $dbProduct->category_id)
            ->where('id', '!=', $dbProduct->id)
            ->active()
            ->take(4)
            ->get();

        if ($related->count() < 4) {
            $more = Product::query()
                ->with(['category', 'brand'])
                ->where('id', '!=', $dbProduct->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->active()
                ->take(4 - $related->count())
                ->get();
            $related = $related->merge($more);
        }

        $relatedProducts = $related->map(fn ($p) => [
            'id'         => $p->id,
            'name'       => $p->name,
            'slug'       => $p->slug,
            'category'   => $p->category?->name ?? 'Phụ kiện',
            'brand'      => $p->brand?->name ?? 'GearHub',
            'price'      => (int) $p->price,
            'sale_price' => $p->sale_price ? (int) $p->sale_price : null,
            'thumbnail'  => $p->thumbnail 
                ? (str_starts_with($p->thumbnail, 'http') 
                    ? $p->thumbnail 
                    : (str_starts_with($p->thumbnail, ['images/', 'storage/']) 
                        ? asset($p->thumbnail) 
                        : asset('storage/' . $p->thumbnail))) 
                : asset('images/hero-headphone.png'),
            'rating'     => '4.8',
        ]);

        return view('client.shop.show', compact('product', 'relatedProducts'));
    }
}
