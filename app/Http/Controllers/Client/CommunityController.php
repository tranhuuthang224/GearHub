<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * Hiển thị trang chủ Diễn đàn Khoe Góc Máy (Pinterest / Masonry Grid).
     */
    public function index(Request $request): View
    {
        $style = $request->query('style');
        $sort = $request->query('sort', 'trending');
        $search = $request->query('search');

        $query = CommunityPost::query()
            ->with(['user', 'products'])
            ->withCount(['products', 'comments']);

        // Bộ lọc phong cách setup
        if ($style && array_key_exists($style, CommunityPost::STYLES)) {
            $query->where('style', $style);
        }

        // Tìm kiếm theo từ khóa
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sắp xếp
        match ($sort) {
            'latest' => $query->latest('id'),
            'most_gears' => $query->orderByDesc('products_count')->orderByDesc('likes_count'),
            'most_comments' => $query->orderByDesc('comments_count')->orderByDesc('likes_count'),
            default => $query->orderByDesc('is_featured')->orderByDesc('likes_count')->latest('id'),
        };

        $posts = $query->paginate(12)->withQueryString();

        // Đếm số lượng bài theo từng phong cách
        $styleCounts = [
            'all' => CommunityPost::count(),
        ];
        foreach (array_keys(CommunityPost::STYLES) as $s) {
            $styleCounts[$s] = CommunityPost::where('style', $s)->count();
        }

        // Danh sách ID các bài mà user hiện tại đã like
        $userLikedPostIds = [];
        if ($user = $request->user()) {
            $userLikedPostIds = CommunityPostLike::where('user_id', $user->id)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('community_post_id')
                ->all();
        }

        return view('client.community.index', [
            'posts'            => $posts,
            'styles'           => CommunityPost::STYLES,
            'currentStyle'     => $style,
            'currentSort'      => $sort,
            'search'           => $search,
            'styleCounts'      => $styleCounts,
            'userLikedPostIds' => $userLikedPostIds,
        ]);
    }

    /**
     * Xem chi tiết bài viết (Hỗ trợ Modal AJAX và trang độc lập).
     */
    public function show(Request $request, CommunityPost $post)
    {
        $post->load([
            'user',
            'products.brand',
            'products.category',
            'comments.user',
        ])->loadCount(['products', 'comments']);

        // Tăng lượt xem nhẹ
        $post->increment('views_count');

        $isLiked = false;
        if ($user = $request->user()) {
            $isLiked = $post->isLikedBy($user);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'id'             => $post->id,
                'title'          => $post->title,
                'slug'           => $post->slug,
                'description'    => $post->description,
                'image'          => $post->display_image,
                'style'          => $post->style,
                'style_info'     => $post->style_info,
                'likes_count'    => $post->likes_count,
                'comments_count' => $post->comments_count,
                'views_count'    => $post->views_count,
                'is_liked'       => $isLiked,
                'created_at'     => $post->created_at->diffForHumans(),
                'author'         => [
                    'name'     => $post->user->name,
                    'initials' => mb_strtoupper(mb_substr($post->user->name, 0, 1)),
                ],
                'products'       => $post->products->map(function ($product) {
                    return [
                        'id'              => $product->id,
                        'name'            => $product->name,
                        'slug'            => $product->slug,
                        'brand'           => $product->brand?->name,
                        'category'        => $product->category?->name,
                        'thumbnail'       => $product->thumbnail ? asset($product->thumbnail) : 'https://placehold.co/400x400/png?text=GearHub',
                        'price'           => $product->price,
                        'sale_price'      => $product->sale_price,
                        'effective_price' => $product->effective_price,
                        'formatted_price' => number_format($product->effective_price, 0, ',', '.') . '₫',
                        'url'             => route('shop.show', $product->slug),
                        'in_stock'        => $product->stock_quantity > 0,
                    ];
                }),
                'comments'       => $post->comments->map(function ($comment) {
                    return [
                        'id'         => $comment->id,
                        'user_name'  => $comment->user->name,
                        'content'    => $comment->content,
                        'created_at' => $comment->created_at->diffForHumans(),
                    ];
                }),
            ]);
        }

        // Chuyển hướng về trang chủ community kèm anchor hoặc modal param
        return redirect()->route('community.index', ['post' => $post->id]);
    }

    /**
     * Thả tim / Bỏ thả tim cho bài viết (AJAX).
     */
    public function like(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thả tim cho góc máy này.',
                'redirect' => route('login'),
            ], 401);
        }

        $existingLike = CommunityPostLike::where('community_post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            CommunityPostLike::create([
                'community_post_id' => $post->id,
                'user_id'           => $user->id,
            ]);
            $post->increment('likes_count');
            $liked = true;
        }

        $post->refresh();

        return response()->json([
            'success'     => true,
            'liked'       => $liked,
            'likes_count' => $post->likes_count,
        ]);
    }

    /**
     * Thêm bình luận cho bài viết (AJAX).
     */
    public function comment(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để bình luận.',
                'redirect' => route('login'),
            ], 401);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'content.required' => 'Vui lòng nhập nội dung bình luận.',
            'content.min'      => 'Bình luận tối thiểu 2 ký tự.',
            'content.max'      => 'Bình luận không quá 1000 ký tự.',
        ]);

        $comment = CommunityPostComment::create([
            'community_post_id' => $post->id,
            'user_id'           => $user->id,
            'content'           => $validated['content'],
        ]);

        $post->increment('comments_count');

        return response()->json([
            'success'        => true,
            'comments_count' => $post->fresh()->comments_count,
            'comment'        => [
                'id'         => $comment->id,
                'user_name'  => $user->name,
                'content'    => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }
}
