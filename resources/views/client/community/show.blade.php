@extends('layouts.client')

@section('title', $post->title . ' | GearHub Community')

@section('content')
<div 
    x-data="communityPostDetail({
        postId: '{{ $post->slug }}',
        numericId: {{ $post->id }},
        initialLiked: {{ $isLiked ? 'true' : 'false' }},
        initialLikesCount: {{ $post->likes_count }},
        isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},
        csrfToken: '{{ csrf_token() }}',
        routes: {
            like: '{{ route('community.like', $post->slug) }}',
            comment: '{{ route('community.comment', $post->slug) }}',
            login: '{{ route('login') }}',
            cartAdd: '{{ route('cart.add') }}'
        }
    })"
    class="bg-[#F8FAFC] min-h-screen text-gray-900 py-8 sm:py-12"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('community.index') }}" class="hover:text-primary transition">Cộng đồng</a>
            <span>/</span>
            <a href="{{ route('community.index', ['style' => $post->style]) }}" class="hover:text-primary transition">
                {{ $post->style_info['name'] }}
            </a>
            <span>/</span>
            <span class="text-gray-900 font-semibold truncate max-w-xs sm:max-w-md">{{ $post->title }}</span>
        </nav>

        <!-- Main Layout: 2 Columns (Content 8 cols, Tagged Gear Sidebar 4 cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Main Column (8 cols): Photo, Story, Like, Comments -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- 1. Large Showcase Photo Card -->
                <div class="bg-white rounded-3xl overflow-hidden border border-gray-200/80 shadow-xs">
                    <!-- Photo Header Container -->
                    <div class="relative bg-slate-950 flex items-center justify-center overflow-hidden max-h-[640px]">
                        <img 
                            src="{{ $post->display_image }}" 
                            alt="{{ $post->title }}"
                            class="w-full h-auto max-h-[640px] object-contain select-none"
                        >

                        <!-- Style Pill Floating Overlay -->
                        <div class="absolute top-4 left-4 z-10">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-black/70 backdrop-blur-md text-white border border-white/20 shadow-md">
                                <span>{{ $post->style_info['icon'] }}</span>
                                <span>{{ $post->style_info['name'] }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Post Meta & Details Content -->
                    <div class="p-6 sm:p-8">
                        
                        <!-- Author & Engagement Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-gray-100">
                            
                            <!-- Author Profile -->
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-base uppercase shadow-sm">
                                    {{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5 font-bold text-gray-900 text-base">
                                        <span>{{ $post->user->name }}</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-primary border border-blue-200">
                                            Tác giả
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                        <span>Đăng {{ $post->created_at->diffForHumans() }}</span>
                                        <span>•</span>
                                        <span>👁️ {{ number_format($post->views_count) }} lượt xem</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Like & Share Action Buttons -->
                            <div class="flex items-center gap-2.5">
                                <!-- Heart Like Button -->
                                <button 
                                    type="button" 
                                    @click="toggleLike()"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-xs"
                                    :class="liked ? 'bg-red-500 text-white shadow-red-500/25' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                                >
                                    <svg class="w-5 h-5 transition-transform active:scale-125" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    <span x-text="likesCount"></span>
                                    <span class="hidden sm:inline">Thả tim</span>
                                </button>

                                <!-- Share Button -->
                                <button 
                                    type="button" 
                                    @click="shareLink()"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition"
                                    title="Sao chép liên kết chia sẻ"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                    <span class="hidden sm:inline">Chia sẻ</span>
                                </button>
                            </div>

                        </div>

                        <!-- Post Title -->
                        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mt-6 leading-tight">
                            {{ $post->title }}
                        </h1>

                        <!-- Post Description / Setup Story -->
                        @if($post->description)
                            <div class="mt-4 text-gray-700 text-sm sm:text-base leading-relaxed whitespace-pre-line bg-slate-50/60 p-5 rounded-2xl border border-gray-150">
                                {{ $post->description }}
                            </div>
                        @endif

                    </div>
                </div>

                <!-- 2. Comment & Discussion Section -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-xs">
                    
                    <div class="flex items-center justify-between pb-6 border-b border-gray-100 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <span>💬 Thảo luận & Hỏi đáp</span>
                            <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                {{ $post->comments_count }}
                            </span>
                        </h2>
                    </div>

                    <!-- Comment Submission Box -->
                    @auth
                        <form @submit.prevent="submitComment()" class="mb-8">
                            <div class="flex gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                    {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 space-y-2">
                                    <textarea 
                                        x-model="commentText" 
                                        rows="3" 
                                        placeholder="Nhận xét góc máy này hoặc hỏi về cấu hình, cách đi dây, mã sản phẩm..."
                                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white text-sm text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                                        :disabled="submittingComment"
                                        required
                                    ></textarea>
                                    <div class="flex justify-end">
                                        <button 
                                            type="submit" 
                                            :disabled="submittingComment || !commentText.trim()"
                                            class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover disabled:opacity-50 text-white text-xs font-bold shadow-sm transition flex items-center gap-2"
                                        >
                                            <span x-show="!submittingComment">Gửi bình luận</span>
                                            <span x-show="submittingComment">Đang gửi...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <!-- Guest Login Prompt -->
                        <div class="mb-8 p-5 rounded-2xl bg-slate-50 border border-gray-200 text-center">
                            <p class="text-sm text-gray-600 mb-3">
                                Bạn muốn giao lưu hoặc hỏi tác giả về góc máy này?
                            </p>
                            <a 
                                href="{{ route('login') }}" 
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-hover transition shadow-sm"
                            >
                                <span>Đăng nhập để bình luận</span>
                            </a>
                        </div>
                    @endauth

                    <!-- Comments Feed -->
                    <div class="space-y-4">
                        <!-- New realtime comment prepend container -->
                        <template x-for="c in newComments" :key="c.id">
                            <div class="flex gap-3 p-4 rounded-2xl bg-blue-50/50 border border-blue-100 transition">
                                <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                    <span x-text="c.user_name.substring(0, 1).toUpperCase()"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-gray-900 text-xs" x-text="c.user_name"></span>
                                        <span class="text-gray-400 text-[11px]" x-text="c.created_at"></span>
                                    </div>
                                    <p class="text-gray-700 text-xs sm:text-sm leading-relaxed" x-text="c.content"></p>
                                </div>
                            </div>
                        </template>

                        @forelse($post->comments as $comment)
                            <div class="flex gap-3 p-4 rounded-2xl bg-gray-50/70 border border-gray-100">
                                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                    {{ mb_strtoupper(mb_substr($comment->user->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-gray-900 text-xs">{{ $comment->user->name }}</span>
                                        <span class="text-gray-400 text-[11px]">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-700 text-xs sm:text-sm leading-relaxed">{{ $comment->content }}</p>
                                </div>
                            </div>
                        @empty
                            <template x-if="newComments.length === 0">
                                <div class="text-center py-8 text-gray-400 text-xs italic">
                                    Chưa có bình luận nào. Hãy là người đầu tiên đặt câu hỏi cho tác giả!
                                </div>
                            </template>
                        @endforelse
                    </div>

                </div>

            </div>

            <!-- Right Sidebar Column (4 cols): Tagged Gears List & Buy Now -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-20">
                
                <!-- Tagged Gears Box -->
                <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-xs">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <div>
                            <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Gear có trong ảnh</span>
                            </h2>
                            <p class="text-[11px] text-gray-500 mt-0.5">Sản phẩm chính hãng tại GearHub</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-primary border border-blue-200">
                            {{ $post->products->count() }} món
                        </span>
                    </div>

                    @if($post->products->isNotEmpty())
                        <!-- Total Gear Value Estimate Badge -->
                        @if($totalGearValue > 0)
                            <div class="mb-5 p-3.5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50/60 border border-blue-200/80 flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Tổng giá trị bộ gear:</span>
                                <span class="text-sm font-black text-primary">
                                    {{ number_format($totalGearValue, 0, ',', '.') }}₫
                                </span>
                            </div>
                        @endif

                        <!-- Product Cards List -->
                        <div class="space-y-3.5">
                            @foreach($post->products as $product)
                                <div class="p-3 rounded-2xl border border-gray-150 bg-slate-50/50 hover:bg-white hover:border-primary/40 hover:shadow-sm transition flex flex-col gap-2.5 group">
                                    
                                    <div class="flex items-center gap-3">
                                        <!-- Product Thumbnail -->
                                        <a href="{{ route('shop.show', $product->slug) }}" class="shrink-0 overflow-hidden rounded-xl bg-white border border-gray-200">
                                            <img 
                                                src="{{ $product->thumbnail ? asset($product->thumbnail) : 'https://placehold.co/400x400/png?text=GearHub' }}" 
                                                alt="{{ $product->name }}" 
                                                class="w-14 h-14 object-cover group-hover:scale-105 transition-transform duration-300"
                                            >
                                        </a>

                                        <!-- Info -->
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                                {{ $product->brand?->name ?? 'GearHub' }}
                                            </div>
                                            <a 
                                                href="{{ route('shop.show', $product->slug) }}" 
                                                class="text-xs font-bold text-gray-900 group-hover:text-primary transition line-clamp-1"
                                                title="{{ $product->name }}"
                                            >
                                                {{ $product->name }}
                                            </a>

                                            <!-- Price & Stock -->
                                            <div class="flex items-baseline gap-2 mt-0.5">
                                                <span class="text-xs font-black text-primary">
                                                    {{ number_format($product->effective_price, 0, ',', '.') }}₫
                                                </span>
                                                @if($product->sale_price)
                                                    <span class="text-[10px] text-gray-400 line-through">
                                                        {{ number_format($product->price, 0, ',', '.') }}₫
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons Row -->
                                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                        <!-- Link to detail -->
                                        <a 
                                            href="{{ route('shop.show', $product->slug) }}" 
                                            class="flex-1 py-1.5 rounded-xl border border-gray-200 hover:border-gray-300 bg-white text-center text-xs font-semibold text-gray-700 transition"
                                        >
                                            Xem chi tiết
                                        </a>

                                        <!-- Mua ngay / Thêm giỏ -->
                                        <button 
                                            type="button" 
                                            @click="quickAddToCart({{ $product->id }})"
                                            class="flex-1 py-1.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-center text-xs font-bold shadow-xs transition flex items-center justify-center gap-1.5 active:scale-95"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                            <span>Mua ngay</span>
                                        </button>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- No tagged gears -->
                        <div class="py-8 text-center text-gray-400 text-xs">
                            <p>Tác giả chưa gắn thẻ sản phẩm nào cho góc máy này.</p>
                        </div>
                    @endif

                </div>

                <!-- Callout Banner: Share your setup -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-3xl p-6 shadow-md text-center">
                    <div class="text-2xl mb-2">📸</div>
                    <h3 class="font-bold text-sm text-white">Bạn cũng có một góc máy ấn tượng?</h3>
                    <p class="text-xs text-slate-300 mt-1 mb-4 leading-relaxed">
                        Hãy chia sẻ battlestation của mình với cộng đồng GearHub để nhận hàng ngàn lượt tim và gắn thẻ gear yêu thích nhé!
                    </p>
                    <a 
                        href="{{ route('community.create') }}" 
                        class="inline-flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-white text-gray-900 text-xs font-bold hover:bg-slate-100 transition shadow-sm"
                    >
                        <span>Đăng góc máy ngay</span>
                    </a>
                </div>

            </div>

        </div>

        <!-- 3. Related Setups: "Góc máy cùng phong cách" -->
        @if($relatedPosts->isNotEmpty())
            <div class="mt-16 pt-10 border-t border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                            Góc máy cùng phong cách {{ $post->style_info['name'] }}
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">Khám phá thêm các góc setup cùng vibe decor</p>
                    </div>
                    <a href="{{ route('community.index', ['style' => $post->style]) }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <span>Xem tất cả</span>
                        <span>→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedPosts as $rel)
                        <a 
                            href="{{ route('community.show', $rel->slug) }}" 
                            class="bg-white rounded-2xl border border-gray-200/80 shadow-xs hover:shadow-lg hover:-translate-y-1 transition duration-300 overflow-hidden flex flex-col group"
                        >
                            <div class="relative overflow-hidden bg-slate-100 h-44">
                                <img 
                                    src="{{ $rel->display_image }}" 
                                    alt="{{ $rel->title }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                >
                                <div class="absolute bottom-2 left-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-black/60 text-white backdrop-blur-md">
                                        🛒 {{ $rel->products_count }} gear
                                    </span>
                                </div>
                            </div>
                            <div class="p-3.5 flex flex-col flex-1 justify-between">
                                <h3 class="text-xs font-bold text-gray-900 group-hover:text-primary transition line-clamp-1">
                                    {{ $rel->title }}
                                </h3>
                                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                                    <span>{{ $rel->user->name }}</span>
                                    <span>❤️ {{ $rel->likes_count }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <!-- Toast Notification for Feedback -->
    <div 
        x-show="toast.visible" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-5 py-3 rounded-2xl shadow-2xl text-sm font-semibold flex items-center gap-3 border border-gray-800"
        style="display: none;"
    >
        <span x-text="toast.message"></span>
    </div>

</div>

<script>
function communityPostDetail(config) {
    return {
        postId: config.postId,
        numericId: config.numericId,
        liked: config.initialLiked,
        likesCount: config.initialLikesCount,
        isLoggedIn: config.isLoggedIn,
        csrfToken: config.csrfToken,
        routes: config.routes,

        commentText: '',
        submittingComment: false,
        newComments: [],

        toast: {
            visible: false,
            message: '',
            timeout: null
        },

        showToast(message) {
            this.toast.message = message;
            this.toast.visible = true;
            clearTimeout(this.toast.timeout);
            this.toast.timeout = setTimeout(() => {
                this.toast.visible = false;
            }, 3000);
        },

        async toggleLike() {
            if (!this.isLoggedIn) {
                this.showToast('Vui lòng đăng nhập để thả tim!');
                setTimeout(() => {
                    window.location.href = this.routes.login;
                }, 1000);
                return;
            }

            try {
                const response = await fetch(this.routes.like, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    this.liked = data.liked;
                    this.likesCount = data.likes_count;
                    this.showToast(data.liked ? '❤️ Đã thả tim góc máy!' : '💔 Đã bỏ tim');
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch (err) {
                console.error(err);
                this.showToast('Có lỗi kết nối máy chủ.');
            }
        },

        async submitComment() {
            if (!this.commentText.trim()) return;

            if (!this.isLoggedIn) {
                this.showToast('Vui lòng đăng nhập để bình luận!');
                setTimeout(() => {
                    window.location.href = this.routes.login;
                }, 1000);
                return;
            }

            this.submittingComment = true;
            try {
                const response = await fetch(this.routes.comment, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        content: this.commentText.trim()
                    })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    this.newComments.unshift(data.comment);
                    this.commentText = '';
                    this.showToast('💬 Đã đăng bình luận thành công!');
                } else {
                    this.showToast(data.message || 'Không thể đăng bình luận.');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Lỗi khi gửi bình luận.');
            } finally {
                this.submittingComment = false;
            }
        },

        async quickAddToCart(productId) {
            try {
                const response = await fetch(this.routes.cartAdd, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: 1
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    this.showToast('🛒 Đã thêm sản phẩm vào giỏ hàng thành công!');
                } else {
                    this.showToast(data.message || 'Không thể thêm vào giỏ hàng.');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Lỗi kết nối máy chủ.');
            }
        },

        shareLink() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href);
                this.showToast('📋 Đã sao chép liên kết góc máy!');
            } else {
                this.showToast('URL: ' + window.location.href);
            }
        }
    };
}
</script>
@endsection
