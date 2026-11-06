@extends('layouts.client')

@section('title', 'Diễn đàn Khoe Góc Máy & Battlestation | GearHub Community')

@section('content')
<div 
    x-data="communityShowcase({
        userLikedPostIds: {{ json_encode($userLikedPostIds) }},
        isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},
        csrfToken: '{{ csrf_token() }}',
        routes: {
            login: '{{ route('login') }}',
            cartAdd: '{{ route('cart.add') }}'
        }
    })"
    class="bg-[#F8FAFC] min-h-screen text-gray-900 pb-20"
>
    <!-- 1. Hero Header Banner -->
    <div class="relative bg-gradient-to-b from-white via-slate-50 to-[#F8FAFC] border-b border-gray-200/80 pt-10 pb-12 sm:pb-16 overflow-hidden">
        <!-- Background subtle glow -->
        <div class="absolute inset-0 pointer-events-none opacity-40">
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[800px] h-[350px] bg-gradient-to-r from-blue-400/20 via-purple-400/20 to-pink-400/20 blur-3xl rounded-full"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
                <a href="{{ route('home') }}" class="hover:text-primary transition">Trang chủ</a>
                <span>/</span>
                <span class="text-gray-900 font-semibold">Cộng đồng</span>
                <span>/</span>
                <span class="text-primary font-medium">Khoe góc máy</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-primary text-xs font-bold uppercase tracking-wider mb-4 shadow-sm">
                        <span>📸 GEARHUB COMMUNITY SHOWCASE</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 tracking-tight leading-tight">
                        Diễn đàn Khoe Góc Máy
                    </h1>
                    <p class="mt-3 text-base sm:text-lg text-gray-600 max-w-2xl leading-relaxed">
                        Khám phá hàng trăm không gian làm việc & battlestation cực phẩm từ cộng đồng. Tìm cảm hứng decor và rinh ngay trọn bộ gear chính hãng có trong ảnh!
                    </p>
                </div>

                <!-- CTA Action Buttons -->
                <div class="flex items-center gap-3 shrink-0">
                    <button 
                        type="button" 
                        @click="showUploadInfo = true"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-primary hover:bg-primary-hover text-white text-sm font-bold shadow-lg shadow-primary/25 hover:shadow-xl transition transform active:scale-95"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Khoe góc máy của bạn</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Filter & Controls Bar -->
    <div class="sticky top-16 z-30 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-xs py-4 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                
                <!-- Setup Style Filters (Horizontal scroll on mobile) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0 scrollbar-none text-xs sm:text-sm font-semibold">
                    <!-- All Styles Pill -->
                    <a 
                        href="{{ route('community.index', array_filter(['sort' => $currentSort, 'search' => $search])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl whitespace-nowrap transition {{ empty($currentStyle) ? 'bg-gray-900 text-white shadow-md' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                    >
                        <span>🌟 Tất cả phong cách</span>
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs {{ empty($currentStyle) ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $styleCounts['all'] ?? 0 }}
                        </span>
                    </a>

                    @foreach($styles as $key => $styleInfo)
                        @php
                            $isActive = ($currentStyle === $key);
                            $urlParams = array_filter([
                                'style' => $key,
                                'sort' => $currentSort,
                                'search' => $search
                            ]);
                        @endphp
                        <a 
                            href="{{ route('community.index', $urlParams) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl whitespace-nowrap transition {{ $isActive ? 'bg-gray-900 text-white shadow-md' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200' }}"
                        >
                            <span>{{ $styleInfo['icon'] }} {{ $styleInfo['name'] }}</span>
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                                {{ $styleCounts[$key] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <!-- Search & Sort Controls -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Keyword Search -->
                    <form action="{{ route('community.index') }}" method="GET" class="relative flex-1 sm:w-64">
                        @if($currentStyle)
                            <input type="hidden" name="style" value="{{ $currentStyle }}">
                        @endif
                        @if($currentSort)
                            <input type="hidden" name="sort" value="{{ $currentSort }}">
                        @endif
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}"
                            placeholder="Tìm kiếm góc setup..." 
                            class="w-full pl-9 pr-8 py-2 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white text-xs sm:text-sm text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        @if($search)
                            <a href="{{ route('community.index', array_filter(['style' => $currentStyle, 'sort' => $currentSort])) }}" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </form>

                    <!-- Sort Select -->
                    <div class="relative shrink-0">
                        <select 
                            onchange="window.location.href = this.value"
                            class="appearance-none pl-3 pr-8 py-2 rounded-xl border border-gray-200 bg-white text-xs sm:text-sm font-semibold text-gray-700 hover:border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none cursor-pointer transition shadow-xs"
                        >
                            <option value="{{ route('community.index', array_filter(['style' => $currentStyle, 'search' => $search, 'sort' => 'trending'])) }}" {{ $currentSort === 'trending' ? 'selected' : '' }}>
                                🔥 Thịnh hành nhất
                            </option>
                            <option value="{{ route('community.index', array_filter(['style' => $currentStyle, 'search' => $search, 'sort' => 'latest'])) }}" {{ $currentSort === 'latest' ? 'selected' : '' }}>
                                ⏰ Mới nhất
                            </option>
                            <option value="{{ route('community.index', array_filter(['style' => $currentStyle, 'search' => $search, 'sort' => 'most_gears'])) }}" {{ $currentSort === 'most_gears' ? 'selected' : '' }}>
                                🛒 Nhiều gear GearHub nhất
                            </option>
                            <option value="{{ route('community.index', array_filter(['style' => $currentStyle, 'search' => $search, 'sort' => 'most_comments'])) }}" {{ $currentSort === 'most_comments' ? 'selected' : '' }}>
                                💬 Sôi nổi nhất (Nhiều bình luận)
                            </option>
                        </select>
                        <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 3. Pinterest / Masonry Showcase Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if($posts->isEmpty())
            <!-- Empty State -->
            <div class="text-center py-20 bg-white rounded-3xl border border-gray-200/80 p-8 shadow-xs max-w-xl mx-auto">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-blue-50 text-primary flex items-center justify-center text-4xl mb-4 shadow-inner">
                    📷
                </div>
                <h3 class="text-xl font-bold text-gray-900">Không tìm thấy góc máy nào</h3>
                <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto">
                    Hiện chưa có góc máy nào thuộc bộ lọc này. Hãy thử chọn phong cách khác hoặc xóa từ khóa tìm kiếm nhé!
                </p>
                <div class="mt-6">
                    <a href="{{ route('community.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition">
                        <span>Xóa tất cả bộ lọc</span>
                    </a>
                </div>
            </div>
        @else
            <!-- Masonry Grid with Tailwind CSS Columns -->
            <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-6 [column-fill:_balance]">
                
                @foreach($posts as $post)
                    @php
                        $styleInfo = $post->style_info;
                        $gearCount = $post->products_count ?? $post->products->count();
                        $isLikedByMe = in_array($post->id, $userLikedPostIds);
                    @endphp
                    
                    <div 
                        class="break-inside-avoid mb-6 rounded-2xl bg-white border border-gray-200/80 shadow-xs hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden flex flex-col group cursor-pointer"
                        @click="openModal({{ $post->id }})"
                    >
                        <!-- Image Container with Hover Effects & Badges -->
                        <div class="relative overflow-hidden bg-slate-100">
                            <img 
                                src="{{ $post->display_image }}" 
                                alt="{{ $post->title }}"
                                loading="lazy"
                                class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500"
                            >

                            <!-- Gradient shade for badge legibility -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/30 pointer-events-none opacity-80 group-hover:opacity-90 transition"></div>

                            <!-- Top Badges Row -->
                            <div class="absolute top-3 inset-x-3 flex items-center justify-between z-10">
                                <!-- Style Badge -->
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/60 backdrop-blur-md text-white border border-white/20 shadow-xs">
                                    <span>{{ $styleInfo['icon'] }}</span>
                                    <span>{{ $styleInfo['name'] }}</span>
                                </span>

                                <!-- Like Button Overlay -->
                                <button 
                                    type="button"
                                    @click.stop="toggleLike({{ $post->id }}, $event)"
                                    class="p-2 rounded-full backdrop-blur-md transition shadow-md flex items-center gap-1"
                                    :class="likedPosts[{{ $post->id }}] ? 'bg-red-500 text-white' : 'bg-black/50 text-white hover:bg-black/70 hover:scale-110'"
                                    title="Thả tim góc máy này"
                                >
                                    <svg class="w-4 h-4 transition-transform active:scale-125" :fill="likedPosts[{{ $post->id }}] ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    <span class="text-xs font-bold leading-none pr-0.5" x-text="likesCount[{{ $post->id }}] ?? {{ $post->likes_count }}">
                                        {{ $post->likes_count }}
                                    </span>
                                </button>
                            </div>

                            <!-- Bottom Badge: GearHub Tagged Gear Count -->
                            @if($gearCount > 0)
                                <div class="absolute bottom-3 left-3 z-10">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-primary/90 hover:bg-primary text-white text-xs font-bold backdrop-blur-md shadow-md border border-white/20 transition group-hover:scale-105">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span>{{ $gearCount }} gear GearHub</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Card Body Content -->
                        <div class="p-4 sm:p-5 flex flex-col flex-1">
                            <h2 class="font-bold text-gray-900 text-base leading-snug group-hover:text-primary transition line-clamp-2">
                                {{ $post->title }}
                            </h2>
                            
                            @if($post->description)
                                <p class="text-xs text-gray-500 mt-1.5 line-clamp-2 leading-relaxed">
                                    {{ $post->description }}
                                </p>
                            @endif

                            <!-- Tagged gear chip highlights -->
                            @if($post->products->isNotEmpty())
                                <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-1.5">
                                    @foreach($post->products->take(2) as $p)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium max-w-[140px] truncate">
                                            🏷️ {{ $p->name }}
                                        </span>
                                    @endforeach
                                    @if($post->products->count() > 2)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-blue-50 text-primary text-[10px] font-bold">
                                            +{{ $post->products->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Card Footer: Author + Stats -->
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                <!-- Author -->
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-[10px] uppercase shrink-0 shadow-xs">
                                        {{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-gray-700 truncate text-xs">{{ $post->user->name }}</span>
                                </div>

                                <!-- Comments Count -->
                                <div class="flex items-center gap-1 text-gray-400 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span class="text-xs font-semibold" x-text="commentsCount[{{ $post->id }}] ?? {{ $post->comments_count }}">
                                        {{ $post->comments_count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            <!-- 4. Pagination Links -->
            <div class="mt-12 flex justify-center">
                {{ $posts->links() }}
            </div>
        @endif

    </div>

    <!-- 5. Interactive Setup Detail Modal (Pinterest Pin Style) -->
    <div 
        x-show="modalOpen" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="closeModal()"
    >
        <!-- Modal Backdrop -->
        <div 
            x-show="modalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
            @click="closeModal()"
        ></div>

        <div class="min-h-screen px-4 py-6 sm:p-8 flex items-center justify-center">
            <div 
                x-show="modalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative bg-white rounded-3xl shadow-2xl max-w-5xl w-full overflow-hidden border border-gray-150 z-10 flex flex-col lg:flex-row max-h-[90vh]"
                @click.stop
            >
                <!-- Close Button -->
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-black/60 hover:bg-black text-white flex items-center justify-center backdrop-blur-md transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Left Column: Large Image -->
                <div class="lg:w-7/12 bg-black flex items-center justify-center relative overflow-hidden group select-none">
                    <template x-if="activePost">
                        <img 
                            :src="activePost.image" 
                            :alt="activePost.title"
                            class="w-full h-full max-h-[50vh] lg:max-h-[90vh] object-contain"
                        >
                    </template>
                </div>

                <!-- Right Column: Details, Gear List & Comments (Scrollable) -->
                <div class="lg:w-5/12 flex flex-col max-h-[50vh] lg:max-h-[90vh] bg-white divide-y divide-gray-100">
                    
                    <!-- Header Info -->
                    <div class="p-6 overflow-y-auto flex-1">
                        <template x-if="activePost">
                            <div>
                                <!-- Author & Action Bar -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm uppercase">
                                            <span x-text="activePost.author.initials"></span>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm" x-text="activePost.author.name"></div>
                                            <div class="text-xs text-gray-500" x-text="activePost.created_at"></div>
                                        </div>
                                    </div>

                                    <!-- Like Button -->
                                    <button 
                                        type="button"
                                        @click="toggleLike(activePost.id, $event)"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shadow-xs"
                                        :class="likedPosts[activePost.id] ? 'bg-red-500 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                                    >
                                        <svg class="w-4 h-4" :fill="likedPosts[activePost.id] ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        <span x-text="likesCount[activePost.id] ?? activePost.likes_count"></span>
                                        <span>Tim</span>
                                    </button>
                                </div>

                                <!-- Post Title & Style Badge -->
                                <div class="mb-3">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-2" :class="activePost.style_info.badge_color">
                                        <span x-text="activePost.style_info.icon"></span>
                                        <span x-text="activePost.style_info.name"></span>
                                    </span>
                                    <h2 class="text-xl font-black text-gray-900 leading-snug" x-text="activePost.title"></h2>
                                </div>

                                <!-- Post Description -->
                                <p class="text-sm text-gray-600 leading-relaxed mb-6" x-text="activePost.description"></p>

                                <!-- Tagged Products Section (GearHub Gears in Photo) -->
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                            <span>Gear có trong ảnh (<span x-text="activePost.products.length"></span>)</span>
                                        </h3>
                                    </div>

                                    <div class="space-y-2.5">
                                        <template x-for="item in activePost.products" :key="item.id">
                                            <div class="flex items-center gap-3 p-2.5 rounded-2xl border border-gray-150 bg-slate-50/70 hover:bg-white hover:border-primary/40 hover:shadow-sm transition">
                                                <img :src="item.thumbnail" :alt="item.name" class="w-12 h-12 object-cover rounded-xl bg-white border border-gray-150 shrink-0">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-[11px] font-semibold text-gray-400 uppercase" x-text="item.brand"></div>
                                                    <a :href="item.url" class="font-bold text-gray-900 text-xs hover:text-primary transition line-clamp-1" x-text="item.name"></a>
                                                    <div class="text-xs font-extrabold text-primary" x-text="item.formatted_price"></div>
                                                </div>
                                                <div class="shrink-0 flex items-center gap-1.5">
                                                    <a :href="item.url" class="px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 hover:text-primary hover:border-primary text-xs font-semibold transition" title="Xem sản phẩm">
                                                        Xem
                                                    </a>
                                                    <button 
                                                        type="button" 
                                                        @click="quickAddToCart(item.id, $event)"
                                                        class="p-1.5 rounded-lg bg-primary hover:bg-primary-hover text-white text-xs transition"
                                                        title="Thêm nhanh vào giỏ hàng"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Comments Section -->
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        <span>Bình luận (<span x-text="activePost.comments.length"></span>)</span>
                                    </h3>

                                    <div class="space-y-3">
                                        <template x-for="c in activePost.comments" :key="c.id">
                                            <div class="bg-gray-50 rounded-xl p-3 text-xs">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-bold text-gray-800" x-text="c.user_name"></span>
                                                    <span class="text-gray-400 text-[10px]" x-text="c.created_at"></span>
                                                </div>
                                                <p class="text-gray-600 leading-relaxed" x-text="c.content"></p>
                                            </div>
                                        </template>
                                        <template x-if="activePost.comments.length === 0">
                                            <p class="text-xs text-gray-400 italic">Chưa có bình luận nào. Hãy là người đầu tiên nhận xét góc máy này!</p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Sticky Bottom Comment Input Form -->
                    <div class="p-4 bg-white border-t border-gray-100">
                        <form @submit.prevent="submitComment()" class="flex items-center gap-2">
                            <input 
                                type="text" 
                                x-model="commentText" 
                                placeholder="Viết nhận xét hoặc hỏi về góc máy này..." 
                                class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                :disabled="commentSubmitting"
                            >
                            <button 
                                type="submit" 
                                :disabled="commentSubmitting || !commentText.trim()"
                                class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover disabled:opacity-50 text-white text-xs font-bold transition shrink-0"
                            >
                                <span x-show="!commentSubmitting">Gửi</span>
                                <span x-show="commentSubmitting">...</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 6. Share / Upload Info Drawer Modal -->
    <div 
        x-show="showUploadInfo" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showUploadInfo = false"
    >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showUploadInfo = false"></div>
        <div class="min-h-screen px-4 py-6 flex items-center justify-center">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full relative z-10 shadow-2xl text-center border border-gray-150">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-primary flex items-center justify-center text-3xl mx-auto mb-4">
                    ✨
                </div>
                <h3 class="text-xl font-black text-gray-900">Khoe góc máy cùng GearHub</h3>
                <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                    Tính năng đăng bài trực tiếp đang được hoàn thiện trong Tuần này! Bạn có thể chia sẻ góc máy bằng cách gửi bài dự thi về fanpage GearHub hoặc gắn hashtag <span class="font-bold text-primary">#GearHubShowcase</span> để được lên trang nhất nhé!
                </p>
                <div class="mt-6 flex flex-col gap-2">
                    <button 
                        type="button" 
                        @click="showUploadInfo = false" 
                        class="w-full py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary-hover transition"
                    >
                        Đã hiểu & Tiếp tục khám phá
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification for Add to Cart / Copy / Actions -->
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
function communityShowcase(config) {
    return {
        isLoggedIn: config.isLoggedIn,
        csrfToken: config.csrfToken,
        routes: config.routes,

        // State
        modalOpen: false,
        activePost: null,
        showUploadInfo: false,
        commentText: '',
        commentSubmitting: false,

        // Likes & Comments reactive maps
        likedPosts: {},
        likesCount: {},
        commentsCount: {},

        // Toast feedback
        toast: {
            visible: false,
            message: '',
            timeout: null
        },

        init() {
            // Seed initial liked statuses
            if (config.userLikedPostIds && Array.isArray(config.userLikedPostIds)) {
                config.userLikedPostIds.forEach(id => {
                    this.likedPosts[id] = true;
                });
            }

            // Check if URL has ?post=id
            const urlParams = new URLSearchParams(window.location.search);
            const postIdParam = urlParams.get('post');
            if (postIdParam) {
                this.openModal(postIdParam);
            }
        },

        showToast(message) {
            this.toast.message = message;
            this.toast.visible = true;
            clearTimeout(this.toast.timeout);
            this.toast.timeout = setTimeout(() => {
                this.toast.visible = false;
            }, 3000);
        },

        async openModal(postId) {
            try {
                const response = await fetch(`/community/${postId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Không thể tải chi tiết góc máy.');
                }

                const data = await response.json();
                this.activePost = data;
                this.likedPosts[data.id] = data.is_liked;
                this.likesCount[data.id] = data.likes_count;
                this.commentsCount[data.id] = data.comments_count;
                this.modalOpen = true;
            } catch (err) {
                console.error(err);
                this.showToast('Có lỗi khi tải dữ liệu bài viết.');
            }
        },

        closeModal() {
            this.modalOpen = false;
            this.activePost = null;
            this.commentText = '';
        },

        async toggleLike(postId, event) {
            if (event) {
                event.stopPropagation();
            }

            if (!this.isLoggedIn) {
                this.showToast('Vui lòng đăng nhập để thả tim bài viết!');
                setTimeout(() => {
                    window.location.href = this.routes.login;
                }, 1000);
                return;
            }

            try {
                const response = await fetch(`/community/${postId}/like`, {
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
                    this.likedPosts[postId] = data.liked;
                    this.likesCount[postId] = data.likes_count;
                    if (this.activePost && this.activePost.id === postId) {
                        this.activePost.likes_count = data.likes_count;
                        this.activePost.is_liked = data.liked;
                    }
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
            if (!this.activePost || !this.commentText.trim()) return;

            if (!this.isLoggedIn) {
                this.showToast('Vui lòng đăng nhập để bình luận!');
                setTimeout(() => {
                    window.location.href = this.routes.login;
                }, 1000);
                return;
            }

            this.commentSubmitting = true;

            try {
                const response = await fetch(`/community/${this.activePost.id}/comments`, {
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
                    this.activePost.comments.unshift(data.comment);
                    this.commentsCount[this.activePost.id] = data.comments_count;
                    this.commentText = '';
                    this.showToast('💬 Đã gửi bình luận thành công!');
                } else {
                    this.showToast(data.message || 'Không thể gửi bình luận.');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Lỗi khi gửi bình luận.');
            } finally {
                this.commentSubmitting = false;
            }
        },

        async quickAddToCart(productId, event) {
            if (event) {
                event.stopPropagation();
            }

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
                    this.showToast('🛒 Đã thêm sản phẩm vào giỏ hàng!');
                } else {
                    this.showToast(data.message || 'Không thể thêm vào giỏ hàng.');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Lỗi kết nối khi thêm giỏ hàng.');
            }
        }
    };
}
</script>
@endsection
