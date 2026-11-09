@extends('layouts.client')

@section('title', 'Smart Gear Finder Quiz | Trợ lý Chọn Gear Phù Hợp - GearHub')
@section('meta_description', 'Khám phá trợ lý trắc nghiệm chọn gear thông minh của GearHub: trả lời 4 câu hỏi đơn giản để hệ thống gợi ý gear chuẩn xác theo phong cách và ngân sách của bạn.')
@section('og_title', 'Smart Gear Finder Quiz - Tìm Gear Chuẩn Gu | GearHub')

@section('content')
<div 
    x-data="gearQuiz({
        submitUrl: '{{ route('quiz.submit') }}',
        csrfToken: '{{ csrf_token() }}'
    })"
    class="bg-[#F8FAFC] min-h-screen text-gray-900 py-10 sm:py-16"
>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-primary text-xs font-bold uppercase tracking-wider mb-3 shadow-xs">
                <span>⚡ SMART GEAR FINDER</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
                Tìm Gear Chuẩn Gu Trong 30 Giây
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600 max-w-xl mx-auto">
                Trả lời nhanh 4 câu hỏi đơn giản để AI GearHub phân tích và gợi ý những sản phẩm phù hợp nhất với nhu cầu và túi tiền của bạn.
            </p>
        </div>

        <!-- Quiz Form Card -->
        <div x-show="!completed" class="bg-white rounded-3xl p-6 sm:p-10 border border-gray-200/80 shadow-xs">
            
            <!-- Step Progress Indicator -->
            <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100">
                <span class="text-xs font-bold uppercase tracking-wider text-primary">
                    Bước <span x-text="step"></span> / 4
                </span>
                <div class="w-48 bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div 
                        class="bg-primary h-full transition-all duration-300"
                        :style="'width: ' + (step * 25) + '%'"
                    ></div>
                </div>
            </div>

            <!-- Step 1: Nhu cầu chính -->
            <div x-show="step === 1" x-transition>
                <h2 class="text-xl font-bold text-gray-900 mb-2">1. Nhu cầu sử dụng chính của bạn là gì?</h2>
                <p class="text-xs text-gray-500 mb-6">Chọn mục đích bạn dành nhiều thời gian nhất khi ngồi vào bàn.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <button 
                        type="button" 
                        @click="answers.purpose = 'gaming'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.purpose === 'gaming' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="text-3xl mb-2">🎮</div>
                        <div class="font-bold text-gray-900 text-sm">Chiến Game (FPS, MOBA)</div>
                        <div class="text-xs text-gray-500 mt-1">Ưu tiên phản hồi nhanh, polling rate cao, switch cơ nhẹ.</div>
                    </button>

                    <button 
                        type="button" 
                        @click="answers.purpose = 'office'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.purpose === 'office' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="text-3xl mb-2">💼</div>
                        <div class="font-bold text-gray-900 text-sm">Văn phòng & Học tập</div>
                        <div class="text-xs text-gray-500 mt-1">Ưu tiên êm ái, yên tĩnh (silent), kết nối đa thiết bị.</div>
                    </button>

                    <button 
                        type="button" 
                        @click="answers.purpose = 'programming'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.purpose === 'programming' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="text-3xl mb-2">💻</div>
                        <div class="font-bold text-gray-900 text-sm">Lập trình & Gõ văn bản</div>
                        <div class="text-xs text-gray-500 mt-1">Cảm giác gõ nảy, layout tiện dụng, công thái học.</div>
                    </button>
                </div>
            </div>

            <!-- Step 2: Loại thiết bị cần tìm -->
            <div x-show="step === 2" x-transition style="display: none;">
                <h2 class="text-xl font-bold text-gray-900 mb-2">2. Bạn đang muốn tìm mua món gear nào?</h2>
                <p class="text-xs text-gray-500 mb-6">Chọn dòng sản phẩm bạn muốn GearHub gợi ý ngay lúc này.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <button 
                        type="button" 
                        @click="answers.device_type = 'ban-phim-co'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.device_type === 'ban-phim-co' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="text-3xl mb-2">⌨️</div>
                        <div class="font-bold text-gray-900 text-sm">Bàn phím cơ</div>
                        <div class="text-xs text-gray-500 mt-1">Custom, layout 75%, TKL, Fullsize...</div>
                    </button>

                    <button 
                        type="button" 
                        @click="answers.device_type = 'chuot'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.device_type === 'chuot' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="text-3xl mb-2">🖱️</div>
                        <div class="font-bold text-gray-900 text-sm">Chuột Gaming / Văn phòng</div>
                        <div class="text-xs text-gray-500 mt-1">Siêu nhẹ, mắt đọc chuẩn, switch quang học...</div>
                    </button>

                    <button 
                        type="button" 
                        @click="answers.device_type = 'tai-nghe'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.device_type === 'tai-nghe' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="text-3xl mb-2">🎧</div>
                        <div class="font-bold text-gray-900 text-sm">Tai nghe & Phụ kiện</div>
                        <div class="text-xs text-gray-500 mt-1">Âm vòm 7.1, mic lọc ồn, đệm tai êm ái...</div>
                    </button>

                    <button 
                        type="button" 
                        @click="answers.device_type = 'man-hinh'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.device_type === 'man-hinh' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="text-3xl mb-2">🖥️</div>
                        <div class="font-bold text-gray-900 text-sm">Màn hình máy tính</div>
                        <div class="text-xs text-gray-500 mt-1">Gaming 144Hz+, Đồ họa 4K IPS, Cong UltraWide...</div>
                    </button>
                </div>
            </div>

            <!-- Step 3: Ngân sách dự kiến -->
            <div x-show="step === 3" x-transition style="display: none;">
                <h2 class="text-xl font-bold text-gray-900 mb-2">3. Ngân sách dự kiến của bạn là bao nhiêu?</h2>
                <p class="text-xs text-gray-500 mb-6">Chúng tôi sẽ lọc ra các sản phẩm p/p (hiệu năng/giá) tốt nhất.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <button 
                        type="button" 
                        @click="answers.budget = 'under_1m'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.budget === 'under_1m' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="font-bold text-gray-900 text-base">Dưới 1.000.000₫</div>
                        <div class="text-xs text-gray-500 mt-1">Phổ thông, học sinh sinh viên, khởi đầu ngon bổ rẻ.</div>
                    </button>

                    <button 
                        type="button" 
                        @click="answers.budget = '1m_to_3m'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.budget === '1m_to_3m' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="font-bold text-gray-900 text-base">1.000.000₫ – 3.000.000₫</div>
                        <div class="text-xs text-gray-500 mt-1">Tầm trung cực hot, build nhôm, 3-mode không dây, RGB.</div>
                    </button>

                    <button 
                        type="button" 
                        @click="answers.budget = 'above_3m'"
                        class="p-5 rounded-2xl border text-left transition"
                        :class="answers.budget === 'above_3m' ? 'border-primary bg-blue-50/50 shadow-xs ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <div class="font-bold text-gray-900 text-base">Trên 3.000.000₫</div>
                        <div class="text-xs text-gray-500 mt-1">Cao cấp, linh kiện đỉnh nóc, gear thi đấu esports.</div>
                    </button>
                </div>
            </div>

            <!-- Step 4: Tính năng ưu tiên -->
            <div x-show="step === 4" x-transition style="display: none;">
                <h2 class="text-xl font-bold text-gray-900 mb-2">4. Bạn ưu tiên những tính năng nào nhất?</h2>
                <p class="text-xs text-gray-500 mb-6">Bạn có thể chọn nhiều tiêu chí cùng lúc.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label 
                        class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition"
                        :class="answers.features.includes('wireless') ? 'border-primary bg-blue-50/50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="checkbox" value="wireless" x-model="answers.features" class="w-4 h-4 text-primary rounded">
                        <div>
                            <div class="font-bold text-gray-900 text-xs">📶 Không dây 3-Mode (Bluetooth / 2.4G)</div>
                            <div class="text-[11px] text-gray-500">Bàn làm việc gọn gàng, tiện kết nối laptop & PC</div>
                        </div>
                    </label>

                    <label 
                        class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition"
                        :class="answers.features.includes('rgb') ? 'border-primary bg-blue-50/50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="checkbox" value="rgb" x-model="answers.features" class="w-4 h-4 text-primary rounded">
                        <div>
                            <div class="font-bold text-gray-900 text-xs">🌈 Đèn LED RGB 16.8 triệu màu</div>
                            <div class="text-[11px] text-gray-500">Đồng bộ ánh sáng, tạo không gian battlestation</div>
                        </div>
                    </label>

                    <label 
                        class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition"
                        :class="answers.features.includes('hotswap') ? 'border-primary bg-blue-50/50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="checkbox" value="hotswap" x-model="answers.features" class="w-4 h-4 text-primary rounded">
                        <div>
                            <div class="font-bold text-gray-900 text-xs">🔧 Hotswap thay switch dễ dàng</div>
                            <div class="text-[11px] text-gray-500">Tự do mod phím, thử nghiệm nhiều loại switch</div>
                        </div>
                    </label>

                    <label 
                        class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition"
                        :class="answers.features.includes('lightweight') ? 'border-primary bg-blue-50/50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="checkbox" value="lightweight" x-model="answers.features" class="w-4 h-4 text-primary rounded">
                        <div>
                            <div class="font-bold text-gray-900 text-xs">🪶 Trọng lượng siêu nhẹ</div>
                            <div class="text-[11px] text-gray-500">Vẩy chuột chuẩn xác, không mỏi cổ tay khi cày lâu</div>
                        </div>
                    </label>

                    <label 
                        class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition"
                        :class="answers.features.includes('high_refresh') ? 'border-primary bg-blue-50/50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="checkbox" value="high_refresh" x-model="answers.features" class="w-4 h-4 text-primary rounded">
                        <div>
                            <div class="font-bold text-gray-900 text-xs">⚡ Tần số quét cao (144Hz - 240Hz+)</div>
                            <div class="text-[11px] text-gray-500">Hình ảnh mượt mà, không xé hình, lợi thế thi đấu</div>
                        </div>
                    </label>

                    <label 
                        class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition"
                        :class="answers.features.includes('color_accurate') ? 'border-primary bg-blue-50/50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="checkbox" value="color_accurate" x-model="answers.features" class="w-4 h-4 text-primary rounded">
                        <div>
                            <div class="font-bold text-gray-900 text-xs">🎨 Chuẩn màu đồ họa (IPS / 4K / DCI-P3)</div>
                            <div class="text-[11px] text-gray-500">Màu sắc chân thực, góc nhìn rộng, tối ưu thiết kế & dựng phim</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                <button 
                    type="button" 
                    x-show="step > 1" 
                    @click="step--"
                    class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-bold text-xs hover:bg-gray-50 transition"
                >
                    ← Quay lại
                </button>
                <div x-show="step === 1"></div>

                <button 
                    type="button" 
                    x-show="step < 4" 
                    @click="step++"
                    class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-xs shadow-sm transition"
                >
                    Tiếp tục →
                </button>

                <button 
                    type="button" 
                    x-show="step === 4" 
                    @click="submitQuiz()"
                    :disabled="loading"
                    class="px-8 py-2.5 rounded-xl bg-primary hover:bg-primary-hover disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-primary/25 transition"
                >
                    <span x-show="!loading">🎯 Xem gợi ý phù hợp nhất</span>
                    <span x-show="loading">Đang phân tích...</span>
                </button>
            </div>

        </div>

        <!-- Quiz Results -->
        <div x-show="completed" x-transition style="display: none;" class="space-y-6">
            
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-gray-900">
                        Top Gear Phù Hợp Dành Cho Bạn
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Được tính điểm tự động bằng thuật toán GearMatchEngine của GearHub.</p>
                </div>
                <button 
                    type="button" 
                    @click="resetQuiz()"
                    class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 transition"
                >
                    Làm lại Quiz
                </button>
            </div>

            <!-- Results Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <template x-for="(product, index) in results" :key="product.id">
                    <div class="bg-white rounded-3xl p-5 border border-gray-200/80 shadow-xs hover:shadow-lg transition flex flex-col justify-between">
                        <div>
                            <!-- Match Percentage Badge -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    ⭐ <span x-text="product.match_percentage"></span>% Phù hợp
                                </span>
                                <span class="text-xs font-bold text-gray-400">#<span x-text="index + 1"></span></span>
                            </div>

                            <img :src="product.thumbnail ? '/' + product.thumbnail : 'https://placehold.co/400x400/png?text=GearHub'" :alt="product.name" loading="lazy" decoding="async" class="w-full h-44 object-contain rounded-2xl bg-gray-50 p-2 mb-4">

                            <div class="text-[10px] font-bold uppercase text-gray-400" x-text="product.brand?.name"></div>
                            <h3 class="font-bold text-gray-900 text-sm line-clamp-2 mt-0.5" x-text="product.name"></h3>

                            <div class="mt-2 text-base font-black text-primary">
                                <span x-text="new Intl.NumberFormat('vi-VN').format(product.sale_price || product.price)"></span>₫
                            </div>

                            <!-- Match reasons -->
                            <div class="mt-3 pt-3 border-t border-gray-100 space-y-1">
                                <template x-for="r in product.match_reasons" :key="r">
                                    <div class="text-[11px] text-gray-600 flex items-center gap-1.5">
                                        <span class="text-emerald-500 font-bold">✓</span>
                                        <span x-text="r"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-t border-gray-100 flex gap-2">
                            <a :href="'/shop/' + product.slug" class="flex-1 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-center text-xs font-bold transition">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty match state -->
            <template x-if="results.length === 0">
                <div class="bg-white rounded-3xl p-10 text-center border border-gray-200">
                    <div class="text-3xl mb-2">🔍</div>
                    <h3 class="font-bold text-gray-900">Chưa tìm thấy sản phẩm hoàn toàn trùng khớp</h3>
                    <p class="text-xs text-gray-500 mt-1">Hãy thử nới lỏng ngân sách hoặc tiêu chí tính năng nhé.</p>
                </div>
            </template>

        </div>

    </div>
</div>

<script>
function gearQuiz(config) {
    return {
        step: 1,
        completed: false,
        loading: false,
        answers: {
            purpose: 'gaming',
            device_type: 'ban-phim-co',
            budget: '1m_to_3m',
            features: ['wireless']
        },
        results: [],

        async submitQuiz() {
            this.loading = true;
            try {
                const response = await fetch(config.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.answers)
                });

                const data = await response.json();
                if (data.success) {
                    this.results = data.matches;
                    this.completed = true;
                }
            } catch (err) {
                console.error(err);
                alert('Có lỗi xảy ra khi tính toán gợi ý.');
            } finally {
                this.loading = false;
            }
        },

        resetQuiz() {
            this.completed = false;
            this.step = 1;
        }
    };
}
</script>
@endsection
