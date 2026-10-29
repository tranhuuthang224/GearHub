<?php

namespace App\Http\Controllers\Client;

use App\Exceptions\Order\InvalidCouponException;
use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Xem trước mã giảm giá ở Checkout. Chỉ để hiển thị: số tiền thật do OrderService tính lại khi đặt hàng.
 */
class CheckoutCouponController extends Controller
{
    public function __invoke(Request $request, CouponService $coupons): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code'     => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'integer', 'min:0', 'max:999999999'],
        ], [
            'code.required'    => 'Vui lòng nhập mã giảm giá.',
            'code.string'      => 'Mã giảm giá không hợp lệ.',
            'code.max'         => 'Mã giảm giá không hợp lệ.',
            'subtotal.required' => 'Thiếu giá trị tạm tính của đơn hàng.',
            'subtotal.integer' => 'Tạm tính không hợp lệ.',
            'subtotal.min'     => 'Tạm tính không hợp lệ.',
            'subtotal.max'     => 'Tạm tính không hợp lệ.',
        ]);

        // Mọi lỗi đều có cùng dạng {valid:false, message} để giao diện chỉ cần xử lý một kiểu
        if ($validator->fails()) {
            return response()->json(['valid' => false, 'message' => $validator->errors()->first()], 422);
        }

        $code     = $request->string('code')->toString();
        $subtotal = (int) $request->input('subtotal');

        try {
            $coupon = $coupons->check($code, $subtotal);
        } catch (InvalidCouponException $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 422);
        }

        $discount = $coupons->calculateDiscount($coupon, $subtotal);

        return response()->json([
            'valid'    => true,
            'code'     => $coupon->code,
            'label'    => $coupon->discountLabel(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => $subtotal - $discount,
            'message'  => "Đã áp dụng mã {$coupon->code}: giảm " . number_format($discount, 0, ',', '.') . ' ₫.',
        ]);
    }
}