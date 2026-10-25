@php
    // Cùng bộ class với <x-input> để các ô thẻ gốc nhìn đồng bộ
    $fieldClass = 'w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition disabled:bg-gray-100 disabled:text-gray-500';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700';
    $errorClass = 'mt-1.5 text-xs font-medium text-rose-600';
    $fileClass  = 'block w-full text-sm text-gray-600 file:mr-4 file:rounded-xl file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200';

    // Dòng specs ban đầu: ưu tiên dữ liệu vừa nhập (khi validate lỗi), rồi đến dữ liệu đã lưu
    $specsRows = old('specs');
    if ($specsRows === null) {
        $specsRows = collect($product->specs ?? [])
            ->map(fn ($value, $key) => ['key' => (string) $key, 'value' => (string) $value])
            ->values()
            ->all();
    }
    $specsRows = array_values((array) $specsRows);
    if (empty($specsRows)) {
        $specsRows = [['key' => '', 'value' => '']];
    }

    // Ảnh chi tiết đã được tick xóa ở lần gửi trước (khi validate lỗi)
    $markedForRemoval = collect(old('remove_images', []))->map(fn ($id) => (int) $id);
@endphp

<div class="space-y-8">

    {{-- ===== Thông tin cơ bản ===== --}}
    <section class="space-y-5">
        <h2 class="text-base font-semibold text-gray-900">Thông tin cơ bản</h2>

        <x-input
            name="name"
            label="Tên sản phẩm"
            :value="$product->name"
            :error="$errors->first('name')"
            hint="Slug được tạo tự động từ tên."
            required
        />

        <x-input
            name="sku"
            label="SKU"
            :value="$product->sku"
            :error="$errors->first('sku')"
            hint="Mã duy nhất, chỉ gồm chữ, số và các ký tự - _ . (tự chuyển thành chữ hoa)."
            required
        />

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            {{-- Danh mục con --}}
            <div>
                <label for="category_id" class="{{ $labelClass }}">
                    Danh mục <span class="text-primary">*</span>
                </label>
                <select id="category_id" name="category_id" required class="{{ $fieldClass }}">
                    <option value="">— Chọn danh mục con —</option>
                    @foreach ($categoryGroups as $group)
                        <optgroup label="{{ $group->name }}">
                            @foreach ($group->children as $child)
                                <option value="{{ $child->id }}" @selected(old('category_id', $product->category_id) == $child->id)>
                                    {{ $child->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('category_id')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
            </div>

            {{-- Thương hiệu --}}
            <div>
                <label for="brand_id" class="{{ $labelClass }}">
                    Thương hiệu <span class="text-primary">*</span>
                </label>
                <select id="brand_id" name="brand_id" required class="{{ $fieldClass }}">
                    <option value="">— Chọn thương hiệu —</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                @error('brand_id')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Mô tả --}}
        <div>
            <label for="description" class="{{ $labelClass }}">Mô tả</label>
            <textarea id="description" name="description" rows="5" class="{{ $fieldClass }}">{{ old('description', $product->description) }}</textarea>
            @error('description')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>
    </section>

    {{-- ===== Giá và tồn kho ===== --}}
    <section class="space-y-5">
        <h2 class="text-base font-semibold text-gray-900">Giá và tồn kho</h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <x-input
                name="price"
                type="number"
                label="Giá gốc (VNĐ)"
                :value="$product->price"
                :error="$errors->first('price')"
                min="1"
                step="1"
                required
            />

            <x-input
                name="sale_price"
                type="number"
                label="Giá khuyến mãi (VNĐ)"
                :value="$product->sale_price"
                :error="$errors->first('sale_price')"
                hint="Để trống nếu không khuyến mãi."
                min="1"
                step="1"
            />

            <x-input
                name="stock_quantity"
                type="number"
                label="Số lượng tồn kho"
                :value="$product->stock_quantity"
                :error="$errors->first('stock_quantity')"
                min="0"
                step="1"
                required
            />
        </div>
    </section>

    {{-- ===== Thông số kỹ thuật (Alpine) ===== --}}
    <section class="space-y-3">
        <div>
            <h2 class="text-base font-semibold text-gray-900">Thông số kỹ thuật</h2>
            <p class="mt-1 text-xs text-gray-500">Mỗi dòng gồm tên và giá trị. Dòng để trống hoàn toàn sẽ được bỏ qua.</p>
        </div>

        <div
            x-data="{
                rows: @js($specsRows),
                add() { this.rows.push({ key: '', value: '' }) },
                remove(index) {
                    this.rows.splice(index, 1);
                    if (this.rows.length === 0) this.add();
                }
            }"
            class="space-y-3"
        >
            <template x-for="(row, index) in rows" :key="index">
                <div class="flex items-start gap-2">
                    <input type="text" x-model="row.key" :name="'specs[' + index + '][key]'"
                           maxlength="100" placeholder="Tên (vd: Kết nối)" class="{{ $fieldClass }}">
                    <input type="text" x-model="row.value" :name="'specs[' + index + '][value]'"
                           maxlength="255" placeholder="Giá trị (vd: Bluetooth 5.1)" class="{{ $fieldClass }}">
                    <x-button type="button" variant="outline" @click="remove(index)" aria-label="Xóa dòng">Xóa</x-button>
                </div>
            </template>

            <x-button type="button" variant="outline-primary" size="sm" @click="add()">+ Thêm thông số</x-button>
        </div>

        @foreach ($errors->get('specs') as $message)
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @endforeach
        @foreach ($errors->get('specs.*') as $messages)
            @foreach ($messages as $message)
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @endforeach
        @endforeach
    </section>

    {{-- ===== Hình ảnh ===== --}}
    <section class="space-y-6">
        <h2 class="text-base font-semibold text-gray-900">Hình ảnh</h2>

        {{-- Ảnh đại diện --}}
        <div>
            <label for="thumbnail" class="{{ $labelClass }}">Ảnh đại diện</label>

            @if ($product->exists && $product->thumbnail_url)
                <div class="mb-3 flex items-center gap-4">
                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                         class="h-20 w-20 rounded-xl object-cover">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remove_thumbnail" value="1" @checked(old('remove_thumbnail'))
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        Xóa ảnh đại diện hiện tại
                    </label>
                </div>
            @endif

            <input id="thumbnail" name="thumbnail" type="file" accept=".jpg,.jpeg,.png,.webp" class="{{ $fileClass }}">
            <p class="mt-1 text-xs text-gray-500">
                JPG, PNG hoặc WEBP, tối đa 2MB.
                @if ($product->exists && $product->thumbnail_url) Chọn tệp mới để thay thế ảnh hiện tại. @endif
            </p>
            @error('thumbnail')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
            @error('remove_thumbnail')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>

        {{-- Ảnh chi tiết hiện có (chỉ khi sửa) --}}
        @if ($product->exists && $product->images->isNotEmpty())
            <div>
                <p class="{{ $labelClass }}">Ảnh chi tiết hiện có</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($product->images as $image)
                        <label class="block rounded-xl border border-gray-200 p-2 text-center text-xs text-gray-600">
                            <img src="{{ $image->url }}" alt="Ảnh chi tiết {{ $loop->iteration }}"
                                 class="mb-2 h-24 w-full rounded-lg object-cover">
                            <span class="inline-flex items-center gap-1.5">
                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"
                                       @checked($markedForRemoval->contains($image->id))
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                Xóa
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('remove_images')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                @foreach ($errors->get('remove_images.*') as $messages)
                    @foreach ($messages as $message)
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @endforeach
                @endforeach
            </div>
        @endif

        {{-- Thêm ảnh chi tiết --}}
        <div>
            <label for="images" class="{{ $labelClass }}">
                {{ $product->exists ? 'Thêm ảnh chi tiết' : 'Ảnh chi tiết' }}
            </label>
            <input id="images" name="images[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp" class="{{ $fileClass }}">
            <p class="mt-1 text-xs text-gray-500">Tối đa 8 ảnh chi tiết (tính cả ảnh hiện có), mỗi ảnh tối đa 2MB.</p>
            @error('images')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
            @foreach ($errors->get('images.*') as $messages)
                @foreach ($messages as $message)
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @endforeach
            @endforeach
        </div>
    </section>

    {{-- ===== Hiển thị ===== --}}
    <section>
        {{-- hidden 0 đảm bảo ô bỏ chọn vẫn gửi giá trị; ô chọn (1) đứng sau nên thắng --}}
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $product->exists ? $product->is_active : true))
                   class="rounded border-gray-300 text-primary focus:ring-primary">
            Hiển thị sản phẩm ở cửa hàng
        </label>
        @error('is_active')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
    </section>
</div>