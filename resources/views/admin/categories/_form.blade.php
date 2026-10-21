<div class="space-y-5">
    {{-- Tên --}}
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">
            Tên danh mục <span class="text-red-600">*</span>
        </label>
        <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
        <p class="mt-1 text-xs text-slate-500">Slug được tạo tự động từ tên.</p>
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Danh mục cha --}}
    <div>
        <label for="parent_id" class="block text-sm font-medium text-slate-700">Danh mục cha</label>
        <select id="parent_id" name="parent_id" @disabled($hasChildren)
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 disabled:bg-slate-100">
            <option value="">— Không có (danh mục cấp 1) —</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @if ($hasChildren)
            <p class="mt-1 text-xs text-slate-500">Danh mục này đang có danh mục con nên luôn ở cấp 1.</p>
        @endif
        @error('parent_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Ảnh đại diện --}}
    <div>
        <label for="image" class="block text-sm font-medium text-slate-700">Ảnh đại diện</label>

        @if ($category->image_url)
            <div class="mt-2 flex items-center gap-4">
                <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                     class="h-20 w-20 rounded-lg object-cover">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300">
                    Xóa ảnh hiện tại
                </label>
            </div>
        @endif

        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp"
               class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
        <p class="mt-1 text-xs text-slate-500">JPG, PNG hoặc WEBP, tối đa 2MB.</p>
        @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>