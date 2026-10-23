<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** Chuẩn hóa SKU trước khi validate để so trùng nhất quán. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('sku'))) {
            $this->merge(['sku' => strtoupper(trim($this->input('sku')))]);
        }
    }

    public function rules(): array
    {
        return [
            // Sản phẩm chỉ gắn vào danh mục con (cấp 2), để bộ lọc Storefront hoạt động đúng
            'category_id'    => ['required', 'integer', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'brand_id'       => ['required', 'integer', Rule::exists('brands', 'id')],
            'name'           => ['required', 'string', 'max:255'],
            // unique tính cả bản ghi đã xóa mềm, vì cột sku UNIQUE ở cấp CSDL cũng tính chúng
            'sku'            => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('products', 'sku')],
            'description'    => ['nullable', 'string', 'max:5000'],
            'price'          => ['required', 'integer', 'min:1', 'max:999999999'],
            // effective_price dùng ?? nên giá khuyến mãi 0 sẽ thành giá bán 0: cấm
            'sale_price'     => ['nullable', 'integer', 'min:1', 'lt:price'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active'      => ['nullable', 'boolean'],

            'specs'          => ['nullable', 'array', 'max:30'],
            'specs.*'        => ['array'],
            'specs.*.key'    => ['nullable', 'string', 'max:100'],
            'specs.*.value'  => ['nullable', 'string', 'max:255'],

            // Không cho SVG: SVG có thể chứa script (XSS)
            'thumbnail'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'images'         => ['nullable', 'array', 'max:8'],
            'images.*'       => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** Kiểm tra các luật khó diễn đạt bằng rule có sẵn: dòng thiếu nửa, khóa trùng. */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $seen = [];

            foreach ((array) $this->input('specs', []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $key   = trim((string) ($row['key'] ?? ''));
                $value = trim((string) ($row['value'] ?? ''));

                if ($key === '' && $value === '') {
                    continue; // dòng trống hoàn toàn: bỏ qua, không báo lỗi
                }

                if ($key === '' || $value === '') {
                    $validator->errors()->add('specs', 'Dòng thông số số ' . ($index + 1) . ' phải có đủ tên và giá trị.');
                    continue;
                }

                $lower = mb_strtolower($key);
                if (isset($seen[$lower])) {
                    $validator->errors()->add('specs', "Tên thông số «{$key}» bị trùng.");
                }
                $seen[$lower] = true;
            }
        });
    }

    /**
     * Gói các dòng key-value thành mảng kết hợp ['Kết nối' => 'Bluetooth', ...]
     * đúng định dạng seeder đã dùng. Trả null nếu không có dòng nào.
     */
    public function specsPayload(): ?array
    {
        $result = [];

        foreach ((array) $this->input('specs', []) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key   = trim((string) ($row['key'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));

            if ($key !== '' && $value !== '') {
                $result[$key] = $value;
            }
        }

        return $result ?: null;
    }

    public function attributes(): array
    {
        return [
            'category_id'    => 'danh mục',
            'brand_id'       => 'thương hiệu',
            'name'           => 'tên sản phẩm',
            'sku'            => 'SKU',
            'description'    => 'mô tả',
            'price'          => 'giá gốc',
            'sale_price'     => 'giá khuyến mãi',
            'stock_quantity' => 'số lượng tồn kho',
            'specs'          => 'thông số kỹ thuật',
            'thumbnail'      => 'ảnh đại diện',
            'images'         => 'ảnh chi tiết',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.integer'  => 'Danh mục không hợp lệ.',
            'category_id.exists'   => 'Danh mục không hợp lệ (chỉ chọn được danh mục con).',

            'brand_id.required'    => 'Vui lòng chọn thương hiệu.',
            'brand_id.integer'     => 'Thương hiệu không hợp lệ.',
            'brand_id.exists'      => 'Thương hiệu không tồn tại.',

            'name.required'        => 'Vui lòng nhập tên sản phẩm.',
            'name.string'          => 'Tên sản phẩm không hợp lệ.',
            'name.max'             => 'Tên sản phẩm không được vượt quá 255 ký tự.',

            'sku.required'         => 'Vui lòng nhập SKU.',
            'sku.string'           => 'SKU không hợp lệ.',
            'sku.max'              => 'SKU không được vượt quá 100 ký tự.',
            'sku.regex'            => 'SKU chỉ gồm chữ, số và các ký tự - _ .',
            'sku.unique'           => 'SKU đã tồn tại (có thể thuộc sản phẩm trong Thùng rác).',

            'description.string'   => 'Mô tả không hợp lệ.',
            'description.max'      => 'Mô tả không được vượt quá 5000 ký tự.',

            'price.required'       => 'Vui lòng nhập giá gốc.',
            'price.integer'        => 'Giá gốc phải là số nguyên (VNĐ).',
            'price.min'            => 'Giá gốc phải lớn hơn 0.',
            'price.max'            => 'Giá gốc không được vượt quá 999.999.999.',

            'sale_price.integer'   => 'Giá khuyến mãi phải là số nguyên (VNĐ).',
            'sale_price.min'       => 'Giá khuyến mãi phải lớn hơn 0.',
            'sale_price.lt'        => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',

            'stock_quantity.required' => 'Vui lòng nhập số lượng tồn kho.',
            'stock_quantity.integer'  => 'Số lượng tồn kho phải là số nguyên.',
            'stock_quantity.min'      => 'Số lượng tồn kho không được âm.',
            'stock_quantity.max'      => 'Số lượng tồn kho không được vượt quá 1.000.000.',

            'is_active.boolean'    => 'Trạng thái hiển thị không hợp lệ.',

            'specs.array'          => 'Thông số kỹ thuật không hợp lệ.',
            'specs.max'            => 'Chỉ được nhập tối đa 30 dòng thông số.',
            'specs.*.array'        => 'Dòng thông số không hợp lệ.',
            'specs.*.key.string'   => 'Tên thông số không hợp lệ.',
            'specs.*.key.max'      => 'Tên thông số không được vượt quá 100 ký tự.',
            'specs.*.value.string' => 'Giá trị thông số không hợp lệ.',
            'specs.*.value.max'    => 'Giá trị thông số không được vượt quá 255 ký tự.',

            'thumbnail.image'      => 'Ảnh đại diện phải là hình ảnh.',
            'thumbnail.mimes'      => 'Ảnh đại diện chỉ chấp nhận định dạng jpg, jpeg, png hoặc webp.',
            'thumbnail.max'        => 'Ảnh đại diện không được vượt quá 2MB.',
            'thumbnail.uploaded'   => 'Tải ảnh đại diện lên thất bại. Vui lòng thử ảnh nhỏ hơn.',

            'images.array'         => 'Ảnh chi tiết không hợp lệ.',
            'images.max'           => 'Chỉ được tải tối đa 8 ảnh chi tiết.',
            'images.*.image'       => 'Mỗi ảnh chi tiết phải là hình ảnh.',
            'images.*.mimes'       => 'Ảnh chi tiết chỉ chấp nhận định dạng jpg, jpeg, png hoặc webp.',
            'images.*.max'         => 'Mỗi ảnh chi tiết không được vượt quá 2MB.',
            'images.*.uploaded'    => 'Tải ảnh chi tiết lên thất bại. Vui lòng thử ảnh nhỏ hơn.',
        ];
    }
}