<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;

class CompareService
{
    public const MAX_ITEMS = 4;
    public const SESSION_KEY = 'compare_items';

    /**
     * Lấy danh sách ID các sản phẩm đang được so sánh từ Session.
     * @return array<int>
     */
    public function getCompareIds(): array
    {
        $raw = session()->get(self::SESSION_KEY, []);
        if (empty($raw)) {
            return [];
        }

        // Hỗ trợ cả mảng tuần tự [1, 2] và mảng key [1 => [...], 2 => [...]]
        if (array_is_list($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }

        return array_values(array_filter(array_map('intval', array_keys($raw))));
    }

    /**
     * Lấy danh sách Product models kèm theo category, brand từ Session.
     */
    public function getItems()
    {
        $ids = $this->getCompareIds();

        if (empty($ids)) {
            return collect();
        }

        // Lấy sản phẩm theo thứ tự trong session
        return Product::with(['category.parent', 'brand', 'images'])
            ->whereIn('id', $ids)
            ->active()
            ->get()
            ->sortBy(fn ($product) => array_search($product->id, $ids))
            ->values();
    }

    /**
     * Thêm sản phẩm vào danh sách so sánh.
     * 
     * Ràng buộc:
     * 1. Tối đa 4 sản phẩm.
     * 2. Chỉ cho phép so sánh sản phẩm cùng Danh mục cha (Root Category).
     * 
     * @return array [success => bool, message => string, items => Collection, count => int]
     */
    public function add(int|string $productId): array
    {
        $productId = (int) $productId;
        $product = Product::with('category.parent')->find($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ];
        }

        $ids = $this->getCompareIds();

        // 1. Kiểm tra đã tồn tại trong danh sách chưa
        if (in_array($productId, $ids)) {
            return [
                'success' => false,
                'message' => 'Sản phẩm này đã có trong danh sách so sánh.',
                'count'   => count($ids),
            ];
        }

        // 2. Kiểm tra số lượng tối đa (Tối đa 4)
        if (count($ids) >= self::MAX_ITEMS) {
            return [
                'success' => false,
                'message' => 'Bạn chỉ có thể so sánh tối đa ' . self::MAX_ITEMS . ' sản phẩm cùng lúc.',
                'count'   => count($ids),
            ];
        }

        // 3. Kiểm tra danh mục cha (Root Category)
        if (!empty($ids)) {
            $firstProduct = Product::with('category.parent')->find($ids[0]);

            if ($firstProduct) {
                $currentRootCatId = $this->getRootCategoryId($product->category);
                $firstRootCatId = $this->getRootCategoryId($firstProduct->category);

                if ($currentRootCatId !== $firstRootCatId) {
                    $catName = $firstProduct->category?->name ?? 'danh mục hiện tại';
                    return [
                        'success' => false,
                        'message' => "Chỉ có thể so sánh các sản phẩm cùng nhóm ngành hàng (hiện tại là nhóm '{$catName}'). Vui lòng xóa bớt để chọn ngành hàng khác.",
                        'count'   => count($ids),
                    ];
                }
            }
        }

        // Thêm vào session
        $ids[] = $productId;
        session()->put(self::SESSION_KEY, $ids);

        return [
            'success' => true,
            'message' => "Đã thêm '{$product->name}' vào danh sách so sánh.",
            'count'   => count($ids),
            'items'   => $this->getItems(),
        ];
    }

    /**
     * Bật/tắt trạng thái so sánh của sản phẩm (Toggle).
     */
    public function toggle(int|string $productId): array
    {
        $productId = (int) $productId;
        $ids = $this->getCompareIds();

        if (in_array($productId, $ids, true)) {
            $res = $this->remove($productId);
            $res['action'] = 'removed';
            return $res;
        }

        $res = $this->add($productId);
        $res['action'] = $res['success'] ? 'added' : 'none';
        return $res;
    }

    /**
     * Xóa 1 sản phẩm khỏi danh sách so sánh.
     */
    public function remove(int|string $productId): array
    {
        $productId = (int) $productId;
        $ids = $this->getCompareIds();

        if (($key = array_search($productId, $ids)) !== false) {
            unset($ids[$key]);
            $ids = array_values($ids); // reindex mảng
            session()->put(self::SESSION_KEY, $ids);

            return [
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi danh sách so sánh.',
                'count'   => count($ids),
                'items'   => $this->getItems(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Sản phẩm không có trong danh sách so sánh.',
            'count'   => count($ids),
        ];
    }

    /**
     * Xóa toàn bộ danh sách so sánh.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Tìm ID của danh mục gốc cao nhất (Root Category)
     */
    protected function getRootCategoryId(?Category $category): ?int
    {
        if (!$category) {
            return null;
        }

        // Leo ngược lên parent đến khi gặp danh mục cha cao nhất
        $current = $category;
        while ($current->parent_id && $current->parent) {
            $current = $current->parent;
        }

        return $current->id;
    }

        /**
     * Xây dựng ma trận thông số kỹ thuật (Specs Matrix) cho danh sách sản phẩm.
     * 
     * Output trả về mảng gồm:
     * - 'keys': Danh sách tất cả các key thuộc tính gộp lại của các sản phẩm (duy nhất).
     * - 'matrix': [
     *     'Tên thuộc tính' => [
     *        'values' => [productId1 => 'giá trị 1', productId2 => 'giá trị 2', ...],
     *        'is_different' => true|false (hàng này các giá trị có khác nhau không, hỗ trợ Member A highlight)
     *     ]
     *   ]
     * 
     * @param \Illuminate\Support\Collection $products
     * @return array
     */
    public function buildSpecsMatrix($products): array
    {
        if ($products->isEmpty()) {
            return [
                'keys' => [],
                'matrix' => [],
            ];
        }

        // 1. Tập hợp tất cả các key xuất hiện trong specs của tất cả sản phẩm
        $allKeys = [];
        foreach ($products as $product) {
            $specs = is_array($product->specs) ? $product->specs : [];
            foreach (array_keys($specs) as $key) {
                if (!in_array($key, $allKeys, true)) {
                    $allKeys[] = $key;
                }
            }
        }

        // 2. Dựng ma trận so sánh
        $matrix = [];
        foreach ($allKeys as $key) {
            $rowValues = [];

            foreach ($products as $product) {
                $specs = is_array($product->specs) ? $product->specs : [];
                // Nếu sản phẩm không có thuộc tính này thì tự động điền '-'
                $rowValues[$product->id] = $specs[$key] ?? '-';
            }

            // Kiểm tra xem các giá trị trong hàng này có khác biệt nhau không
            // Loại bỏ các giá trị trùng lặp
            $uniqueValues = array_unique(array_values($rowValues));
            $isDifferent = count($uniqueValues) > 1;

            $matrix[$key] = [
                'label'        => $key,
                'values'       => $rowValues,
                'is_different' => $isDifferent,
            ];
        }

        return [
            'keys'   => $allKeys,
            'matrix' => $matrix,
        ];
    }
}