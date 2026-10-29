<?php

namespace App\Services;

use App\Exceptions\Order\InvalidCouponException;
use App\Models\Coupon;
use Carbon\CarbonInterface;

/**
 * Kiểm tra mã giảm giá, tính tiền giảm và ghi nhận lượt dùng.
 * Dùng cho route xem trước ở Checkout (Thứ 4) và OrderService (Thứ 5).
 */
class CouponService
{
    /** Chuẩn hóa mã khách nhập: bỏ khoảng trắng hai đầu và viết hoa (khớp cách lưu ở Admin). */
    public function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    public function find(string $code): ?Coupon
    {
        $code = $this->normalizeCode($code);

        return $code === '' ? null : Coupon::where('code', $code)->first();
    }

    /**
     * Xem trước ở Checkout: tìm mã rồi kiểm tra. Không ghi gì vào CSDL.
     *
     * @throws InvalidCouponException  message là lý do bằng tiếng Việt, hiển thị thẳng cho khách
     */
    public function check(string $code, int $subtotal, ?CarbonInterface $at = null): Coupon
    {
        $coupon = $this->find($code);

        $this->assertUsable($coupon, $subtotal, $at);

        return $coupon;
    }

    /**
     * Kiểm tra một mã đã có có dùng được cho đơn hàng này không.
     * OrderService gọi lại hàm này bên trong transaction đặt hàng.
     *
     * @throws InvalidCouponException
     */
    public function assertUsable(?Coupon $coupon, int $subtotal, ?CarbonInterface $at = null): void
    {
        // Mã không tồn tại và mã đã tắt dùng chung một thông báo để không lộ mã nào đang có
        if ($coupon === null) {
            throw new InvalidCouponException('Mã giảm giá không hợp lệ.');
        }

        switch ($coupon->status($at)) {
            case Coupon::STATUS_INACTIVE:
                throw new InvalidCouponException('Mã giảm giá không hợp lệ.');
            case Coupon::STATUS_SCHEDULED:
                throw new InvalidCouponException(
                    'Mã giảm giá có hiệu lực từ ' . $coupon->starts_at->format('d/m/Y H:i') . '.'
                );
            case Coupon::STATUS_EXPIRED:
                throw new InvalidCouponException('Mã giảm giá đã hết hạn.');
            case Coupon::STATUS_EXHAUSTED:
                throw new InvalidCouponException('Mã giảm giá đã hết lượt sử dụng.');
        }

        if ($subtotal <= 0) {
            throw new InvalidCouponException('Giỏ hàng trống, không thể áp dụng mã giảm giá.');
        }

        if ($subtotal < $coupon->min_order) {
            $missing = $coupon->min_order - $subtotal;

            throw new InvalidCouponException(
                "Đơn hàng cần từ {$this->money($coupon->min_order)} để dùng mã này (còn thiếu {$this->money($missing)})."
            );
        }
    }

    /**
     * Số tiền giảm (VNĐ, số nguyên). Phần trăm làm tròn xuống; không bao giờ vượt tạm tính.
     * Chỉ tính toán, không kiểm tra hiệu lực: gọi assertUsable() trước.
     */
    public function calculateDiscount(Coupon $coupon, int $subtotal): int
    {
        $discount = $coupon->type === Coupon::TYPE_PERCENT
            ? intdiv($subtotal * $coupon->value, 100)
            : $coupon->value;

        return max(0, min($discount, $subtotal));
    }

    /**
     * Ghi nhận một lượt dùng. PHẢI gọi bên trong transaction đặt hàng để rollback thì lượt dùng cũng hoàn lại.
     *
     * Dùng một câu UPDATE có điều kiện thay vì "đọc rồi ghi", nên hai đơn đồng thời không thể
     * cùng lấy lượt cuối cùng ngay cả khi mỗi bên đang cầm một bản sao đã cũ của mã.
     *
     * @throws InvalidCouponException  Mã vừa hết lượt
     */
    public function redeem(Coupon $coupon): void
    {
        $updated = Coupon::whereKey($coupon->getKey())
            ->where(fn ($query) => $query
                ->whereNull('usage_limit')
                ->orWhereColumn('used_count', '<', 'usage_limit'))
            ->increment('used_count');

        if ($updated === 0) {
            throw new InvalidCouponException('Mã giảm giá đã hết lượt sử dụng.');
        }

        $coupon->refresh();
    }

    /** Hoàn lại một lượt dùng (khi hủy đơn). Không bao giờ xuống dưới 0. */
    public function release(Coupon $coupon): void
    {
        Coupon::whereKey($coupon->getKey())
            ->where('used_count', '>', 0)
            ->decrement('used_count');

        $coupon->refresh();
    }

    private function money(int $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' ₫';
    }
}