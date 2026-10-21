<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')],
            // Không cho SVG: SVG có thể chứa script (XSS)
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'tên thương hiệu',
            'logo' => 'logo',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Vui lòng nhập tên thương hiệu.',
            'name.string'          => 'Tên thương hiệu không hợp lệ.',
            'name.max'             => 'Tên thương hiệu không được vượt quá 255 ký tự.',
            'name.unique'          => 'Tên thương hiệu đã tồn tại.',
            'logo.image'           => 'Tệp tải lên phải là hình ảnh.',
            'logo.mimes'           => 'Logo chỉ chấp nhận định dạng jpg, jpeg, png hoặc webp.',
            'logo.max'             => 'Logo không được vượt quá 2MB.',
            'logo.uploaded'        => 'Tải logo lên thất bại. Vui lòng thử ảnh nhỏ hơn.',
            'remove_logo.boolean'  => 'Giá trị xóa logo không hợp lệ.',
        ];
    }
}