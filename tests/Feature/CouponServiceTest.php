<?php

namespace Tests\Feature;

use App\Exceptions\Order\InvalidCouponException;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CouponService();
    }

    private function makeCoupon(array $attributes = []): Coupon
    {
        // used_count không nằm trong $fillable nên phải bỏ qua bảo vệ có chủ đích
        return Coupon::unguarded(fn () => Coupon::create(array_merge([
            'code' => 'GEAR10', 'type' => 'percent', 'value' => 10, 'min_order' => 500000,
            'usage_limit' => null, 'used_count' => 0,
            'starts_at' => null, 'expires_at' => null, 'is_active' => true,
        ], $attributes)));
    }

    /** Chạy một đoạn mã mong đợi ném InvalidCouponException và trả về thông báo. */
    private function rejection(callable $callback): string
    {
        try {
            $callback();
        } catch (InvalidCouponException $e) {
            return $e->getMessage();
        }

        $this->fail('Mong đợi InvalidCouponException nhưng không có ngoại lệ nào được ném.');
    }

    public function test_normalize_code(): void
    {
        $this->assertSame('GEAR10', $this->service->normalizeCode('  gear10 '));
    }

    public function test_valid_coupon_passes_with_messy_input(): void
    {
        $coupon = $this->makeCoupon();

        $this->assertTrue($this->service->check('  gear10 ', 600000)->is($coupon));
    }

    public function test_unknown_disabled_and_blank_codes_share_one_generic_message(): void
    {
        $this->makeCoupon(['code' => 'OFF', 'is_active' => false]);

        $unknown  = $this->rejection(fn () => $this->service->check('KHONGCO', 600000));
        $disabled = $this->rejection(fn () => $this->service->check('OFF', 600000));
        $blank    = $this->rejection(fn () => $this->service->check('   ', 600000));

        $this->assertSame('Mã giảm giá không hợp lệ.', $unknown);
        $this->assertSame($unknown, $disabled);
        $this->assertSame($unknown, $blank);
    }

    public function test_scheduled_coupon_mentions_start_date(): void
    {
        $start = now()->addDays(3);
        $this->makeCoupon(['starts_at' => $start]);

        $message = $this->rejection(fn () => $this->service->check('GEAR10', 600000));

        $this->assertStringContainsString($start->format('d/m/Y H:i'), $message);
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $this->makeCoupon(['expires_at' => now()->subMinute()]);

        $this->assertSame(
            'Mã giảm giá đã hết hạn.',
            $this->rejection(fn () => $this->service->check('GEAR10', 600000))
        );
    }

    public function test_exhausted_coupon_is_rejected(): void
    {
        $this->makeCoupon(['usage_limit' => 3, 'used_count' => 3]);

        $this->assertSame(
            'Mã giảm giá đã hết lượt sử dụng.',
            $this->rejection(fn () => $this->service->check('GEAR10', 600000))
        );
    }

    public function test_subtotal_below_minimum_reports_the_shortfall(): void
    {
        $this->makeCoupon(['min_order' => 500000]);

        $message = $this->rejection(fn () => $this->service->check('GEAR10', 400000));

        $this->assertStringContainsString('500.000 ₫', $message);
        $this->assertStringContainsString('còn thiếu 100.000 ₫', $message);
    }

    public function test_exactly_the_minimum_is_accepted(): void
    {
        $this->makeCoupon(['min_order' => 500000]);

        $this->assertSame('GEAR10', $this->service->check('GEAR10', 500000)->code);
    }

    public function test_empty_cart_is_rejected(): void
    {
        $this->makeCoupon(['min_order' => 0]);

        $message = $this->rejection(fn () => $this->service->check('GEAR10', 0));

        $this->assertStringContainsString('Giỏ hàng trống', $message);
    }

    public function test_coupon_state_is_reported_before_the_minimum_order(): void
    {
        $this->makeCoupon(['expires_at' => now()->subDay(), 'min_order' => 500000]);

        $this->assertSame(
            'Mã giảm giá đã hết hạn.',
            $this->rejection(fn () => $this->service->check('GEAR10', 100000))
        );
    }

    public function test_percent_discount_is_rounded_down(): void
    {
        $coupon = $this->makeCoupon(['type' => 'percent', 'value' => 10]);

        $this->assertSame(99, $this->service->calculateDiscount($coupon, 999));
        $this->assertSame(60000, $this->service->calculateDiscount($coupon, 600000));
    }

    public function test_discount_never_exceeds_subtotal(): void
    {
        $full  = $this->makeCoupon(['code' => 'FULL100', 'type' => 'percent', 'value' => 100]);
        $fixed = $this->makeCoupon(['code' => 'FIXED50', 'type' => 'fixed', 'value' => 50000]);

        $this->assertSame(600000, $this->service->calculateDiscount($full, 600000));
        $this->assertSame(30000, $this->service->calculateDiscount($fixed, 30000));    // chặn trên bởi tạm tính
        $this->assertSame(50000, $this->service->calculateDiscount($fixed, 600000));
    }

    public function test_redeem_increments_used_count(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 2]);

        $this->service->redeem($coupon);

        $this->assertSame(1, $coupon->used_count);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_redeem_stops_at_the_usage_limit(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);

        $this->service->redeem($coupon);

        $this->expectException(InvalidCouponException::class);
        $this->service->redeem($coupon);
    }

    public function test_redeem_is_safe_against_stale_copies(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);

        // Hai đơn cùng nạp mã lúc còn một lượt: cả hai đều thấy used_count = 0
        $first  = Coupon::find($coupon->id);
        $second = Coupon::find($coupon->id);

        $this->service->redeem($first);

        try {
            $this->service->redeem($second);
            $this->fail('Bản sao cũ không được phép lấy thêm lượt thứ hai.');
        } catch (InvalidCouponException) {
            // đúng như mong đợi
        }

        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_unlimited_coupon_can_always_be_redeemed(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => null]);

        for ($i = 0; $i < 5; $i++) {
            $this->service->redeem($coupon);
        }

        $this->assertSame(5, $coupon->fresh()->used_count);
    }

    public function test_redeem_is_rolled_back_with_the_transaction(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 5]);

        try {
            DB::transaction(function () use ($coupon) {
                $this->service->redeem($coupon);

                throw new \RuntimeException('Đặt hàng lỗi giữa chừng');
            });
        } catch (\RuntimeException) {
            // đúng như mong đợi
        }

        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_release_decrements_but_never_below_zero(): void
    {
        $coupon = $this->makeCoupon(['used_count' => 2]);

        $this->service->release($coupon);
        $this->assertSame(1, $coupon->fresh()->used_count);

        $this->service->release($coupon);
        $this->service->release($coupon);
        $this->assertSame(0, $coupon->fresh()->used_count);
    }
}