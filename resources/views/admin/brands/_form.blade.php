@php
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700';
    $errorClass = 'mt-1.5 text-xs font-medium text-rose-600';
@endphp

<div class="space-y-5">
    {{-- Tên --}}
    <x-input
        name="name"
        label="Tên thương hiệu"
        :value="$brand->name"
        :error="$errors->first('name')"
        hint="Slug được tạo tự động từ tên."
        required
    />

    {{-- Logo --}}
    <div>
        <label for="logo" class="{{ $labelClass }}">Logo</label>

        @if ($brand->logo_url)
            <div class="mb-3 flex items-center gap-4">
                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                     class="h-20 w-20 rounded-xl object-cover">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remove_logo" value="1"
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    Xóa logo hiện tại
                </label>
            </div>
        @endif

        <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp"
               class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-xl file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
        <p class="mt-1 text-xs text-gray-500">JPG, PNG hoặc WEBP, tối đa 2MB.</p>
        @error('logo')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        @error('remove_logo')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
    </div>
</div>