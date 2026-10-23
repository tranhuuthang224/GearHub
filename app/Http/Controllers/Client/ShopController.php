<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductCatalog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Display the Product Catalog (Shop/All Products) matching Figma Page 4.
     */
    public function index(Request $request): View
    {
        $allProducts = ProductCatalog::all();

        // Danh mục tự động tổng hợp từ kho sản phẩm thực tế
        $categories = collect($allProducts)
            ->groupBy('category')
            ->map(fn($group, $cat) => [
                'name' => $cat,
                'slug' => $cat,
                'count' => $group->count(),
            ])
            ->values()
            ->all();

        // Thương hiệu tự động tổng hợp từ kho sản phẩm thực tế
        $brands = collect($allProducts)
            ->pluck('brand')
            ->unique()
            ->sort()
            ->map(fn($b) => ['name' => $b, 'slug' => $b])
            ->values()
            ->all();

        // Lọc sản phẩm theo query params (Categories, Brands, Search, Sort)
        $selectedCategories = (array) $request->input('categories', []);
        $selectedBrands = (array) $request->input('brands', []);
        $search = $request->input('search');
        $sort = $request->input('sort', 'newest');
        $maxPrice = $request->input('max_price');

        $filteredProducts = collect($allProducts);

        if (!empty($selectedCategories)) {
            $filteredProducts = $filteredProducts->whereIn('category', $selectedCategories);
        }

        if (!empty($selectedBrands)) {
            $filteredProducts = $filteredProducts->whereIn('brand', $selectedBrands);
        }

        if ($maxPrice && is_numeric($maxPrice)) {
            $filteredProducts = $filteredProducts->filter(function ($item) use ($maxPrice) {
                $effectivePrice = !empty($item['sale_price']) ? (int) $item['sale_price'] : (int) $item['price'];
                return $effectivePrice <= (int) $maxPrice;
            });
        }

        if ($search) {
            $filteredProducts = $filteredProducts->filter(function ($item) use ($search) {
                return stripos($item['name'], $search) !== false 
                    || stripos($item['sku'] ?? '', $search) !== false
                    || stripos($item['brand'] ?? '', $search) !== false;
            });
        }

        if ($sort === 'price_asc') {
            $filteredProducts = $filteredProducts->sortBy(fn($p) => $p['sale_price'] ?? $p['price']);
        } elseif ($sort === 'price_desc') {
            $filteredProducts = $filteredProducts->sortByDesc(fn($p) => $p['sale_price'] ?? $p['price']);
        }

        $totalProducts = $filteredProducts->count();
        $perPage = 12; // 12 sản phẩm mỗi trang (4 hàng x 3 cột)
        $currentPage = max(1, (int) $request->input('page', 1));
        $currentItems = $filteredProducts->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $products = new LengthAwarePaginator(
            $currentItems,
            $totalProducts,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('client.shop.index', compact(
            'categories',
            'brands',
            'products',
            'selectedCategories',
            'selectedBrands',
            'search',
            'sort',
            'totalProducts'
        ));
    }

    /**
     * Display Single Product Detail (Figma Page 5).
     */
    public function show(string $slug): View
    {
        $dbProduct = null;
        try {
            $dbProduct = Product::with(['category', 'brand', 'images'])->where('slug', $slug)->first();
        } catch (\Throwable $e) {
            // Database not yet migrated or connection unavailable, fallback to catalog
            $dbProduct = null;
        }

        if ($dbProduct) {
            $thumb = $dbProduct->thumbnail 
                ? (str_starts_with($dbProduct->thumbnail, 'http') ? $dbProduct->thumbnail : asset('storage/' . $dbProduct->thumbnail))
                : asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png');

            $gallery = collect([$thumb]);
            foreach ($dbProduct->images as $img) {
                $gallery->push(str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path));
            }

            $product = [
                'id' => $dbProduct->id,
                'name' => $dbProduct->name,
                'slug' => $dbProduct->slug,
                'sku' => $dbProduct->sku,
                'category' => $dbProduct->category?->name ?? 'Phụ kiện',
                'brand' => $dbProduct->brand?->name ?? 'GearHub',
                'price' => (int) $dbProduct->price,
                'sale_price' => $dbProduct->sale_price ? (int) $dbProduct->sale_price : null,
                'rating' => '4.9',
                'reviews_count' => 128,
                'description' => $dbProduct->description ?? 'Sản phẩm phụ kiện công nghệ chính hãng, phân phối bởi GearHub.',
                'thumbnail' => $thumb,
                'gallery' => $gallery->unique()->values()->all(),
                'stock_quantity' => (int) $dbProduct->stock_quantity,
                'specs' => is_array($dbProduct->specs) ? $dbProduct->specs : (json_decode($dbProduct->specs, true) ?? []),
            ];

            $related = Product::where('category_id', $dbProduct->category_id)
                ->where('id', '!=', $dbProduct->id)
                ->where('is_active', true)
                ->take(4)
                ->get();

            if ($related->count() < 4) {
                $more = Product::where('id', '!=', $dbProduct->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->where('is_active', true)
                    ->take(4 - $related->count())
                    ->get();
                $related = $related->merge($more);
            }

            $relatedProducts = $related->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'category' => $p->category?->name ?? 'Phụ kiện',
                'brand' => $p->brand?->name ?? 'GearHub',
                'price' => (int) $p->price,
                'sale_price' => $p->sale_price ? (int) $p->sale_price : null,
                'thumbnail' => $p->thumbnail ? (str_starts_with($p->thumbnail, 'http') ? $p->thumbnail : asset('storage/' . $p->thumbnail)) : asset('images/hero-headphone.png'),
                'rating' => '4.8',
            ]);
        } else {
            // Tra cứu từ danh mục thực tế ProductCatalog
            $matched = ProductCatalog::findBySlug($slug) ?? ProductCatalog::all()[0];

            $product = [
                'id' => $matched['id'],
                'name' => $matched['name'],
                'slug' => $matched['slug'],
                'sku' => $matched['sku'] ?? 'GEAR-ITEM',
                'category' => $matched['category'],
                'brand' => $matched['brand'],
                'price' => (int) $matched['price'],
                'sale_price' => !empty($matched['sale_price']) ? (int) $matched['sale_price'] : null,
                'rating' => $matched['rating'] ?? '4.9',
                'reviews_count' => $matched['reviews_count'] ?? 64,
                'description' => $matched['description'] ?? 'Sản phẩm gaming gear và phụ kiện chính hãng, phân phối bởi GearHub.',
                'thumbnail' => $matched['thumbnail'],
                'gallery' => $matched['gallery'] ?? [$matched['thumbnail']],
                'stock_quantity' => (int) ($matched['stock_quantity'] ?? 15),
                'specs' => $matched['specs'] ?? [],
            ];

            // Tìm 4 sản phẩm liên quan trong cùng danh mục
            $related = collect(ProductCatalog::all())
                ->where('category', $matched['category'])
                ->where('id', '!=', $matched['id'])
                ->take(4);

            if ($related->count() < 4) {
                $more = collect(ProductCatalog::all())
                    ->where('id', '!=', $matched['id'])
                    ->whereNotIn('id', $related->pluck('id'))
                    ->take(4 - $related->count());
                $related = $related->merge($more);
            }

            $relatedProducts = $related->map(fn($p) => [
                'id' => $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'category' => $p['category'],
                'brand' => $p['brand'],
                'price' => (int) $p['price'],
                'sale_price' => !empty($p['sale_price']) ? (int) $p['sale_price'] : null,
                'thumbnail' => $p['thumbnail'],
                'rating' => $p['rating'] ?? '4.8',
            ])->values()->all();
        }

        return view('client.shop.show', compact('product', 'relatedProducts'));
    }
}
