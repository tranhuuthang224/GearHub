<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CompareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    /**
     * Giới hạn số lượng sản phẩm so sánh tối đa cùng lúc.
     */
    public const MAX_COMPARE_ITEMS = 4;

    public function __construct(
        protected ?CompareService $compareService = null
    ) {
        $this->compareService = $compareService ?? app(CompareService::class);
    }

    /**
     * Hiển thị trang Bảng so sánh thông số kỹ thuật đa cột.
     */
    public function index(Request $request): View|JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $compareSession = session('compare_items', []);

        // Hỗ trợ Preset so sánh nhanh theo ngành hàng (Màn hình, Bàn phím cơ, Chuột gaming)
        if ($request->has('preset') && empty($compareSession)) {
            $preset = $request->input('preset');
            $catSlug = match ($preset) {
                'monitors', 'man-hinh'   => 'man-hinh',
                'keyboards', 'ban-phim'  => 'ban-phim',
                'mice', 'chuot'          => 'chuot',
                default                  => null,
            };

            if ($catSlug) {
                $sampleProducts = Product::query()
                    ->active()
                    ->with(['category.parent', 'brand', 'reviews'])
                    ->whereHas('category', function ($q) use ($catSlug) {
                        $q->where('slug', 'like', "%{$catSlug}%")
                          ->orWhereHas('parent', fn ($pq) => $pq->where('slug', 'like', "%{$catSlug}%"));
                    })
                    ->latest('id')
                    ->take(2)
                    ->get();

                if ($sampleProducts->count() >= 2) {
                    $newCompare = [];
                    foreach ($sampleProducts as $sp) {
                        $newCompare[$sp->id] = [
                            'id'              => $sp->id,
                            'name'            => $sp->name,
                            'slug'            => $sp->slug,
                            'price'           => $sp->price,
                            'sale_price'      => $sp->sale_price,
                            'effective_price' => $sp->effective_price,
                            'thumbnail'       => $sp->thumbnail ? asset($sp->thumbnail) : 'https://placehold.co/100x100/png?text=GearHub',
                            'brand'           => $sp->brand?->name,
                            'category'        => $sp->category?->name,
                        ];
                    }
                    session(['compare_items' => $newCompare]);
                    return redirect()->route('compare.index')->with('success', 'Đã tải 2 sản phẩm mẫu vào bảng so sánh đối chiếu thông số!');
                }
            }
        }
        
        // Hỗ trợ cả mảng ID [1, 2] và mảng associative [1 => [...]]
        if (array_is_list($compareSession)) {
            $productIds = $compareSession;
        } else {
            $productIds = array_keys($compareSession);
        }

        $products = collect();
        $specsMatrix = [];
        $allSpecKeys = [];
        $diffCount = 0;
        $rootCategory = null;

        if (!empty($productIds)) {
            $dbProducts = Product::query()
                ->active()
                ->with(['category.parent', 'brand', 'images', 'reviews'])
                ->whereIn('id', $productIds)
                ->get();

            // Sắp xếp lại theo đúng thứ tự người dùng đã thêm vào session
            $products = collect($productIds)
                ->map(fn ($id) => $dbProducts->firstWhere('id', $id))
                ->filter()
                ->values();

            if ($products->isNotEmpty()) {
                $firstCat = $products->first()->category;
                $rootCategory = $firstCat ? ($firstCat->parent ?: $firstCat) : null;
            }

            // Trích xuất toàn bộ các key thông số kỹ thuật (specs) duy nhất
            foreach ($products as $p) {
                $specs = is_array($p->specs) ? $p->specs : (json_decode($p->specs, true) ?? []);
                foreach (array_keys($specs) as $key) {
                    if (!in_array($key, $allSpecKeys)) {
                        $allSpecKeys[] = $key;
                    }
                }
            }

            // Xây dựng ma trận thông số kỹ thuật: [specKey => [productId => value]]
            foreach ($allSpecKeys as $key) {
                $rowValues = [];
                foreach ($products as $p) {
                    $specs = is_array($p->specs) ? $p->specs : (json_decode($p->specs, true) ?? []);
                    $val = $specs[$key] ?? '—';
                    $rowValues[$p->id] = $val;
                }

                // Chuẩn hóa giá trị để so sánh chính xác giữa các sản phẩm
                $normalizedValues = array_values(array_map(function ($v) {
                    if (is_array($v)) {
                        return json_encode($v, JSON_UNESCAPED_UNICODE);
                    }
                    return trim((string) $v);
                }, $rowValues));

                // Khác biệt nếu các sản phẩm có giá trị khác nhau
                $isDifferent = count(array_unique($normalizedValues)) > 1;
                if ($isDifferent) {
                    $diffCount++;
                }

                $specsMatrix[$key] = [
                    'values'       => $rowValues,
                    'is_different' => $isDifferent,
                ];
            }

            // Tính toán khác biệt thông tin cơ bản
            $brandDiff = $products->pluck('brand_id')->unique()->count() > 1;
            $categoryDiff = $products->pluck('category_id')->unique()->count() > 1;
            $stockDiff = $products->map(fn ($p) => $p->stock_quantity > 0)->unique()->count() > 1;

            if ($brandDiff) $diffCount++;
            if ($categoryDiff) $diffCount++;
            if ($stockDiff) $diffCount++;
        } else {
            $brandDiff = false;
            $categoryDiff = false;
            $stockDiff = false;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'count'        => $products->count(),
                'products'     => $products,
                'specs_matrix' => $specsMatrix,
                'diff_count'   => $diffCount,
            ]);
        }

        return view('client.compare.index', compact(
            'products',
            'specsMatrix',
            'allSpecKeys',
            'diffCount',
            'rootCategory',
            'brandDiff',
            'categoryDiff',
            'stockDiff'
        ));
    }

    /**
     * Lấy danh sách sản phẩm đang có trong thanh so sánh (JSON cho Alpine.js).
     */
    public function items(): JsonResponse
    {
        $compareSession = session('compare_items', []);

        return response()->json([
            'success' => true,
            'count'   => count($compareSession),
            'items'   => array_values($compareSession),
            'ids'     => array_map('intval', array_keys($compareSession)),
        ]);
    }

    /**
     * Thêm sản phẩm vào danh sách so sánh (AJAX).
     */
    public function add(Request $request, int $id): JsonResponse
    {
        $product = Product::query()
            ->active()
            ->with(['category.parent', 'brand'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm hoặc sản phẩm đã ngừng kinh doanh.',
            ], 404);
        }

        $compareSession = session('compare_items', []);

        // 1. Kiểm tra đã tồn tại chưa
        if (isset($compareSession[$id])) {
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã có trong danh sách so sánh.',
                'action'  => 'exists',
                'count'   => count($compareSession),
                'items'   => array_values($compareSession),
            ]);
        }

        // 2. Giới hạn tối đa 4 sản phẩm
        if (count($compareSession) >= self::MAX_COMPARE_ITEMS) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể so sánh tối đa 4 sản phẩm cùng lúc. Vui lòng xóa bớt để thêm mới.',
                'count'   => count($compareSession),
            ], 422);
        }

        // 3. Ràng buộc: Chỉ so sánh các sản phẩm cùng nhóm Danh mục gốc (Root Category)
        if (!empty($compareSession)) {
            $firstItemId = array_key_first($compareSession);
            $firstProduct = Product::with('category.parent')->find($firstItemId);

            if ($firstProduct && $product->category && $firstProduct->category) {
                $currentRootId = $this->getRootCategoryId($product->category);
                $firstRootId = $this->getRootCategoryId($firstProduct->category);

                if ($currentRootId !== $firstRootId) {
                    $firstCatName = $firstProduct->category->parent?->name ?? $firstProduct->category->name;
                    return response()->json([
                        'success' => false,
                        'message' => "Chỉ có thể so sánh các sản phẩm cùng nhóm danh mục ('{$firstCatName}'). Vui lòng xóa bớt để chọn ngành hàng khác.",
                        'count'   => count($compareSession),
                    ], 422);
                }
            }
        }

        // Thêm vào session
        $compareSession[$id] = [
            'id'              => $product->id,
            'name'            => $product->name,
            'slug'            => $product->slug,
            'price'           => $product->price,
            'sale_price'      => $product->sale_price,
            'effective_price' => $product->effective_price,
            'thumbnail'       => $product->thumbnail ? asset($product->thumbnail) : 'https://placehold.co/100x100/png?text=GearHub',
            'brand'           => $product->brand?->name,
            'category'        => $product->category?->name,
        ];

        session(['compare_items' => $compareSession]);

        return response()->json([
            'success' => true,
            'message' => "Đã thêm '{$product->name}' vào so sánh.",
            'action'  => 'added',
            'count'   => count($compareSession),
            'items'   => array_values($compareSession),
        ]);
    }

    /**
     * Bật/Tắt trạng thái so sánh của sản phẩm (Toggle - Dùng cho nút So sánh trên Card).
     */
    public function toggle(Request $request, int $id): JsonResponse
    {
        $compareSession = session('compare_items', []);

        if (isset($compareSession[$id])) {
            return $this->remove($request, $id);
        }

        return $this->add($request, $id);
    }

    /**
     * Xóa 1 sản phẩm khỏi danh sách so sánh (AJAX).
     */
    public function remove(Request $request, int $id): JsonResponse
    {
        $compareSession = session('compare_items', []);

        if (isset($compareSession[$id])) {
            unset($compareSession[$id]);
            session(['compare_items' => $compareSession]);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi danh sách so sánh.',
                'action'  => 'removed',
                'count'   => count($compareSession),
                'items'   => array_values($compareSession),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Sản phẩm không có trong danh sách so sánh.',
            'count'   => count($compareSession),
        ], 404);
    }

    /**
     * Xóa toàn bộ danh sách so sánh.
     */
    public function clear(Request $request)
    {
        session()->forget('compare_items');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tất cả sản phẩm so sánh.',
                'count'   => 0,
                'items'   => [],
            ]);
        }

        return redirect()->route('shop.index')->with('success', 'Đã xóa danh sách so sánh.');
    }

    /**
     * Tìm ID của danh mục gốc (Root Category).
     */
    protected function getRootCategoryId($category): ?int
    {
        if (!$category) return null;
        $current = $category;
        while ($current->parent_id && $current->parent) {
            $current = $current->parent;
        }
        return $current->id;
    }
}
