<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class GearMatchEngine
{
    /**
     * Hệ số điểm chuẩn cho thuật toán Quiz Recommendation:
     * - Khớp danh mục / ngành hàng: 40 điểm
     * - Khớp khoảng ngân sách: 30 điểm
     * - Khớp mỗi tiêu chí specs / tính năng ưu tiên: 10 điểm
     */
    public const WEIGHT_CATEGORY = 40;
    public const WEIGHT_BUDGET   = 30;
    public const WEIGHT_FEATURE  = 10;

    /**
     * Dự trù trọng số tối đa để tính tỷ lệ match % (Huy hiệu % độ phù hợp trên UI)
     */
    public const MAX_POSSIBLE_SCORE = 100;

    /**
     * Dữ liệu Quiz nhận vào từ Client
     * @var array
     */
    protected array $answers = [];

    /**
     * Khởi tạo engine với câu trả lời
     */
    public function forAnswers(array $answers): self
    {
        $this->answers = $answers;
        return $this;
    }

    /**
     * Lấy các sản phẩm ứng viên đang active từ DB
     */
    protected function getCandidateProducts(): Collection
    {
        return Product::with(['category.parent', 'brand', 'images'])
            ->active()
            ->where('stock_quantity', '>', 0)
            ->get();
    }
}