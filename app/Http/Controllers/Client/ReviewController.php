<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function __construct(
        protected ?ReviewService $reviewService = null
    ) {
        $this->reviewService = $reviewService ?? app(ReviewService::class);
    }

    /**
     * Tìm sản phẩm theo Slug, ID hoặc Model.
     */
    protected function resolveProduct(Product|string $productOrSlug): Product
    {
        if ($productOrSlug instanceof Product) {
            return $productOrSlug;
        }

        return Product::query()
            ->where('slug', $productOrSlug)
            ->orWhere('id', is_numeric($productOrSlug) ? (int) $productOrSlug : 0)
            ->active()
            ->firstOrFail();
    }

    /**
     * Lấy danh sách reviews & thống kê sao của sản phẩm (API / AJAX)
     * GET /products/{product}/reviews
     */
    public function index(Request $request, Product|string $product)
    {
        $product = $this->resolveProduct($product);

        $reviews = $product->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $stats = $product->ratingBreakdown();
        $eligibility = $this->reviewService->canUserReview($request->user(), $product);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'stats'       => $stats,
                'eligibility' => $eligibility,
                'reviews'     => $reviews,
            ]);
        }

        return redirect()->to(route('shop.show', $product->slug) . '#reviews');
    }

    /**
     * Lưu hoặc cập nhật đánh giá sản phẩm.
     * POST /shop/{slug}/reviews hoặc POST /products/{product}/reviews
     */
    public function store(Request $request, Product|string $slug): JsonResponse|RedirectResponse
    {
        $product = $this->resolveProduct($slug);
        $user = $request->user();


        $validated = $request->validate([
            'rating'     => ['required', 'integer', 'between:1,5'],
            'comment'    => ['required', 'string', 'min:5', 'max:2000'],
            'photos'     => ['nullable', 'array', 'max:3'],
            'photos.*'   => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'images'     => ['nullable', 'array', 'max:3'],
            'images.*'   => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
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

        $comment = preg_replace('~<script\b[^>]*>.*?</script\s*>~is', '', $validated['comment']);
        $validated['comment'] = strip_tags(trim($validated['comment']));

        // Tìm review cũ nếu có
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        $photoPaths = $existingReview ? ($existingReview->photos ?? []) : [];

        // Hỗ trợ cả trường name="photos" và name="images"
        $uploadedFiles = $request->file('photos') ?? $request->file('images') ?? [];
        if (!empty($uploadedFiles)) {
            $newPaths = [];
            foreach ($uploadedFiles as $file) {
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
                'rating'               => $validated['rating'],
                'comment'              => $validated['comment'],
                'photos'               => $photoPaths,
                'images'               => $photoPaths,
                'is_verified_purchase' => true,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
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
    public function destroy(Request $request, Product|string $slug): RedirectResponse
    {
        $product = $this->resolveProduct($slug);
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
