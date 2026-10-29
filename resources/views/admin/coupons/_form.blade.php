@php
    $fieldClass = 'w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700';
    $errorClass = 'mt-1.5 text-xs font-medium text-rose-600';
@endphp

<div class="space-y-5">
    <x-input
        name="code"
        label="Mã giảm giá"
        :value="$coupon->code"
        :error="$errors->first('code')"
        hint="Chữ không dấu, số, - hoặc _. Tự động chuyển thành chữ hoa."
        required
    />

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        {{-- Loại giảm --}}
        <div>
            <label for="type" class="{{ $labelClass }}">
                Loại giảm <span class="text-primary">*</span>
            </label>
            <select id="type" name="type" required class="{{ $fieldClass }}">
                @foreach (\App\Models\Coupon::TYPE_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $coupon->type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>

        {{-- Giá trị giảm --}}
        <x-input
            name="value"
            type="number"
            label="Giá trị giảm"
            :value="$coupon->exists ? $coupon->value : null"
            :error="$errors->first('value')"
            hint="Phần trăm: nhập từ 1 đến 100. Số tiền cố định: nhập số VNĐ."
            min="1"
            step="1"
            required
        />
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <x-input
            name="min_order"
            type="number"
            label="Đơn tối thiểu (VNĐ)"
            :value="$coupon->min_order"
            :error="$errors->first('min_order')"
            hint="Nhập 0 nếu không yêu cầu."
            min="0"
            step="1"
            required
        />

        <x-input
            name="usage_limit"
            type="number"
            label="Giới hạn lượt dùng"
            :value="$coupon->usage_limit"
            :error="$errors->first('usage_limit')"
            hint="Để trống nếu không giới hạn."
            min="1"
            step="1"
        />
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <x-input
            name="starts_at"
            type="datetime-local"
            label="Ngày bắt đầu"
            :value="$coupon->starts_at?->format('Y-m-d\TH:i')"
            :error="$errors->first('starts_at')"
            hint="Để trống nếu có hiệu lực ngay."
        />

        <x-input
            name="expires_at"
            type="datetime-local"
            label="Ngày hết hạn"
            :value="$coupon->expires_at?->format('Y-m-d\TH:i')"
            :error="$errors->first('expires_at')"
            hint="Để trống nếu không hết hạn."
        />
    </div>

    @if ($coupon->exists)
        <p class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600">
            Mã này đã được dùng <strong>{{ number_format($coupon->used_count, 0, ',', '.') }}</strong> lượt.
        </p>
    @endif

    {{-- Kích hoạt --}}
    <div>
        {{-- hidden 0 đảm bảo ô bỏ chọn vẫn gửi giá trị; ô chọn (1) đứng sau nên thắng --}}
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $coupon->is_active))
                   class="rounded border-gray-300 text-primary focus:ring-primary">
            Kích hoạt mã giảm giá
        </label>
        @error('is_active')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
    </div>
</div>