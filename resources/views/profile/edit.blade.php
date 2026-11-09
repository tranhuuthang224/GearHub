@extends('layouts.client', ['title' => 'Quản lý Hồ sơ cá nhân - GearHub'])

@section('title', 'Quản Lý Hồ Sơ Cá Nhân | GearHub')

@section('content')
<div 
    x-data="{
        activeTab: '{{ request('tab', session('active_tab', 'profile')) }}',
        addressModalOpen: false,
        isEditingAddress: false,
        addressFormAction: '{{ route('profile.addresses.store') }}',
        addressFormMethod: 'POST',
        addressData: {
            id: null,
            receiver_name: '',
            phone: '',
            address_line: '',
            is_default: false
        },
        openAddAddress() {
            this.isEditingAddress = false;
            this.addressFormAction = '{{ route('profile.addresses.store') }}';
            this.addressFormMethod = 'POST';
            this.addressData = {
                id: null,
                receiver_name: @js(old('receiver_name', $user->name)),
                phone: @js(old('phone', $user->phone ?? '')),
                address_line: '',
                is_default: {{ $addresses->isEmpty() ? 'true' : 'false' }}
            };
            this.addressModalOpen = true;
        },
        openEditAddress(addr) {
            this.isEditingAddress = true;
            this.addressFormAction = '{{ url('/profile/addresses') }}/' + addr.id;
            this.addressFormMethod = 'POST'; // Sẽ dùng @method('PUT')
            this.addressData = {
                id: addr.id,
                receiver_name: addr.receiver_name,
                phone: addr.phone,
                address_line: addr.address_line,
                is_default: Boolean(addr.is_default)
            };
            this.addressModalOpen = true;
        },
        deletePostId: null,
        deletePostModalOpen: false,
        confirmDeletePost(id) {
            this.deletePostId = id;
            this.deletePostModalOpen = true;
        }
    }"
    class="bg-[#F8FAFC] min-h-screen text-gray-900 py-8 sm:py-12"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- 1. BREADCRUMB -->
        <nav class="flex items-center gap-2 text-xs text-gray-400" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-primary transition flex items-center gap-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Trang chủ
            </a>
            <span>/</span>
            <span class="text-gray-900 font-semibold">Hồ sơ tài khoản</span>
        </nav>

        <!-- 2. THÔNG BÁO FLASH MESSAGE -->
        @if (session('success') || session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-emerald-800 text-sm font-semibold shadow-sm animate-fade-in">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>
                        {{ session('success') ?? (session('status') === 'password-updated' ? 'Mật khẩu đã được cập nhật thành công!' : 'Hồ sơ tài khoản đã được lưu thành công!') }}
                    </span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm font-medium space-y-1.5 shadow-sm">
                <div class="font-bold flex items-center gap-2 text-rose-900">
                    <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>Có lỗi xảy ra, vui lòng kiểm tra lại:</span>
                </div>
                <ul class="list-disc list-inside pl-1 text-xs space-y-1 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 3. PROFILE HEADER BANNER (Card tổng quan người dùng) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm">
            <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6">
                
                <!-- Avatar & Tên User -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                    <div class="relative group">
                        @if ($user->avatar_url)
                            <img 
                                src="{{ $user->avatar_url }}" 
                                alt="{{ $user->name }}" 
                                loading="lazy"
                                decoding="async"
                                class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover ring-4 ring-primary/20 shadow-lg"
                            >
                        @else
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-br from-gray-900 to-gray-700 text-white flex items-center justify-center font-black text-3xl sm:text-4xl shadow-lg uppercase ring-4 ring-gray-100">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                        @endif
                        <button 
                            type="button" 
                            @click="activeTab = 'profile'; $nextTick(() => document.getElementById('avatar-input-field')?.click())"
                            class="absolute bottom-1 right-1 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shadow-md hover:bg-primary-hover transition transform hover:scale-110"
                            title="Đổi ảnh đại diện"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                                {{ $user->name }}
                            </h1>
                            @if ($user->role === 'admin')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                    ⚡ Quản trị viên
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    ✓ Thành viên GearHub
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-gray-500 font-medium flex items-center justify-center sm:justify-start gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                            <span>{{ $user->email }}</span>
                        </p>
                        @if ($user->phone)
                            <p class="text-xs sm:text-sm text-gray-500 font-medium flex items-center justify-center sm:justify-start gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ $user->phone }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Thống kê hoạt động cá nhân -->
                <div class="grid grid-cols-3 gap-2 sm:gap-4 w-full md:w-auto">
                    <button 
                        type="button" 
                        @click="activeTab = 'addresses'"
                        class="p-3 sm:p-4 rounded-2xl border text-center transition hover:border-primary/50 group"
                        :class="activeTab === 'addresses' ? 'bg-red-50/40 border-primary' : 'bg-gray-50/80 border-gray-200/80'"
                    >
                        <span class="block text-xl sm:text-2xl font-black text-gray-900 group-hover:text-primary transition">
                            {{ $addresses->count() }}
                        </span>
                        <span class="text-[11px] text-gray-500 font-medium uppercase tracking-wider block mt-0.5">
                            📍 Địa chỉ
                        </span>
                    </button>

                    <button 
                        type="button" 
                        @click="activeTab = 'posts'"
                        class="p-3 sm:p-4 rounded-2xl border text-center transition hover:border-primary/50 group"
                        :class="activeTab === 'posts' ? 'bg-red-50/40 border-primary' : 'bg-gray-50/80 border-gray-200/80'"
                    >
                        <span class="block text-xl sm:text-2xl font-black text-gray-900 group-hover:text-primary transition">
                            {{ $myPosts->total() }}
                        </span>
                        <span class="text-[11px] text-gray-500 font-medium uppercase tracking-wider block mt-0.5">
                            🖥️ Góc máy
                        </span>
                    </button>

                    <a 
                        href="{{ url('/orders') }}" 
                        class="p-3 sm:p-4 rounded-2xl border bg-gray-50/80 border-gray-200/80 text-center transition hover:border-primary/50 group"
                    >
                        <span class="block text-xl sm:text-2xl font-black text-gray-900 group-hover:text-primary transition">
                            {{ $user->orders()->count() }}
                        </span>
                        <span class="text-[11px] text-gray-500 font-medium uppercase tracking-wider block mt-0.5">
                            📦 Đơn hàng
                        </span>
                    </a>
                </div>

            </div>
        </div>

        <!-- 4. NAVIGATION TABS -->
        <div class="flex items-center gap-2 border-b border-gray-200 overflow-x-auto pb-1 scrollbar-none">
            <button 
                type="button" 
                @click="activeTab = 'profile'"
                class="px-5 py-3 text-sm font-bold rounded-2xl transition flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'profile' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Thông tin & Mật khẩu</span>
            </button>

            <button 
                type="button" 
                @click="activeTab = 'addresses'"
                class="px-5 py-3 text-sm font-bold rounded-2xl transition flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'addresses' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Sổ địa chỉ nhận hàng</span>
                <span 
                    class="text-xs px-2 py-0.5 rounded-full font-black"
                    :class="activeTab === 'addresses' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'"
                >
                    {{ $addresses->count() }}
                </span>
            </button>

            <button 
                type="button" 
                @click="activeTab = 'posts'"
                class="px-5 py-3 text-sm font-bold rounded-2xl transition flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'posts' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Góc máy của tôi</span>
                <span 
                    class="text-xs px-2 py-0.5 rounded-full font-black"
                    :class="activeTab === 'posts' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'"
                >
                    {{ $myPosts->total() }}
                </span>
            </button>

            <a 
                href="{{ url('/orders') }}"
                class="px-5 py-3 text-sm font-bold rounded-2xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition flex items-center gap-2 whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Đơn hàng của tôi →</span>
            </a>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: THÔNG TIN CÁ NHÂN & ĐỔI MẬT KHẨU   -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'profile'" x-transition class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- CỘT 1: CẬP NHẬT THÔNG TIN CÁ NHÂN (7 COLS) -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6">
                    <div class="pb-4 border-b border-gray-100">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 border border-red-100 text-primary text-xs font-bold uppercase tracking-wider mb-2">
                            <span>Thông tin cơ bản</span>
                        </div>
                        <h2 class="text-xl font-black text-gray-900 tracking-tight">Cập nhật thông tin cá nhân</h2>
                        <p class="text-xs text-gray-500 mt-1">Quản lý họ tên, số điện thoại, email và ảnh đại diện hiển thị</p>
                    </div>

                    <form 
                        action="{{ route('profile.update') }}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="space-y-5"
                        x-data="{
                            previewUrl: @js($user->avatar_url),
                            handleAvatarChange(e) {
                                const file = e.target.files[0];
                                if (file) {
                                    this.previewUrl = URL.createObjectURL(file);
                                }
                            }
                        }"
                    >
                        @csrf
                        @method('PATCH')

                        <!-- Avatar Picker với Preview -->
                        <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-gray-100 flex flex-col sm:flex-row items-center gap-5">
                            <div class="relative shrink-0">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="Avatar Preview" class="w-20 h-20 rounded-full object-cover ring-2 ring-primary/30 shadow-md">
                                </template>
                                <template x-if="!previewUrl">
                                    <div class="w-20 h-20 rounded-full bg-gray-800 text-white flex items-center justify-center font-bold text-xl uppercase shadow-md">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                </template>
                            </div>

                            <div class="space-y-1.5 text-center sm:text-left flex-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Ảnh đại diện (Avatar)
                                </label>
                                <p class="text-[11px] text-gray-500">Hỗ trợ ảnh JPG, PNG, WEBP. Tối đa 2MB.</p>
                                
                                <div class="pt-1">
                                    <input 
                                        type="file" 
                                        name="avatar" 
                                        id="avatar-input-field"
                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                        @change="handleAvatarChange"
                                        class="text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-black file:cursor-pointer transition"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Họ và tên -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Họ và tên <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                value="{{ old('name', $user->name) }}" 
                                required
                                placeholder="Ví dụ: Nguyễn Văn An"
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                            >
                        </div>

                        <!-- Số điện thoại & Email -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Số điện thoại -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Số điện thoại
                                </label>
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    value="{{ old('phone', $user->phone) }}" 
                                    placeholder="Ví dụ: 0912345678"
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                                >
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Địa chỉ Email <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    value="{{ old('email', $user->email) }}" 
                                    required
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                                >
                            </div>
                        </div>

                        <!-- Nút lưu thay đổi -->
                        <div class="pt-2">
                            <button 
                                type="submit" 
                                class="px-6 py-3.5 rounded-2xl bg-primary hover:bg-primary-hover text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-primary/20 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Lưu thay đổi hồ sơ</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CỘT 2: ĐỔI MẬT KHẨU TÀI KHOẢN (5 COLS) -->
                <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6">
                    <div class="pb-4 border-b border-gray-100">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-2">
                            <span>Bảo mật tài khoản</span>
                        </div>
                        <h2 class="text-xl font-black text-gray-900 tracking-tight">Đổi mật khẩu</h2>
                        <p class="text-xs text-gray-500 mt-1">Đảm bảo tài khoản an toàn với mật khẩu tối thiểu 8 ký tự</p>
                    </div>

                    <form 
                        action="{{ route('password.update') }}" 
                        method="POST" 
                        class="space-y-4"
                        x-data="{ showCurr: false, showNew: false, showConfirm: false }"
                    >
                        @csrf
                        @method('PUT')

                        <!-- Mật khẩu hiện tại -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Mật khẩu hiện tại <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showCurr ? 'text' : 'password'" 
                                    name="current_password" 
                                    required 
                                    placeholder="Nhập mật khẩu đang dùng"
                                    class="w-full px-4 pr-11 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                                >
                                <button 
                                    type="button" 
                                    @click="showCurr = !showCurr" 
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                                >
                                    <span x-text="showCurr ? '🙈' : '👁️'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Mật khẩu mới -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Mật khẩu mới <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showNew ? 'text' : 'password'" 
                                    name="password" 
                                    required 
                                    placeholder="Tối thiểu 8 ký tự"
                                    class="w-full px-4 pr-11 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                                >
                                <button 
                                    type="button" 
                                    @click="showNew = !showNew" 
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                                >
                                    <span x-text="showNew ? '🙈' : '👁️'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Xác nhận mật khẩu mới -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Xác nhận mật khẩu mới <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showConfirm ? 'text' : 'password'" 
                                    name="password_confirmation" 
                                    required 
                                    placeholder="Nhập lại mật khẩu mới"
                                    class="w-full px-4 pr-11 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                                >
                                <button 
                                    type="button" 
                                    @click="showConfirm = !showConfirm" 
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                                >
                                    <span x-text="showConfirm ? '🙈' : '👁️'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button 
                                type="submit" 
                                class="w-full py-3.5 px-6 rounded-2xl bg-gray-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider shadow-sm transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Cập nhật mật khẩu</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: SỔ ĐỊA CHỈ NHẬN HÀNG               -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'addresses'" x-transition class="space-y-6" style="display: none;">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6">
                
                <!-- Header của Tab Sổ địa chỉ -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-gray-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">
                            <span>Sổ địa chỉ</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                            Địa chỉ giao hàng đã lưu
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Địa chỉ mặc định sẽ tự động được chọn và điền sẵn khi bạn tiến hành thanh toán đơn hàng.
                        </p>
                    </div>

                    <button 
                        type="button" 
                        @click="openAddAddress()"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs font-bold uppercase tracking-wider shadow-md shadow-primary/20 transition transform hover:-translate-y-0.5 active:scale-95 shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Thêm địa chỉ mới</span>
                    </button>
                </div>

                <!-- Danh sách Card Địa chỉ -->
                @if ($addresses->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        @foreach ($addresses as $addr)
                            <div class="p-5 sm:p-6 rounded-3xl border transition flex flex-col justify-between {{ $addr->is_default ? 'bg-gradient-to-br from-emerald-50/40 via-white to-white border-emerald-300 ring-2 ring-emerald-500/10 shadow-sm' : 'bg-white border-gray-200/80 hover:border-gray-300' }}">
                                
                                <div class="space-y-3">
                                    <!-- Header Card: Tên + Badge Mặc định -->
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-8 h-8 rounded-xl {{ $addr->is_default ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center text-sm font-bold shrink-0">
                                                📍
                                            </span>
                                            <div>
                                                <h3 class="font-bold text-gray-900 text-sm sm:text-base leading-snug">
                                                    {{ $addr->receiver_name }}
                                                </h3>
                                                <p class="text-xs text-gray-500 font-medium">{{ $addr->phone }}</p>
                                            </div>
                                        </div>

                                        @if ($addr->is_default)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                                <span>✓ Mặc định</span>
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Địa chỉ chi tiết -->
                                    <p class="text-xs text-gray-700 leading-relaxed font-medium bg-[#F8FAFC] p-3 rounded-2xl border border-gray-100">
                                        {{ $addr->address_line }}
                                    </p>
                                </div>

                                <!-- Thanh công cụ nút bấm -->
                                <div class="pt-4 mt-4 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                                    <div>
                                        @if (!$addr->is_default)
                                            <form action="{{ route('profile.addresses.default', $addr->id) }}" method="POST">
                                                @csrf
                                                <button 
                                                    type="submit" 
                                                    class="text-xs font-bold text-gray-600 hover:text-emerald-700 transition flex items-center gap-1 py-1"
                                                >
                                                    <span>Đặt làm mặc định</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                <span>Sẽ tự động điền khi mua hàng</span>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button 
                                            type="button" 
                                            @click="openEditAddress({{ Js::from($addr) }})"
                                            class="px-3 py-1.5 rounded-xl border border-gray-200 hover:border-gray-300 bg-white text-xs font-bold text-gray-700 hover:text-primary transition"
                                        >
                                            Sửa
                                        </button>

                                        <form 
                                            action="{{ route('profile.addresses.destroy', $addr->id) }}" 
                                            method="POST" 
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa địa chỉ nhận hàng này?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="px-3 py-1.5 rounded-xl border border-gray-200 hover:border-rose-200 hover:bg-rose-50 text-xs font-bold text-gray-500 hover:text-rose-600 transition"
                                            >
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State khi chưa có địa chỉ -->
                    <x-empty-state 
                        icon="📍"
                        title="Bạn chưa lưu địa chỉ nhận hàng nào"
                        description="Lưu trước địa chỉ nhận hàng giúp bạn đặt hàng nhanh chóng chỉ với 1 cú click ở bước Checkout."
                        action-text="+ Thêm địa chỉ đầu tiên"
                        action-click="openAddAddress()"
                        class="my-4 py-10 bg-[#F8FAFC]"
                    />
                @endif

            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: GÓC MÁY CỦA TÔI (COMMUNITY POSTS)   -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'posts'" x-transition class="space-y-6" style="display: none;">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6">
                
                <!-- Header của Tab Góc máy của tôi -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-gray-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 border border-purple-100 text-purple-800 text-xs font-bold uppercase tracking-wider mb-2">
                            <span>Diễn đàn Khoe Góc Máy</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                            Góc máy setup của tôi ({{ $myPosts->total() }})
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Quản lý các bài viết không gian làm việc & chiến game bạn đã chia sẻ với cộng đồng GearHub.
                        </p>
                    </div>

                    <a 
                        href="{{ route('community.create') }}" 
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-red-600 via-primary to-rose-600 hover:from-primary hover:to-red-700 text-white text-xs font-bold uppercase tracking-wider shadow-md shadow-primary/20 transition transform hover:-translate-y-0.5 active:scale-95 shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Đăng góc máy mới</span>
                    </a>
                </div>

                <!-- Lưới hiển thị bài viết góc máy -->
                @if ($myPosts->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($myPosts as $post)
                            <div class="rounded-3xl border border-gray-200/90 overflow-hidden bg-white shadow-xs hover:shadow-md transition flex flex-col group">
                                
                                <!-- Ảnh bài viết kèm Badges -->
                                <div class="relative aspect-[16/10] bg-gray-100 overflow-hidden">
                                    <img 
                                        src="{{ $post->image }}" 
                                        alt="{{ $post->title }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                        loading="lazy"
                                    >

                                    <!-- Badge Phong cách -->
                                    <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                        @php
                                            $styleMeta = App\Models\CommunityPost::STYLES[$post->style] ?? null;
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider backdrop-blur-md bg-black/60 text-white border border-white/20">
                                            {{ $styleMeta['icon'] ?? '◽' }} {{ $styleMeta['name'] ?? $post->style }}
                                        </span>
                                    </div>

                                    <!-- Badge Trạng thái duyệt -->
                                    <div class="absolute top-3 right-3">
                                        @if ($post->status === 'published')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500 text-white shadow-sm">
                                                ✓ Đã duyệt
                                            </span>
                                        @elseif ($post->status === 'pending')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-white shadow-sm">
                                                ⏳ Chờ duyệt
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-600 text-white shadow-sm">
                                                🔒 Tạm ẩn
                                            </span>
                                        @endif
                                    </div>

                                    @if ($post->products_count > 0)
                                        <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-full text-[11px] font-bold text-gray-900 shadow-sm flex items-center gap-1">
                                            <span>🏷️</span>
                                            <span>{{ $post->products_count }} Gear</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Nội dung tóm tắt & thống kê -->
                                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                    <div class="space-y-1.5">
                                        <h3 class="font-bold text-gray-900 text-sm sm:text-base line-clamp-2 leading-snug group-hover:text-primary transition">
                                            {{ $post->title }}
                                        </h3>
                                        <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                            {{ $post->description ?? $post->content }}
                                        </p>
                                    </div>

                                    <!-- Thống kê Tương tác & Nút hành động -->
                                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                        <div class="flex items-center gap-3 font-semibold">
                                            <span class="flex items-center gap-1 text-rose-500">
                                                ❤️ {{ $post->likes_count }}
                                            </span>
                                            <span class="flex items-center gap-1 text-gray-600">
                                                💬 {{ $post->comments_count }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a 
                                                href="{{ route('community.show', $post->slug) }}" 
                                                class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-primary hover:text-white text-gray-700 font-bold transition text-xs"
                                                title="Xem bài viết"
                                            >
                                                Xem
                                            </a>

                                            <button 
                                                type="button" 
                                                @click="confirmDeletePost({{ $post->id }})"
                                                class="px-2.5 py-1.5 rounded-xl border border-gray-200 hover:border-rose-200 hover:bg-rose-50 text-gray-400 hover:text-rose-600 font-bold transition text-xs"
                                                title="Xóa bài viết"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Phân trang danh sách bài viết -->
                    <div class="pt-4">
                        {{ $myPosts->appends(['tab' => 'posts'])->links() }}
                    </div>
                @else
                    <!-- Empty State khi chưa có bài viết góc máy -->
                    <x-empty-state 
                        icon="🖥️"
                        title="Bạn chưa đăng bài viết khoe góc máy nào"
                        description="Hãy chia sẻ không gian setup chiến game hoặc làm việc của bạn để nhận tương tác và gợi ý gear từ cộng đồng GearHub!"
                        action-text="+ Đăng góc máy đầu tiên"
                        action-url="{{ route('community.create') }}"
                        class="my-4 py-10 bg-[#F8FAFC]"
                    />
                @endif

            </div>
        </div>

    </div>

    <!-- =================================================== -->
    <!-- MODAL THÊM / CHỈNH SỬA ĐỊA CHỈ NHẬN HÀNG (Alpine.js)-->
    <!-- =================================================== -->
    <div 
        x-show="addressModalOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        style="display: none;"
    >
        <div 
            @click.outside="addressModalOpen = false" 
            class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100 space-y-6 relative max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-lg font-black text-gray-900 tracking-tight" x-text="isEditingAddress ? 'Cập nhật địa chỉ nhận hàng' : 'Thêm địa chỉ nhận hàng mới'">
                    Thêm địa chỉ nhận hàng mới
                </h3>
                <button 
                    type="button" 
                    @click="addressModalOpen = false" 
                    class="text-gray-400 hover:text-gray-600 transition text-lg font-bold"
                >
                    ✕
                </button>
            </div>

            <form :action="addressFormAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEditingAddress">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Họ tên người nhận -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Họ và tên người nhận <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="receiver_name" 
                        x-model="addressData.receiver_name" 
                        required 
                        placeholder="Ví dụ: Nguyễn Văn A"
                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                    >
                </div>

                <!-- Số điện thoại -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Số điện thoại người nhận <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="tel" 
                        name="phone" 
                        x-model="addressData.phone" 
                        required 
                        placeholder="Ví dụ: 0912345678"
                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                    >
                </div>

                <!-- Địa chỉ nhận hàng chi tiết -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Địa chỉ nhận hàng chi tiết <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        name="address_line" 
                        x-model="addressData.address_line" 
                        rows="3" 
                        required 
                        placeholder="Số nhà, tên tòa nhà/tổ dân phố, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium leading-relaxed"
                    ></textarea>
                </div>

                <!-- Checkbox Mặc định -->
                <div class="pt-1">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            name="is_default" 
                            value="1" 
                            x-model="addressData.is_default"
                            class="w-5 h-5 rounded-lg border-gray-300 text-primary focus:ring-primary/20 transition cursor-pointer"
                        >
                        <span class="text-xs font-bold text-gray-800">
                            Đặt làm địa chỉ nhận hàng mặc định (Tự điền khi mua hàng)
                        </span>
                    </label>
                </div>

                <!-- Footer nút bấm modal -->
                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        @click="addressModalOpen = false" 
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition"
                    >
                        Hủy
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-md shadow-primary/20 transition"
                        x-text="isEditingAddress ? 'Cập nhật địa chỉ' : 'Lưu địa chỉ'"
                    >
                        Lưu địa chỉ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =================================================== -->
    <!-- MODAL XÁC NHẬN XÓA BÀI VIẾT GÓC MÁY (Alpine.js)   -->
    <!-- =================================================== -->
    <div 
        x-show="deletePostModalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        style="display: none;"
    >
        <div 
            @click.outside="deletePostModalOpen = false" 
            class="bg-white rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl border border-gray-100"
        >
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-xl">
                ⚠️
            </div>
            <h3 class="text-base font-black text-gray-900 tracking-tight">Xóa bài viết góc máy?</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Hành động này sẽ xóa vĩnh viễn bài đăng góc máy và toàn bộ lượt tim, bình luận liên quan. Bạn có chắc chắn không?
            </p>

            <form :action="'{{ url('/profile/community-posts') }}/' + deletePostId" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button 
                    type="button" 
                    @click="deletePostModalOpen = false" 
                    class="w-1/2 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition"
                >
                    Hủy
                </button>
                <button 
                    type="submit" 
                    class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition"
                >
                    Xóa ngay
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
