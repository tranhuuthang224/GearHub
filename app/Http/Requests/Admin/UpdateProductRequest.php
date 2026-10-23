<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    private const MAX_GALLERY_IMAGES = 8;

    public function rules(): array
    {
        $product = $this->route('product');

        return array_merge(parent::rules(), [
            // Bỏ qua chính sản phẩm đang sửa; vẫn tính cả sản phẩm xóa mềm của người khác
            'sku' => [
                'required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],

            'remove_thumbnail' => ['nullable', 'boolean'],

            // Chỉ cho xóa ảnh thuộc chính sản phẩm này
            'remove_images'   => ['nullable', 'array'],
            'remove_images.*' => [
                'integer',
                Rule::exists('product_images', 'id')->where('product_id', $product->id),
            ],
        ]);
    }

    /** Giữ luật specs của lớp cha, thêm luật tổng số ảnh chi tiết. */
    public function withValidator($validator): void
    {
        parent::withValidator($validator);

        $validator->after(function ($validator) {
            $product = $this->route('product');

            $removeIds = array_map('intval', (array) $this->input('remove_images', []));
            $existing  = $product->images()->count();
            $removed   = $removeIds ? $product->images()->whereIn('id', $removeIds)->count() : 0;
            $incoming  = count((array) $this->file('images', []));

            $remaining = $existing - $removed;

            if ($remaining + $incoming > self::MAX_GALLERY_IMAGES) {
                $validator->errors()->add(
                    'images',
                    'Tổng số ảnh chi tiết không được vượt quá ' . self::MAX_GALLERY_IMAGES
                    . " (hiện còn {$remaining} ảnh sau khi xóa)."
                );
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'remove_thumbnail.boolean' => 'Giá trị xóa ảnh đại diện không hợp lệ.',
            'remove_images.array'      => 'Danh sách ảnh cần xóa không hợp lệ.',
            'remove_images.*.integer'  => 'Ảnh cần xóa không hợp lệ.',
            'remove_images.*.exists'   => 'Ảnh cần xóa không thuộc sản phẩm này.',
        ]);
    }
}