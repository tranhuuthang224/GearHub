<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateBrandRequest extends StoreBrandRequest
{
    public function rules(): array
    {
        $brand = $this->route('brand');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('brands', 'name')->ignore($brand->id),
            ],
            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}