@php
    $initialCompareItems = array_values(session('compare_items', []));
@endphp

<script>
function stickyCompareBarComponent(initialItems, csrfToken, toggleUrlBase, removeUrlBase, clearUrl) {
    return {
        items: initialItems,
        collapsed: false,
        maxItems: 4,
        loading: false,
        toastMessage: '',
        showToast: false,

        init() {
            window.addEventListener('toggle-compare', (e) => {
                const id = e.detail?.id;
                if (id) this.toggle(id);
            });
            window.addEventListener('remove-compare', (e) => {
                const id = e.detail?.id;
                if (id) this.remove(id);
            });
            window.addEventListener('clear-compare', () => {
                this.clear();
            });

            this.broadcastActiveIds();
        },

        broadcastActiveIds() {
            const ids = this.items.map(i => i.id);
            window.dispatchEvent(new CustomEvent('compare-active-ids', { detail: { ids } }));
        },

        notify(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3500);
        },

        toggle(id) {
            if (this.loading) return;
            this.loading = true;

            fetch(toggleUrlBase + '/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.items = data.items || [];
                    this.collapsed = false;
                    this.broadcastActiveIds();
                    this.notify(data.message);
                } else {
                    this.notify(data.message || 'Không thể thực hiện thao tác.');
                }
            })
            .catch(() => {
                this.loading = false;
                this.notify('Có lỗi kết nối máy chủ khi cập nhật so sánh.');
            });
        },

        remove(id) {
            if (this.loading) return;
            this.loading = true;

            fetch(removeUrlBase + '/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.items = data.items || [];
                    this.broadcastActiveIds();
                    this.notify(data.message);
                }
            })
            .catch(() => {
                this.loading = false;
            });
        },

        clear() {
            if (this.loading || this.items.length === 0) return;
            this.loading = true;

            fetch(clearUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.items = [];
                    this.broadcastActiveIds();
                    this.notify(data.message);
                }
            })
            .catch(() => {
                this.loading = false;
            });
        }
    };
}
</script>

<div 
    x-data="stickyCompareBarComponent({{ Js::from($initialCompareItems) }}, '{{ csrf_token() }}', '{{ url('/compare/toggle') }}', '{{ url('/compare/remove') }}', '{{ route('compare.clear') }}')"
    class="relative"
>
    <!-- Floating Toast Feedback riêng cho So sánh -->
    <div 
        x-show="showToast" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-28 right-6 z-50 bg-[#18181B] text-white px-4 py-3 rounded-2xl shadow-2xl border border-gray-800 flex items-center gap-2.5 text-xs font-medium max-w-sm"
        style="display: none;"
    >
        <span class="w-5 h-5 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-xs shrink-0">⚖</span>
        <span x-text="toastMessage" class="flex-1"></span>
    </div>

    <!-- Nút mở lại thanh So sánh khi bị thu gọn (Collapsed floating pill) -->
    <div 
        x-show="items.length > 0 && collapsed" 
        x-transition 
        class="fixed bottom-6 right-6 z-40"
        style="display: none;"
    >
        <button 
            type="button" 
            @click="collapsed = false" 
            class="bg-[#18181B] hover:bg-black text-white px-4 py-3 rounded-full shadow-2xl border border-gray-800 flex items-center gap-2.5 text-xs font-bold transition transform hover:scale-105 active:scale-95"
        >
            <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs">⚖</span>
            <span>So sánh (<span x-text="items.length"></span>)</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </div>

    <!-- THANH CÔNG CỤ SO SÁNH DÍNH ĐÁY CHÍNH (STICKY BAR) -->
    <div 
        x-show="items.length > 0 && !collapsed" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-250 transform"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200/90 shadow-[0_-8px_30px_rgba(0,0,0,0.12)] transition-all"
        style="display: none;"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-3 sm:gap-4">
                
                <!-- Cột trái: Tiêu đề, danh mục & số lượng đã chọn -->
                <div class="flex items-center justify-between w-full lg:w-auto gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-sm text-gray-900 tracking-tight">So sánh sản phẩm</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary text-white">
                                    <span x-text="items.length"></span>/4
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-500 font-medium">
                                <span x-show="items.length > 0" x-text="items[0]?.root_name || 'Cùng danh mục'"></span>
                                <span class="hidden sm:inline">• Tối đa 4 sản phẩm cùng loại</span>
                            </p>
                        </div>
                    </div>

                    <!-- Nút thu gọn thanh bar trên Mobile -->
                    <button 
                        type="button" 
                        @click="collapsed = true" 
                        class="lg:hidden text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition"
                        title="Thu gọn"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                <!-- Cột giữa: 4 Slots sản phẩm đang chọn so sánh -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 w-full lg:max-w-2xl">
                    <template x-for="(slotIndex) in [0, 1, 2, 3]" :key="slotIndex">
                        <div class="relative">
                            <!-- Slot ĐÃ CÓ sản phẩm -->
                            <template x-if="items[slotIndex]">
                                <div class="flex items-center gap-2 p-1.5 pr-6 rounded-xl bg-gray-50 border border-gray-200/80 hover:border-primary/50 transition relative group h-[52px]">
                                    <img 
                                        :src="items[slotIndex].thumbnail" 
                                        :alt="items[slotIndex].name" 
                                        class="w-10 h-10 object-contain rounded-lg bg-white p-0.5 border border-gray-100 shrink-0"
                                    >
                                    <div class="overflow-hidden min-w-0 flex-1">
                                        <p class="text-[11px] font-bold text-gray-900 truncate leading-tight" x-text="items[slotIndex].name"></p>
                                        <p class="text-[10px] font-extrabold text-primary pt-0.5" x-text="new Intl.NumberFormat('vi-VN').format(items[slotIndex].sale_price || items[slotIndex].price) + 'đ'"></p>
                                    </div>
                                    <!-- Nút xóa nhanh món này -->
                                    <button 
                                        type="button" 
                                        @click="remove(items[slotIndex].id)" 
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-gray-200/80 hover:bg-rose-500 hover:text-white text-gray-500 flex items-center justify-center text-xs font-bold transition"
                                        title="Bỏ chọn"
                                    >
                                        ×
                                    </button>
                                </div>
                            </template>

                            <!-- Slot TRỐNG -->
                            <template x-if="!items[slotIndex]">
                                <div class="flex items-center justify-center gap-1.5 p-1.5 rounded-xl border border-dashed border-gray-200 bg-gray-50/50 text-gray-400 h-[52px] text-[11px]">
                                    <span class="text-xs">+</span>
                                    <span>Thêm gear</span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Cột phải: Các nút hành động (Xóa tất cả, So sánh ngay, Thu gọn) -->
                <div class="flex items-center justify-end gap-3 w-full lg:w-auto">
                    <button 
                        type="button" 
                        @click="clear()" 
                        class="text-xs font-semibold text-gray-500 hover:text-rose-600 transition underline whitespace-nowrap"
                    >
                        Xóa tất cả
                    </button>

                    <!-- Nút So sánh ngay -->
                    <a 
                        href="{{ route('compare.index') }}" 
                        class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-xs sm:text-sm shadow-md shadow-primary/25 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 whitespace-nowrap"
                    >
                        <span>So sánh ngay</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>

                    <!-- Nút thu gọn trên Desktop -->
                    <button 
                        type="button" 
                        @click="collapsed = true" 
                        class="hidden lg:flex text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition shrink-0"
                        title="Thu gọn thanh so sánh"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
