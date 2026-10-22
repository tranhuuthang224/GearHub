@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'required' => false,
    'multiple' => false,
    'accept' => 'image/png,image/jpeg,image/webp,image/svg+xml',
    'error' => null,
    'hint' => 'Định dạng hỗ trợ: PNG, JPG, WEBP, SVG (tối đa 2MB/ảnh).',
    'currentImage' => null,
    'currentImages' => [],
])

<div class="w-full" x-data="{
    previews: [],
    handleFiles(event) {
        const files = Array.from(event.target.files);
        this.previews = [];
        files.forEach(file => {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previews.push({
                        name: file.name,
                        size: (file.size / 1024).toFixed(1) + ' KB',
                        url: e.target.result
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    },
    clearFile(input) {
        this.previews = [];
        if (input) input.value = '';
    }
}">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-primary">*</span>
            @endif
        </label>
    @endif

    <!-- Upload Dropzone Area -->
    <div 
        class="relative border-2 border-dashed rounded-2xl p-6 text-center transition duration-200 hover:border-primary/50 bg-gray-50/50 hover:bg-white group {{ $error ? 'border-rose-400 bg-rose-50/20' : 'border-gray-200' }}"
    >
        <input
            type="file"
            id="{{ $name }}"
            name="{{ $multiple ? $name . '[]' : $name }}"
            accept="{{ $accept }}"
            {{ $multiple ? 'multiple' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            @change="handleFiles($event)"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 disabled:cursor-not-allowed"
            {{ $attributes }}
        >

        <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
            <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 group-hover:text-primary group-hover:scale-110 transition duration-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="text-xs text-gray-600">
                <span class="font-bold text-primary">Bấm để tải tệp lên</span> hoặc kéo thả tệp vào đây
            </div>
            <p class="text-[11px] text-gray-400">
                {{ $hint }}
            </p>
        </div>
    </div>

    <!-- Live Preview for newly selected files (Alpine.js) -->
    <template x-if="previews.length > 0">
        <div class="mt-4 space-y-2">
            <div class="flex items-center justify-between text-xs text-gray-500 font-semibold">
                <span>Tệp vừa chọn (<span x-text="previews.length"></span>)</span>
                <button 
                    type="button" 
                    @click="clearFile(document.getElementById('{{ $name }}'))" 
                    class="text-rose-600 hover:underline font-bold"
                >
                    Hủy chọn
                </button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                <template x-for="(preview, index) in previews" :key="index">
                    <div class="relative aspect-square rounded-xl bg-gray-100 border border-gray-200 overflow-hidden group/img">
                        <img :src="preview.url" :alt="preview.name" class="w-full h-full object-contain p-1.5">
                        <div class="absolute inset-x-0 bottom-0 bg-black/60 text-white text-[9px] p-1 truncate text-center" x-text="preview.size"></div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Existing Image Preview (For Edit mode) -->
    @if (!empty($currentImage) && empty($currentImages))
        <div class="mt-3 flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 border border-gray-200">
            <div class="w-12 h-12 rounded-lg bg-white border border-gray-200 overflow-hidden flex items-center justify-center p-1 shrink-0">
                <img src="{{ str_starts_with($currentImage, 'http') ? $currentImage : asset('storage/' . $currentImage) }}" alt="Current Image" class="max-h-full max-w-full object-contain">
            </div>
            <div class="text-xs">
                <span class="font-semibold text-gray-700 block">Ảnh hiện tại</span>
                <span class="text-gray-400 text-[11px]">Tải ảnh mới lên nếu muốn thay thế ảnh này</span>
            </div>
        </div>
    @endif

    @if (!empty($currentImages) && count($currentImages) > 0)
        <div class="mt-3 space-y-1.5">
            <span class="text-xs font-semibold text-gray-700 block">Các ảnh chi tiết hiện có:</span>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                @foreach ($currentImages as $cImg)
                    <div class="aspect-square rounded-xl bg-white border border-gray-200 overflow-hidden p-1 flex items-center justify-center">
                        <img src="{{ str_starts_with($cImg, 'http') ? $cImg : asset('storage/' . $cImg) }}" alt="Existing Gallery" class="max-h-full max-w-full object-contain">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($error)
        <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
