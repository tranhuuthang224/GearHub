<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Lưu hoặc cập nhật đánh giá sản phẩm.
     */
    public function store(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $product = Product::where('slug', $slug)->active()->firstOrFail();

        $validated = $request->validate([
            'rating'   => ['required', 'integer', 'between:1,5'],
            'comment'  => ['required', 'string', 'min:5', 'max:2000'],
            'photos'   => ['nullable', 'array', 'max:3'],
            'photos.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'rating.required'  => 'Vui lòng chọn số sao đánh giá (từ 1 đến 5 sao).',
            'rating.between'   => 'Số sao đánh giá phải từ 1 đến 5.',
            'comment.required' => 'Vui lòng nhập nội dung nhận xét của bạn.',
            'comment.min'      => 'Nội dung nhận xét cần tối thiểu 5 ký tự.',
            'comment.max'      => 'Nội dung nhận xét không được vượt quá 2000 ký tự.',
            'photos.max'       => 'Bạn chỉ có thể tải lên tối đa 3 ảnh góc chụp thật.',
            'photos.*.image'   => 'Tệp đính kèm phải là định dạng hình ảnh.',
            'photos.*.mimes'   => 'Chỉ chấp nhận ảnh định dạng jpeg, jpg, png, webp.',
            'photos.*.max'     => 'Kích thước mỗi ảnh không được vượt quá 5MB.',
        ]);

        $user = $request->user();

        // Tìm review cũ nếu có
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        $photoPaths = $existingReview ? ($existingReview->photos ?? []) : [];

        // Nếu người dùng tải ảnh mới lên
        if ($request->hasFile('photos')) {
            $newPaths = [];
            foreach ($request->file('photos') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('reviews', 'public');
                    $newPaths[] = $path;
                }
            }

            if (!empty($newPaths)) {
                $photoPaths = $newPaths;
            }
        }

        $review = Review::updateOrCreate(
            [
                'user_id'    => $user->id,
                'product_id' => $product->id,
            ],
            [
                'rating'     => $validated['rating'],
                'comment'    => $validated['comment'],
                'photos'     => $photoPaths,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn! Đánh giá đã được ghi nhận thành công.',
                'review'  => [
                    'id'         => $review->id,
                    'rating'     => $review->rating,
                    'comment'    => $review->comment,
                    'photos'     => array_map(fn ($p) => asset('storage/' . $p), $review->photos ?? []),
                    'user_name'  => $user->name,
                    'created_at' => $review->created_at->diffForHumans(),
                ],
            ]);
        }

        return redirect()
            ->to(route('shop.show', $product->slug) . '#reviews')
            ->with('review_success', 'Cảm ơn bạn! Đánh giá sản phẩm đã được gửi thành công.');
    }

    /**
     * Xóa đánh giá của chính người dùng.
     */
    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $product = Product::where('slug', $slug)->active()->firstOrFail();
        $user = $request->user();

        $review = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($review) {
            // Xóa file ảnh trong storage nếu có
            if (!empty($review->photos)) {
                foreach ($review->photos as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
            $review->delete();
        }

        return redirect()
            ->to(route('shop.show', $product->slug) . '#reviews')
            ->with('review_success', 'Đã xóa đánh giá của bạn.');
    }
}
