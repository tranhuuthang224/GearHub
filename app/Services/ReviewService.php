<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReviewService
{
    /**
     * Kiểm tra xem User có đủ điều kiện đánh giá sản phẩm hay không:
     * - Phải đăng nhập
     * - Phải có ít nhất 1 đơn hàng trạng thái 'completed' chứa sản phẩm này
     * - Chưa từng đánh giá sản phẩm này trước đó
     */
    public function canUserReview(?User $user, Product $product): array
    {
        if (!$user) {
            return [
                'can_review' => false,
                'reason'     => 'Vui lòng đăng nhập để đánh giá sản phẩm.',
            ];
        }

        // Kiểm tra đã đánh giá chưa
        $alreadyReviewed = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($alreadyReviewed) {
            return [
                'can_review' => false,
                'reason'     => 'Bạn đã đánh giá sản phẩm này rồi.',
            ];
        }

        // Kiểm tra đã mua và đơn hàng đã hoàn thành (completed) chưa
        $hasCompletedPurchase = Order::where('user_id', $user->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();

        if (!$hasCompletedPurchase) {
            return [
                'can_review' => false,
                'reason'     => 'Chỉ khách hàng đã mua và hoàn thành đơn hàng sản phẩm này mới được đánh giá.',
            ];
        }

        return [
            'can_review' => true,
            'reason'     => null,
        ];
    }

    /**
     * Lưu đánh giá mới kèm upload ảnh (Lưu relative path an toàn)
     */
    public function createReview(User $user, Product $product, array $data, array $images = []): Review
    {
        $uploadedPaths = [];
        if (!empty($images)) {
            foreach ($images as $image) {
                if ($image instanceof UploadedFile) {
                    $path = $image->store('reviews', 'public');
                    $uploadedPaths[] = $path;
                }
            }
        }

        return Review::create([
            'user_id'              => $user->id,
            'product_id'           => $product->id,
            'rating'               => (int) $data['rating'],
            'comment'              => $data['comment'],
            'images'               => !empty($uploadedPaths) ? $uploadedPaths : null,
            'photos'               => !empty($uploadedPaths) ? $uploadedPaths : null,
            'is_verified_purchase' => true,
        ]);
    }
}
