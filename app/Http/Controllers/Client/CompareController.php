<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    /**
     * Giới hạn số lượng sản phẩm so sánh tối đa cùng lúc.
     */
    public const MAX_COMPARE_ITEMS = 4;

    /**
     * Hiển thị trang Bảng so sánh thông số kỹ thuật đa cột.
     */
    public function index(): View
    {
        $compareSession = session('compare_items', []);
        $productIds = array_keys($compareSession);

        $products = collect();
        $specsMatrix = [];
        $allSpecKeys = [];
        $diffCount = 0;
        $rootCategory = null;

        if (!empty($productIds)) {
            $dbProducts = Product::query()
                ->active()
                ->with(['category.parent', 'brand', 'images'])
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
     * Lấy danh sách sản phẩm đang trong so sánh (API dùng cho Sticky Compare Bar).
     */
    public function items(): JsonResponse
    {
        $items = array_values(session('compare_items', []));

        return response()->json([
            'success' => true,
            'items'   => $items,
            'count'   => count($items),
        ]);
    }

    /**
     * Bật/tắt sản phẩm trong danh sách so sánh (Toggle: thêm nếu chưa có, xóa nếu đã có).
     */
    public function toggle(int $id): JsonResponse
    {
        $compareItems = session('compare_items', []);

        // Nếu sản phẩm đã có trong danh sách -> Xóa bỏ
        if (isset($compareItems[$id])) {
            $removedName = $compareItems[$id]['name'] ?? 'Sản phẩm';
            unset($compareItems[$id]);
            session(['compare_items' => $compareItems]);

            return response()->json([
                'success' => true,
                'action'  => 'removed',
                'id'      => $id,
                'items'   => array_values($compareItems),
                'count'   => count($compareItems),
                'message' => "Đã xóa \"{$removedName}\" khỏi danh sách so sánh.",
            ]);
        }

        // Nếu chưa có -> Kiểm tra điều kiện thêm mới
        return $this->addDirect($id);
    }

    /**
     * Thêm sản phẩm vào danh sách so sánh.
     */
    public function add(int $id): JsonResponse
    {
        return $this->addDirect($id);
    }

    /**
     * Xóa 1 sản phẩm khỏi danh sách so sánh.
     */
    public function remove(int $id): JsonResponse
    {
        $compareItems = session('compare_items', []);

        if (isset($compareItems[$id])) {
            $name = $compareItems[$id]['name'] ?? 'Sản phẩm';
            unset($compareItems[$id]);
            session(['compare_items' => $compareItems]);

            return response()->json([
                'success' => true,
                'action'  => 'removed',
                'id'      => $id,
                'items'   => array_values($compareItems),
                'count'   => count($compareItems),
                'message' => "Đã xóa \"{$name}\" khỏi danh sách so sánh.",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Sản phẩm không có trong danh sách so sánh.',
            'items'   => array_values($compareItems),
            'count'   => count($compareItems),
        ], 404);
    }

    /**
     * Xóa sạch toàn bộ danh sách so sánh.
     */
    public function clear(): JsonResponse
    {
        session()->forget('compare_items');

        return response()->json([
            'success' => true,
            'action'  => 'cleared',
            'items'   => [],
            'count'   => 0,
            'message' => 'Đã xóa toàn bộ sản phẩm trong danh sách so sánh.',
        ]);
    }

    /**
     * Xử lý kiểm tra ràng buộc nghiệp vụ và thêm sản phẩm vào session.
     */
    private function addDirect(int $id): JsonResponse
    {
        $compareItems = session('compare_items', []);

        // 1. Kiểm tra giới hạn số lượng tối đa 4 món
        if (count($compareItems) >= self::MAX_COMPARE_ITEMS) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể so sánh tối đa 4 sản phẩm cùng lúc. Vui lòng xóa bớt sản phẩm để thêm mới.',
                'items'   => array_values($compareItems),
                'count'   => count($compareItems),
            ], 422);
        }

        // 2. Kiểm tra sản phẩm có tồn tại và đang hoạt động không
        $product = Product::query()
            ->active()
            ->with(['category.parent', 'brand'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.',
                'items'   => array_values($compareItems),
                'count'   => count($compareItems),
            ], 404);
        }

        // 3. Xác định nhóm danh mục cha (Root Category)
        $category = $product->category;
        $rootId   = $category ? ($category->parent_id ?: $category->id) : null;
        $rootName = $category ? ($category->parent ? $category->parent->name : $category->name) : 'Sản phẩm';

        // 4. Ràng buộc chỉ cho phép so sánh các sản phẩm cùng nhóm danh mục gốc
        if (!empty($compareItems)) {
            $firstItem = reset($compareItems);
            $firstRootId = $firstItem['root_id'] ?? null;
            $firstRootName = $firstItem['root_name'] ?? 'danh mục';

            if ($firstRootId !== null && $rootId !== null && $firstRootId !== $rootId) {
                return response()->json([
                    'success' => false,
                    'message' => "Chỉ có thể so sánh các sản phẩm cùng nhóm danh mục ({$firstRootName}). Không thể so sánh với {$rootName}.",
                    'items'   => array_values($compareItems),
                    'count'   => count($compareItems),
                ], 422);
            }
        }

        // 5. Thêm vào danh sách so sánh
        $compareItems[$product->id] = [
            'id'          => $product->id,
            'name'        => $product->name,
            'slug'        => $product->slug,
            'sku'         => $product->sku,
            'price'       => (int) $product->price,
            'sale_price'  => $product->sale_price ? (int) $product->sale_price : null,
            'thumbnail'   => $product->thumbnail_url ?? asset($product->thumbnail ?? 'images/hero-headphone.png'),
            'category'    => $product->category?->name,
            'brand'       => $product->brand?->name,
            'root_id'     => $rootId,
            'root_name'   => $rootName,
        ];

        session(['compare_items' => $compareItems]);

        return response()->json([
            'success' => true,
            'action'  => 'added',
            'id'      => $product->id,
            'items'   => array_values($compareItems),
            'count'   => count($compareItems),
            'message' => "Đã thêm \"{$product->name}\" vào danh sách so sánh.",
        ]);
    }
}
