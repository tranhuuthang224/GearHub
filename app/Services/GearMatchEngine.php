<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class GearMatchEngine
{
    /** Trọng số chấm điểm chuẩn */
    public const WEIGHT_CATEGORY = 40;
    public const WEIGHT_BUDGET   = 30;
    public const WEIGHT_FEATURE  = 10;

    protected array $answers = [];

    public function forAnswers(array $answers): self
    {
        $this->answers = $answers;
        return $this;
    }

    /**
     * Thuật toán chấm điểm và lấy Top sản phẩm phù hợp nhất (mặc định Top 3)
     */
    public function getTopMatches(int $limit = 3): Collection
    {
        $products = $this->getCandidateProducts();

        $scoredProducts = $products->map(function ($product) {
            $scoreDetails = $this->calculateScore($product);
            
            $product->match_score = $scoreDetails['score'];
            $product->match_percentage = min(100, (int) round(($scoreDetails['score'] / 100) * 100));
            $product->match_reasons = $scoreDetails['reasons'];

            return $product;
        });

        // Sắp xếp giảm dần theo điểm và lấy Top $limit
        return $scoredProducts
            ->filter(fn ($p) => $p->match_score > 0)
            ->sortByDesc('match_score')
            ->values()
            ->take($limit);
    }

    /**
     * Logic tính điểm chi tiết cho từng sản phẩm
     */
    protected function calculateScore(Product $product): array
    {
        $score = 0;
        $reasons = [];

        $targetCategory = $this->answers['device_type'] ?? null; // vd: 'ban-phim-co', 'chuot', 'tai-nghe'
        $budgetRange    = $this->answers['budget'] ?? null;      // vd: 'under_1m', '1m_to_3m', 'above_3m'
        $usagePurpose   = $this->answers['purpose'] ?? null;     // vd: 'gaming', 'office', 'programming'
        $prioritySpecs  = (array) ($this->answers['features'] ?? []); // vd: ['wireless', 'rgb', 'silent']

        // 1. Chấm điểm Danh mục / Loại thiết bị (+40đ)
        if ($targetCategory) {
            $catSlug = $product->category?->slug ?? '';
            $parentCatSlug = $product->category?->parent?->slug ?? '';

            if (str_contains($catSlug, $targetCategory) || str_contains($parentCatSlug, $targetCategory)) {
                $score += self::WEIGHT_CATEGORY;
                $reasons[] = 'Đúng danh mục thiết bị bạn cần';
            }
        }

        // 2. Chấm điểm Ngân sách (+30đ)
        $effectivePrice = $product->effective_price ?? $product->price;
        if ($budgetRange && $this->isPriceMatching($effectivePrice, $budgetRange)) {
            $score += self::WEIGHT_BUDGET;
            $reasons[] = 'Nằm trong khoảng ngân sách lý tưởng';
        }

        // 3. Chấm điểm Nhu cầu sử dụng & Specs/Tính năng ưu tiên (+10đ mỗi tiêu chí)
        $specsText = mb_strtolower(json_encode($product->specs, JSON_UNESCAPED_UNICODE) . ' ' . $product->name . ' ' . $product->description);

        // Nhu cầu (Gaming / Văn phòng / Code)
        if ($usagePurpose) {
            if ($usagePurpose === 'gaming' && (str_contains($specsText, 'gaming') || str_contains($specsText, 'rgb') || str_contains($specsText, 'hz') || str_contains($specsText, 'switch'))) {
                $score += self::WEIGHT_FEATURE;
                $reasons[] = 'Tối ưu cho trải nghiệm Gaming';
            } elseif ($usagePurpose === 'office' && (str_contains($specsText, 'văn phòng') || str_contains($specsText, 'silent') || str_contains($specsText, 'yên tĩnh') || str_contains($specsText, 'ergonomic'))) {
                $score += self::WEIGHT_FEATURE;
                $reasons[] = 'Êm ái, thích hợp môi trường văn phòng';
            } elseif ($usagePurpose === 'programming' && (str_contains($specsText, 'phím cơ') || str_contains($specsText, 'ergonomic') || str_contains($specsText, 'bluetooth'))) {
                $score += self::WEIGHT_FEATURE;
                $reasons[] = 'Gõ văn bản / lập trình thoải mái cả ngày';
            }
        }

        // Các tính năng ưu tiên (Wireless, RGB, Pin trâu, Hot-swap,...)
        foreach ($prioritySpecs as $feature) {
            $matched = false;
            switch ($feature) {
                case 'wireless':
                    if (str_contains($specsText, 'không dây') || str_contains($specsText, 'wireless') || str_contains($specsText, 'bluetooth') || str_contains($specsText, '2.4g')) {
                        $matched = true;
                        $reasons[] = 'Hỗ trợ kết nối không dây tiện lợi';
                    }
                    break;
                case 'rgb':
                    if (str_contains($specsText, 'rgb') || str_contains($specsText, 'led')) {
                        $matched = true;
                        $reasons[] = 'Đèn LED RGB rực rỡ cá tính';
                    }
                    break;
                case 'hotswap':
                    if (str_contains($specsText, 'hot-swap') || str_contains($specsText, 'hotswap')) {
                        $matched = true;
                        $reasons[] = 'Hỗ trợ thay switch nóng (Hot-swap)';
                    }
                    break;
                case 'lightweight':
                    if (str_contains($specsText, 'siêu nhẹ') || str_contains($specsText, 'g') || str_contains($specsText, 'lightweight')) {
                        $matched = true;
                        $reasons[] = 'Trọng lượng nhẹ, chống mỏi tay';
                    }
                    break;
            }

            if ($matched) {
                $score += self::WEIGHT_FEATURE;
            }
        }

        return [
            'score'   => $score,
            'reasons' => array_unique($reasons),
        ];
    }

    /**
     * Kiểm tra khớp khoảng giá
     */
    protected function isPriceMatching(int $price, string $range): bool
    {
        return match ($range) {
            'under_500k'   => $price <= 500000,
            'under_1m'     => $price <= 1000000,
            '1m_to_2m'     => $price >= 1000000 && $price <= 2000000,
            '1m_to_3m'     => $price >= 1000000 && $price <= 3000000,
            '2m_to_5m'     => $price >= 2000000 && $price <= 5000000,
            'above_3m'     => $price >= 3000000,
            'above_5m'     => $price >= 5000000,
            default        => true,
        };
    }

    protected function getCandidateProducts(): Collection
    {
        return Product::with(['category.parent', 'brand', 'images'])
            ->active()
            ->where('stock_quantity', '>', 0)
            ->get();
    }
}