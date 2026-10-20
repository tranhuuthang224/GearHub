<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
                Rule::notIn([$category->id]), // không được tự làm cha của chính mình
                function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                    // Danh mục đang có con mà lại bị gán cha thì thành 3 cấp
                    if ($value && $category->children()->exists()) {
                        $fail('Danh mục đang có danh mục con nên không thể chuyển thành danh mục con.');
                    }
                },
            ],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}