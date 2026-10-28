<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateCouponRequest extends StoreCouponRequest
{
    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return array_merge(parent::rules(), [
            'code' => [
                'required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons', 'code')->ignore($coupon->id),
            ],
            // Khi sửa cho phép đặt hạn về quá khứ để vô hiệu hóa mã
            'expires_at' => ['nullable', 'date'],
        ]);
    }

    public function withValidator($validator): void
    {
        parent::withValidator($validator);

        $validator->after(function ($validator) {
            $coupon = $this->route('coupon');
            $limit  = $this->input('usage_limit');

            if (
                $limit !== null && $limit !== ''
                && ! $validator->errors()->has('usage_limit')
                && (int) $limit < $coupon->used_count
            ) {
                $validator->errors()->add(
                    'usage_limit',
                    "Giới hạn lượt dùng không thể thấp hơn số lượt đã dùng ({$coupon->used_count})."
                );
            }
        });
    }
}