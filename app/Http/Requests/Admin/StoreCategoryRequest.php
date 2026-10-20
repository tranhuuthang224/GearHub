<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
            // Chỉ được chọn danh mục cấp 1 làm cha, đảm bảo tối đa 2 cấp
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('parent_id')],
            // Không cho SVG: SVG có thể chứa script (XSS)
            'image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'tên danh mục',
            'parent_id' => 'danh mục cha',
            'image'     => 'ảnh đại diện',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Vui lòng nhập tên danh mục.',
            'name.unique'      => 'Tên danh mục đã tồn tại.',
            'parent_id.exists' => 'Danh mục cha không hợp lệ (chỉ chọn được danh mục cấp 1).',
            'image.image'      => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes'      => 'Ảnh chỉ chấp nhận định dạng jpg, jpeg, png hoặc webp.',
            'image.max'        => 'Ảnh không được vượt quá 2MB.',
        ];
    }
}