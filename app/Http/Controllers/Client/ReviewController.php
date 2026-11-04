<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService
    ) {}

    /**
     * Lấy danh sách reviews & thống kê sao của sản phẩm
     * GET /products/{product}/reviews
     */
    public function index(Request $request, Product $product)
    {
        $reviews = $product->reviews()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);

        $stats = $product->ratingBreakdown();
        $eligibility = $this->reviewService->canUserReview(auth()->user(), $product);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'stats'       => $stats,
                'eligibility' => $eligibility,
                'reviews'     => $reviews,
            ]);
        }

        return view('client.reviews.index', compact('product', 'reviews', 'stats', 'eligibility'));
    }

    /**
     * Gửi đánh giá mới
     * POST /products/{product}/reviews
     */
    public function store(Request $request, Product $product)
    {
        $user = auth()->user();
        $eligibility = $this->reviewService->canUserReview($user, $product);

        if (!$eligibility['can_review']) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $eligibility['reason']], 403);
            }
            return back()->with('error', $eligibility['reason']);
        }

        $validated = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'required|string|min:5|max:1000',
            'images'   => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $review = $this->reviewService->createReview(
            $user,
            $product,
            $validated,
            $request->file('images', [])
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã đánh giá sản phẩm!',
                'review'  => $review->load('user:id,name,avatar'),
            ]);
        }

        return back()->with('success', 'Đánh giá của bạn đã được ghi nhận.');
    }
}