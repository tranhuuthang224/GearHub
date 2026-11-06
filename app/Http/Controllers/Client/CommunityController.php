<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\Product;
use App\Services\CommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function __construct(
        protected ?CommunityService $communityService = null
    ) {
        $this->communityService = $communityService ?? app(CommunityService::class);
    }

    /**
     * Tìm bài viết theo slug hoặc id.
     */
    protected function resolvePost(string $idOrSlug): CommunityPost
    {
        return CommunityPost::query()
            ->where('slug', $idOrSlug)
            ->orWhere('id', is_numeric($idOrSlug) ? (int) $idOrSlug : 0)
            ->firstOrFail();
    }

    /**
     * Hiển thị trang chủ Diễn đàn Khoe Góc Máy (Pinterest / Masonry Grid).
     */
    public function index(Request $request)
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
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
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
            $userLikedPostIds = PostLike::where('user_id', $user->id)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('community_post_id')
                ->all();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'posts'   => $posts,
            ]);
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
     * Hiển thị form đăng bài khoe góc máy (/community/create).
     */
    public function create(): View
    {
        $suggestedProducts = Product::where('is_active', true)
            ->with(['brand', 'category'])
            ->latest('id')
            ->take(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id'              => $product->id,
                    'name'            => $product->name,
                    'brand'           => $product->brand?->name ?? 'GearHub',
                    'category'        => $product->category?->name ?? 'Gear',
                    'thumbnail'       => $product->thumbnail ? asset($product->thumbnail) : 'https://placehold.co/400x400/png?text=GearHub',
                    'effective_price' => $product->effective_price,
                    'formatted_price' => number_format($product->effective_price, 0, ',', '.') . '₫',
                ];
            });

        return view('client.community.create', [
            'styles'            => CommunityPost::STYLES,
            'suggestedProducts' => $suggestedProducts,
        ]);
    }

    /**
     * Tìm kiếm sản phẩm phục vụ tính năng Tag Gear (AJAX).
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->query('q', ''));

        $query = Product::where('is_active', true)->with(['brand', 'category']);

        if (! empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        $products = $query->latest('id')->take(10)->get()->map(function ($product) {
            return [
                'id'              => $product->id,
                'name'            => $product->name,
                'brand'           => $product->brand?->name ?? 'GearHub',
                'category'        => $product->category?->name ?? 'Gear',
                'thumbnail'       => $product->thumbnail ? asset($product->thumbnail) : 'https://placehold.co/400x400/png?text=GearHub',
                'price'           => $product->price,
                'sale_price'      => $product->sale_price,
                'effective_price' => $product->effective_price,
                'formatted_price' => number_format($product->effective_price, 0, ',', '.') . '₫',
                'in_stock'        => $product->stock_quantity > 0,
            ];
        });

        return response()->json($products);
    }

    /**
     * Lưu bài đăng khoe góc máy mới.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title'         => ['required', 'string', 'min:5', 'max:200'],
            'style'         => ['required', 'string'],
            'image'         => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'description'   => ['nullable', 'string', 'max:3000'],
            'content'       => ['nullable', 'string', 'max:3000'],
            'product_ids'   => ['nullable', 'array', 'max:10'],
            'product_ids.*' => ['exists:products,id'],
        ], [
            'title.required'       => 'Vui lòng nhập tiêu đề cho góc máy của bạn.',
            'title.min'            => 'Tiêu đề bài viết cần tối thiểu 5 ký tự.',
            'title.max'            => 'Tiêu đề không được vượt quá 200 ký tự.',
            'style.required'       => 'Vui lòng chọn phong cách setup phù hợp.',
            'image.required'       => 'Vui lòng tải lên ảnh chụp góc máy của bạn.',
            'image.image'          => 'Tệp tải lên phải là định dạng hình ảnh.',
            'image.mimes'          => 'Chỉ chấp nhận các định dạng ảnh: JPEG, JPG, PNG, WEBP.',
            'image.max'            => 'Dung lượng ảnh tối đa là 10MB.',
            'product_ids.*.exists' => 'Sản phẩm được gắn thẻ không tồn tại trong hệ thống.',
        ]);

        $imagePath = $request->file('image')->store('community', 'public');

        $slugBase = Str::slug($validated['title']);
        $slug = $slugBase . '-' . Str::lower(Str::random(6));

        $desc = $validated['description'] ?? $validated['content'] ?? null;

        $post = CommunityPost::create([
            'user_id'     => $request->user()->id,
            'title'       => $validated['title'],
            'slug'        => $slug,
            'style'       => $validated['style'],
            'image'       => $imagePath,
            'description' => $desc,
            'content'     => $desc,
            'status'      => CommunityPost::STATUS_PUBLISHED,
        ]);

        if (! empty($validated['product_ids'])) {
            $post->products()->sync($validated['product_ids']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng bài khoe góc máy thành công!',
                'slug'    => $post->slug,
            ]);
        }

        return redirect()->route('community.show', $post->slug)
            ->with('success', 'Chúc mừng! Góc máy của bạn đã được đăng thành công lên GearHub Community.');
    }

    /**
     * Xem chi tiết bài viết (Khung chi tiết /community/{slug} hoặc Modal AJAX).
     */
    public function show(Request $request, string $slug)
    {
        $post = $this->resolvePost($slug);

        $post->load([
            'user',
            'products.brand',
            'products.category',
            'comments.user',
        ])->loadCount(['products', 'comments']);

        // Tăng lượt xem
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
                'content'        => $post->content,
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

        // Lấy danh sách các góc máy cùng phong cách (Related Posts)
        $relatedPosts = CommunityPost::query()
            ->where('style', $post->style)
            ->where('id', '!=', $post->id)
            ->with(['user'])
            ->withCount(['products', 'comments'])
            ->latest('id')
            ->take(4)
            ->get();

        // Tổng giá trị bộ gear có trong bài
        $totalGearValue = $post->products->sum(fn ($p) => $p->effective_price);

        return view('client.community.show', [
            'post'           => $post,
            'isLiked'        => $isLiked,
            'relatedPosts'   => $relatedPosts,
            'totalGearValue' => $totalGearValue,
        ]);
    }

    /**
     * Thả tim / Bỏ thả tim cho bài viết (AJAX, bọc DB transaction).
     */
    public function like(Request $request, string $post): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success'  => false,
                'message'  => 'Vui lòng đăng nhập để thả tim cho góc máy này.',
                'redirect' => route('login'),
            ], 401);
        }

        $communityPost = $this->resolvePost($post);

        $result = DB::transaction(function () use ($user, $communityPost) {
            $existingLike = PostLike::where('community_post_id', $communityPost->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                $communityPost->decrement('likes_count');
                $liked = false;
            } else {
                PostLike::create([
                    'community_post_id' => $communityPost->id,
                    'user_id'           => $user->id,
                ]);
                $communityPost->increment('likes_count');
                $liked = true;
            }

            return [
                'liked'       => $liked,
                'likes_count' => (int) $communityPost->fresh()->likes_count,
            ];
        });

        return response()->json([
            'success'     => true,
            'liked'       => $result['liked'],
            'likes_count' => $result['likes_count'],
        ]);
    }

    /** Alias for toggleLike */
    public function toggleLike(Request $request, int|string $id)
    {
        return $this->like($request, (string) $id);
    }

    /**
     * Thêm bình luận cho bài viết (AJAX hoặc Form submit).
     */
    public function comment(Request $request, string $post)
    {
        $user = $request->user();

        if (! $user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Vui lòng đăng nhập để bình luận.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'content.required' => 'Vui lòng nhập nội dung bình luận.',
            'content.min'      => 'Bình luận tối thiểu 2 ký tự.',
            'content.max'      => 'Bình luận không quá 1000 ký tự.',
        ]);

        $communityPost = $this->resolvePost($post);

        $comment = DB::transaction(function () use ($user, $communityPost, $validated) {
            $cmt = PostComment::create([
                'community_post_id' => $communityPost->id,
                'user_id'           => $user->id,
                'content'           => $validated['content'],
            ]);

            $communityPost->increment('comments_count');

            return $cmt->load('user:id,name');
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'comments_count' => $communityPost->fresh()->comments_count,
                'comment'        => [
                    'id'         => $comment->id,
                    'user_name'  => $user->name,
                    'content'    => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                ],
            ]);
        }

        return back()->with('success', 'Bình luận của bạn đã được đăng tải thành công!');
    }

    /** Alias for storeComment */
    public function storeComment(Request $request, int|string $id)
    {
        return $this->comment($request, (string) $id);
    }
}
