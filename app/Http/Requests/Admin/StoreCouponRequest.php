<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** Chuẩn hóa trước khi validate: mã viết hoa, đơn tối thiểu để trống thành 0. */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (is_string($this->input('code'))) {
            $merge['code'] = strtoupper(trim($this->input('code')));
        }

        if ($this->input('min_order') === null || $this->input('min_order') === '') {
            $merge['min_order'] = 0;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('coupons', 'code')],
            'type'        => ['required', Rule::in([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED])],
            'value'       => ['required', 'integer', 'min:1', 'max:999999999'],
            'min_order'   => ['required', 'integer', 'min:0', 'max:999999999'],
            // Để trống nghĩa là không giới hạn lượt dùng
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'starts_at'   => ['nullable', 'date'],
            // Mã mới không được hết hạn ngay từ đầu (khi sửa thì cho phép, xem UpdateCouponRequest)
            'expires_at'  => ['nullable', 'date', 'after:now'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    /** Hai luật khó diễn đạt bằng rule có sẵn: phần trăm tối đa 100, hết hạn sau bắt đầu. */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $errors = $validator->errors();

            if (
                ! $errors->has('value')
                && $this->input('type') === Coupon::TYPE_PERCENT
                && (int) $this->input('value') > 100
            ) {
                $errors->add('value', 'Giảm theo phần trăm chỉ được từ 1 đến 100.');
            }

            $start = $this->input('starts_at');
            $end   = $this->input('expires_at');

            if (
                $start && $end
                && ! $errors->has('starts_at')
                && ! $errors->has('expires_at')
                && strtotime((string) $end) <= strtotime((string) $start)
            ) {
                $errors->add('expires_at', 'Ngày hết hạn phải sau ngày bắt đầu.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'code'        => 'mã giảm giá',
            'type'        => 'loại giảm',
            'value'       => 'giá trị giảm',
            'min_order'   => 'đơn tối thiểu',
            'usage_limit' => 'giới hạn lượt dùng',
            'starts_at'   => 'ngày bắt đầu',
            'expires_at'  => 'ngày hết hạn',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.string'   => 'Mã giảm giá không hợp lệ.',
            'code.max'      => 'Mã giảm giá không được vượt quá 50 ký tự.',
            'code.regex'    => 'Mã chỉ gồm chữ không dấu, số và các ký tự - _ (không có khoảng trắng).',
            'code.unique'   => 'Mã giảm giá đã tồn tại.',

            'type.required' => 'Vui lòng chọn loại giảm.',
            'type.in'       => 'Loại giảm không hợp lệ.',

            'value.required' => 'Vui lòng nhập giá trị giảm.',
            'value.integer'  => 'Giá trị giảm phải là số nguyên.',
            'value.min'      => 'Giá trị giảm phải lớn hơn 0.',
            'value.max'      => 'Giá trị giảm không được vượt quá 999.999.999.',

            'min_order.required' => 'Vui lòng nhập đơn tối thiểu (nhập 0 nếu không yêu cầu).',
            'min_order.integer'  => 'Đơn tối thiểu phải là số nguyên (VNĐ).',
            'min_order.min'      => 'Đơn tối thiểu không được âm.',
            'min_order.max'      => 'Đơn tối thiểu không được vượt quá 999.999.999.',

            'usage_limit.integer' => 'Giới hạn lượt dùng phải là số nguyên.',
            'usage_limit.min'     => 'Giới hạn lượt dùng phải từ 1 trở lên (để trống nếu không giới hạn).',
            'usage_limit.max'     => 'Giới hạn lượt dùng không được vượt quá 1.000.000.',

            'starts_at.date'    => 'Ngày bắt đầu không hợp lệ.',
            'expires_at.date'   => 'Ngày hết hạn không hợp lệ.',
            'expires_at.after'  => 'Ngày hết hạn phải ở tương lai.',

            'is_active.boolean' => 'Trạng thái kích hoạt không hợp lệ.',
        ];
    }
}