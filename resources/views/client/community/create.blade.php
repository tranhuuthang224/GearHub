@extends('layouts.client')

@section('title', 'Đăng bài Khoe Góc Máy | GearHub Community')
@section('meta_description', 'Chia sẻ góc máy setup gaming hoặc không gian làm việc của bạn tới cộng đồng GearHub và gắn thẻ các món gear có trong ảnh.')

@section('content')
<div 
    x-data="communityCreatePost({
        suggestedProducts: {{ json_encode($suggestedProducts) }},
        searchUrl: '{{ route('community.search-products') }}'
    })"
    class="bg-[#F8FAFC] min-h-screen text-gray-900 py-10 sm:py-14"
>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('community.index') }}" class="hover:text-primary transition">Cộng đồng</a>
            <span>/</span>
            <span class="text-primary font-medium">Đăng bài khoe góc máy</span>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-primary text-xs font-bold uppercase tracking-wider mb-2">
                <span>📸 CHIA SẺ BATTLESTATION CỦA BẠN</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
                Khoe Góc Máy Lên GearHub
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-2 max-w-2xl">
                Tải lên không gian làm việc hoặc chiến game đỉnh chóp của bạn. Đừng quên gắn thẻ các gear GearHub có trong ảnh để mọi người cùng tham khảo nhé!
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-8 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Vui lòng kiểm tra lại thông tin:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6 text-xs text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form 
            action="{{ route('community.store') }}" 
            method="POST" 
            enctype="multipart/form-data" 
            class="space-y-8"
        >
            @csrf

            <!-- Hidden inputs for tagged gear IDs -->
            <template x-for="gear in taggedGears" :key="gear.id">
                <input type="hidden" name="product_ids[]" :value="gear.id">
            </template>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Cột trái: Tải ảnh & Chọn phong cách (5 cols) -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <!-- 1. Upload ảnh xem trước -->
                    <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-xs">
                        <label class="block text-sm font-bold text-gray-900 mb-2">
                            Ảnh chụp góc máy <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-4">
                            Ảnh ngang hoặc dọc rõ nét, ánh sáng đẹp mắt (tối đa 10MB).
                        </p>

                        <!-- Drag & Drop Zone -->
                        <div 
                            class="relative border-2 border-dashed rounded-2xl p-4 transition text-center cursor-pointer overflow-hidden flex flex-col items-center justify-center min-h-[260px]"
                            :class="previewUrl ? 'border-primary/40 bg-slate-50' : 'border-gray-300 hover:border-primary/60 hover:bg-slate-50'"
                            @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false"
                            @drop.prevent="handleDrop($event)"
                            @click="$refs.fileInput.click()"
                        >
                            <input 
                                type="file" 
                                name="image" 
                                x-ref="fileInput" 
                                accept="image/jpeg,image/png,image/webp,image/jpg" 
                                class="hidden"
                                @change="handleFileSelect($event)"
                                required
                            >

                            <!-- Preview container when image selected -->
                            <template x-if="previewUrl">
                                <div class="relative w-full h-full group">
                                    <img :src="previewUrl" alt="Xem trước ảnh" class="w-full h-64 object-cover rounded-xl shadow-xs">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition rounded-xl flex items-center justify-center gap-2">
                                        <button 
                                            type="button" 
                                            @click.stop="$refs.fileInput.click()"
                                            class="px-3 py-1.5 rounded-lg bg-white/90 text-gray-900 text-xs font-bold hover:bg-white transition"
                                        >
                                            Đổi ảnh khác
                                        </button>
                                        <button 
                                            type="button" 
                                            @click.stop="removeImage()"
                                            class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition"
                                        >
                                            Xóa ảnh
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Placeholder when no image -->
                            <template x-if="!previewUrl">
                                <div class="py-8 px-4 flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-primary flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-800">Nhấp để tải ảnh lên</span>
                                    <span class="text-xs text-gray-400 mt-1">hoặc kéo và thả tệp vào đây</span>
                                    <span class="text-[11px] text-gray-400 mt-2">Hỗ trợ JPG, PNG, WEBP lên đến 10MB</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 2. Chọn phong cách setup -->
                    <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-xs">
                        <label class="block text-sm font-bold text-gray-900 mb-2">
                            Phong cách Setup <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-4">
                            Chọn phong cách đúng nhất để cộng đồng dễ dàng lọc và tìm kiếm.
                        </p>

                        <div class="space-y-2.5">
                            @foreach($styles as $key => $style)
                                <label 
                                    class="flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer transition select-none"
                                    :class="selectedStyle === '{{ $key }}' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                >
                                    <div class="flex items-center gap-3">
                                        <input 
                                            type="radio" 
                                            name="style" 
                                            value="{{ $key }}" 
                                            x-model="selectedStyle"
                                            class="w-4 h-4 text-primary focus:ring-primary border-gray-300"
                                            {{ old('style', 'minimalist') === $key ? 'checked' : '' }}
                                        >
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">{{ $style['icon'] }}</span>
                                            <span class="text-sm font-bold text-gray-800">{{ $style['name'] }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md {{ $style['badge_color'] }}">
                                        {{ $style['slug'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- Cột phải: Thông tin & Gắn thẻ Gear (7 cols) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Thông tin cơ bản -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/80 shadow-xs space-y-5">
                        
                        <!-- Tiêu đề -->
                        <div>
                            <label for="title" class="block text-sm font-bold text-gray-900 mb-2">
                                Tiêu đề góc máy <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                value="{{ old('title') }}" 
                                placeholder="Ví dụ: Góc làm việc Tối giản trắng xám cho lập trình viên..." 
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white text-sm text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                                required
                            >
                        </div>

                        <!-- Mô tả -->
                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-900 mb-2">
                                Mô tả không gian & Chia sẻ kinh nghiệm
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4" 
                                placeholder="Chia sẻ cách bạn phối màu, đi dây giấu nguồn, loại bàn ghế, đèn LED hoặc cảm giác gõ phím khi sử dụng góc máy này..."
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white text-sm text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition leading-relaxed"
                            >{{ old('description') }}</textarea>
                        </div>

                    </div>

                    <!-- TÍNH NĂNG ĐỘC ĐÁO: GẮN THẺ (TAG) SẢN PHẨM GEARHUB -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/80 shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs">🏷️</span>
                                <span>Gắn thẻ (Tag) sản phẩm GearHub trong ảnh</span>
                            </label>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-50 text-primary border border-blue-200">
                                Đã gắn: <span x-text="taggedGears.length">0</span> gear
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-4">
                            Tìm và tag các gear bạn đang dùng (bàn phím, chuột, tai nghe, pad chuột...). Sản phẩm sẽ hiển thị trên góc máy để người xem có thể mua ngay!
                        </p>

                        <!-- Ô tìm kiếm sản phẩm real-time -->
                        <div class="relative mb-4">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    x-model="searchQuery" 
                                    @input.debounce.300ms="searchGear()"
                                    @focus="searchFocused = true"
                                    placeholder="Gõ tên phím cơ, chuột, tai nghe GearHub (ví dụ: Aula, Logitech, Akko...)" 
                                    class="w-full pl-10 pr-10 py-3 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white text-sm text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                                >
                                <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span x-show="isSearching" class="absolute right-3.5 top-3.5 text-xs text-primary font-bold animate-pulse">
                                    Đang tìm...
                                </span>
                            </div>

                            <!-- Dropdown kết quả gợi ý -->
                            <div 
                                x-show="searchFocused && searchResults.length > 0" 
                                @click.away="searchFocused = false"
                                x-transition
                                class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl border border-gray-200 shadow-xl z-30 max-h-72 overflow-y-auto divide-y divide-gray-100 p-2"
                                style="display: none;"
                            >
                                <template x-for="item in searchResults" :key="item.id">
                                    <div 
                                        @click="addGear(item)"
                                        class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-blue-50/70 transition cursor-pointer"
                                    >
                                        <img :src="item.thumbnail" :alt="item.name" loading="lazy" decoding="async" class="w-11 h-11 object-cover rounded-lg bg-gray-100 shrink-0 border border-gray-150">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[10px] font-bold text-gray-400 uppercase" x-text="item.brand"></div>
                                            <div class="text-xs font-bold text-gray-900 truncate" x-text="item.name"></div>
                                            <div class="text-xs font-extrabold text-primary" x-text="item.formatted_price"></div>
                                        </div>
                                        <button 
                                            type="button" 
                                            class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                            :class="isTagged(item.id) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-primary text-white hover:bg-primary-hover'"
                                            :disabled="isTagged(item.id)"
                                        >
                                            <span x-text="isTagged(item.id) ? 'Đã tag' : '+ Gắn thẻ'"></span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Danh sách gear đã gắn thẻ (Selected Tagged Gears) -->
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                                Danh sách gear có trong ảnh:
                            </div>

                            <!-- Empty tagged gear state -->
                            <template x-if="taggedGears.length === 0">
                                <div class="p-6 rounded-2xl border-2 border-dashed border-gray-200 text-center text-xs text-gray-400">
                                    Chưa có sản phẩm nào được gắn thẻ. Hãy tìm kiếm ở trên hoặc chọn nhanh từ danh mục gợi ý bên dưới!
                                </div>
                            </template>

                            <!-- Tagged gear cards -->
                            <div class="space-y-2">
                                <template x-for="(gear, index) in taggedGears" :key="gear.id">
                                    <div class="flex items-center justify-between p-2.5 rounded-2xl border border-blue-200 bg-blue-50/40 shadow-2xs">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img :src="gear.thumbnail" :alt="gear.name" loading="lazy" decoding="async" class="w-10 h-10 object-cover rounded-xl bg-white border border-gray-150 shrink-0">
                                            <div class="min-w-0">
                                                <div class="text-[10px] font-bold text-primary uppercase" x-text="gear.brand"></div>
                                                <div class="text-xs font-bold text-gray-900 truncate max-w-xs sm:max-w-md" x-text="gear.name"></div>
                                                <div class="text-xs font-extrabold text-gray-700" x-text="gear.formatted_price"></div>
                                            </div>
                                        </div>
                                        <button 
                                            type="button" 
                                            @click="removeGear(gear.id)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Xóa gắn thẻ sản phẩm này"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Gợi ý nhanh từ kho hàng GearHub -->
                        <div class="mt-5 pt-4 border-t border-gray-100">
                            <div class="text-xs font-bold text-gray-500 mb-2.5">
                                Gợi ý gear phổ biến:
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="item in suggestedProducts" :key="item.id">
                                    <button 
                                        type="button"
                                        @click="addGear(item)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-medium transition"
                                        :class="isTagged(item.id) ? 'bg-gray-100 border-gray-200 text-gray-400 line-through cursor-not-allowed' : 'bg-white border-gray-200 hover:border-primary text-gray-700 hover:text-primary hover:shadow-xs'"
                                        :disabled="isTagged(item.id)"
                                    >
                                        <span>+</span>
                                        <span x-text="item.name" class="max-w-[140px] truncate"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Nút gửi bài viết -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a 
                            href="{{ route('community.index') }}" 
                            class="px-5 py-3 rounded-2xl border border-gray-200 text-gray-700 hover:bg-gray-100 text-sm font-bold transition"
                        >
                            Hủy bỏ
                        </a>
                        <button 
                            type="submit" 
                            class="px-8 py-3 rounded-2xl bg-primary hover:bg-primary-hover text-white text-sm font-bold shadow-lg shadow-primary/25 hover:shadow-xl transition transform active:scale-95 flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Đăng góc máy ngay</span>
                        </button>
                    </div>

                </div>

            </div>
        </form>

    </div>
</div>

<script>
function communityCreatePost(config) {
    return {
        suggestedProducts: config.suggestedProducts || [],
        searchUrl: config.searchUrl,

        // Image upload preview state
        previewUrl: null,
        dragover: false,
        selectedStyle: '{{ old('style', 'minimalist') }}',

        // Tagged Gear state
        searchQuery: '',
        isSearching: false,
        searchFocused: false,
        searchResults: [],
        taggedGears: [],

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.previewUrl = URL.createObjectURL(file);
            }
        },

        handleDrop(event) {
            this.dragover = false;
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                this.$refs.fileInput.files = event.dataTransfer.files;
                this.previewUrl = URL.createObjectURL(file);
            }
        },

        removeImage() {
            this.previewUrl = null;
            this.$refs.fileInput.value = '';
        },

        async searchGear() {
            if (!this.searchQuery.trim()) {
                this.searchResults = this.suggestedProducts;
                return;
            }

            this.isSearching = true;
            try {
                const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(this.searchQuery.trim())}`);
                if (response.ok) {
                    this.searchResults = await response.json();
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isSearching = false;
            }
        },

        addGear(item) {
            if (this.isTagged(item.id)) return;
            this.taggedGears.push(item);
            this.searchQuery = '';
            this.searchFocused = false;
        },

        removeGear(id) {
            this.taggedGears = this.taggedGears.filter(g => g.id !== id);
        },

        isTagged(id) {
            return this.taggedGears.some(g => g.id === id);
        }
    };
}
</script>
@endsection
