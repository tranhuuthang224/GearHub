<?php

namespace Tests\Feature;

use App\Models\Coupon;
use Tests\TestCase;

class CouponStatusTest extends TestCase
{
    private function coupon(array $attributes = []): Coupon
    {
        return (new Coupon())->forceFill(array_merge([
            'code' => 'TEST', 'type' => 'percent', 'value' => 10, 'min_order' => 0,
            'usage_limit' => null, 'used_count' => 0,
            'starts_at' => null, 'expires_at' => null, 'is_active' => true,
        ], $attributes));
    }

    public function test_coupon_without_limits_is_active(): void
    {
        $this->assertSame(Coupon::STATUS_ACTIVE, $this->coupon()->status());
    }

    public function test_disabled_coupon_is_inactive_even_if_otherwise_valid(): void
    {
        $this->assertSame(Coupon::STATUS_INACTIVE, $this->coupon(['is_active' => false])->status());
    }

    public function test_coupon_before_start_is_scheduled(): void
    {
        $coupon = $this->coupon(['starts_at' => now()->addDay()]);

        $this->assertSame(Coupon::STATUS_SCHEDULED, $coupon->status());
    }

    public function test_coupon_after_expiry_is_expired(): void
    {
        $coupon = $this->coupon(['expires_at' => now()->subMinute()]);

        $this->assertSame(Coupon::STATUS_EXPIRED, $coupon->status());
    }

    public function test_coupon_with_all_uses_taken_is_exhausted(): void
    {
        $coupon = $this->coupon(['usage_limit' => 5, 'used_count' => 5]);

        $this->assertSame(Coupon::STATUS_EXHAUSTED, $coupon->status());
    }

    public function test_unlimited_coupon_never_runs_out(): void
    {
        $coupon = $this->coupon(['usage_limit' => null, 'used_count' => 999999]);

        $this->assertSame(Coupon::STATUS_ACTIVE, $coupon->status());
    }

    public function test_status_can_be_evaluated_at_another_moment(): void
    {
        $coupon = $this->coupon(['starts_at' => now()->addDay(), 'expires_at' => now()->addDays(3)]);

        $this->assertSame(Coupon::STATUS_ACTIVE, $coupon->status(now()->addDays(2)));
        $this->assertSame(Coupon::STATUS_EXPIRED, $coupon->status(now()->addDays(4)));
    }

    public function test_discount_label(): void
    {
        $this->assertSame('10%', $this->coupon(['type' => 'percent', 'value' => 10])->discountLabel());
        $this->assertSame('50.000 ₫', $this->coupon(['type' => 'fixed', 'value' => 50000])->discountLabel());
    }
}