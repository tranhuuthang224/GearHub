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
        return session()->get(self::SESSION_KEY, []);
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
}