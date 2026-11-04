<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Services\CommunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    public function __construct(
        protected CommunityService $communityService
    ) {}

    /**
     * Danh sách bài đăng khoe góc máy (Layout Pinterest / Masonry)
     * GET /community?style=rgb
     */
    public function index(Request $request)
    {
        $query = CommunityPost::with(['user:id,name', 'products:id,name,thumbnail,price,sale_price'])
            ->published()
            ->latest();

        // Lọc theo phong cách setup (style)
        if ($request->filled('style') && array_key_exists($request->style, CommunityPost::STYLES)) {
            $query->where('style', $request->style);
        }

        $posts = $query->paginate(12)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'posts'   => $posts,
            ]);
        }

        return view('client.community.index', [
            'posts'         => $posts,
            'styles'        => CommunityPost::STYLES,
            'selectedStyle' => $request->style,
        ]);
    }

    /**
     * Màn hình form đăng bài: /community/create
     */
    public function create()
    {
        return view('client.community.create', [
            'styles' => CommunityPost::STYLES,
        ]);
    }

    /**
     * Lưu bài đăng mới kèm gắn thẻ sản phẩm
     * POST /community
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'style'         => 'required|string|in:' . implode(',', array_keys(CommunityPost::STYLES)),
            'content'       => 'nullable|string|max:2000',
            'image'         => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Tối đa 5MB
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $post = $this->communityService->createPost(
            auth()->user(),
            $validated,
            $request->file('image'),
            $validated['product_ids'] ?? []
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng bài khoe góc máy thành công!',
                'slug'    => $post->slug,
            ]);
        }

        return redirect()->route('community.show', $post->slug)->with('success', 'Đăng bài thành công!');
    }

    /**
     * Xem chi tiết bài đăng
     * GET /community/{slug}
     */
    public function show(string $slug)
    {
        $post = CommunityPost::with([
            'user:id,name',
            'products.category',
            'comments.user:id,name',
        ])
        ->where('slug', $slug)
        ->published()
        ->firstOrFail();

        $isLiked = $post->isLikedBy(auth()->user());

        return view('client.community.show', compact('post', 'isLiked'));
    }

    /**
     * Toggle Thả tim (Chống spam click, atomic lock)
     * POST /community/{id}/like
     */
    public function toggleLike(Request $request, int $id)
    {
        $user = auth()->user();
        $post = CommunityPost::findOrFail($id);

        $result = DB::transaction(function () use ($user, $post) {
            $existingLike = PostLike::where('community_post_id', $post->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                $post->decrement('likes_count');
                $liked = false;
            } else {
                PostLike::create([
                    'community_post_id' => $post->id,
                    'user_id'           => $user->id,
                ]);
                $post->increment('likes_count');
                $liked = true;
            }

            return [
                'liked'       => $liked,
                'likes_count' => (int) $post->fresh()->likes_count,
            ];
        });

        return response()->json([
            'success'     => true,
            'liked'       => $result['liked'],
            'likes_count' => $result['likes_count'],
        ]);
    }

    /**
     * Thêm bình luận
     * POST /community/{id}/comments
     */
    public function storeComment(Request $request, int $id)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:2|max:1000',
        ]);

        $post = CommunityPost::findOrFail($id);

        $comment = DB::transaction(function () use ($post, $validated) {
            $cmt = PostComment::create([
                'community_post_id' => $post->id,
                'user_id'           => auth()->id(),
                'content'           => $validated['content'],
            ]);

            $post->increment('comments_count');

            return $cmt->load('user:id,name');
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Đã đăng bình luận thành công.',
                'comment'        => $comment,
                'comments_count' => $post->fresh()->comments_count,
            ]);
        }

        return back()->with('success', 'Đã đăng bình luận.');
    }
}