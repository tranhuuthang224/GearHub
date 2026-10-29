<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutCouponEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);
    }

    private function makeCoupon(array $attributes = []): Coupon
    {
        return Coupon::unguarded(fn () => Coupon::create(array_merge([
            'code' => 'GEAR10', 'type' => 'percent', 'value' => 10, 'min_order' => 500000,
            'usage_limit' => null, 'used_count' => 0,
            'starts_at' => null, 'expires_at' => null, 'is_active' => true,
        ], $attributes)));
    }

    private function preview(mixed $code, mixed $subtotal)
    {
        return $this->actingAs($this->customer)
            ->postJson(route('checkout.coupon'), ['code' => $code, 'subtotal' => $subtotal]);
    }

    public function test_guest_is_rejected(): void
    {
        $this->makeCoupon();

        $this->postJson(route('checkout.coupon'), ['code' => 'GEAR10', 'subtotal' => 600000])
            ->assertUnauthorized();
    }

    public function test_valid_preview_returns_amounts_and_canonical_code(): void
    {
        $this->makeCoupon();

        $this->preview(' gear10 ', 600000)
            ->assertOk()
            ->assertJson([
                'valid'    => true,
                'code'     => 'GEAR10',
                'label'    => '10%',
                'subtotal' => 600000,
                'discount' => 60000,
                'total'    => 540000,
            ])
            ->assertJsonStructure(['valid', 'code', 'label', 'subtotal', 'discount', 'total', 'message']);
    }

    public function test_invalid_coupons_return_422_with_a_reason(): void
    {
        $this->makeCoupon(['code' => 'OFF', 'is_active' => false]);
        $this->makeCoupon(['code' => 'OLD', 'expires_at' => now()->subDay()]);

        $this->preview('KHONGCO', 600000)->assertStatus(422)
            ->assertJson(['valid' => false, 'message' => 'Mã giảm giá không hợp lệ.']);

        $this->preview('OFF', 600000)->assertStatus(422)
            ->assertJson(['valid' => false, 'message' => 'Mã giảm giá không hợp lệ.']);

        $this->preview('OLD', 600000)->assertStatus(422)
            ->assertJson(['valid' => false, 'message' => 'Mã giảm giá đã hết hạn.']);
    }

    public function test_minimum_order_message_includes_the_shortfall(): void
    {
        $this->makeCoupon(['min_order' => 500000]);

        $this->preview('GEAR10', 400000)
            ->assertStatus(422)
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Đơn hàng cần từ 500.000 ₫ để dùng mã này (còn thiếu 100.000 ₫).');
    }

    public function test_missing_or_malformed_input_returns_consistent_json(): void
    {
        $this->makeCoupon();

        $this->actingAs($this->customer)
            ->postJson(route('checkout.coupon'), ['subtotal' => 600000])
            ->assertStatus(422)
            ->assertJson(['valid' => false, 'message' => 'Vui lòng nhập mã giảm giá.']);

        $this->preview('GEAR10', 'abc')
            ->assertStatus(422)
            ->assertJson(['valid' => false, 'message' => 'Tạm tính không hợp lệ.']);

        $this->preview('GEAR10', -5)
            ->assertStatus(422)
            ->assertJson(['valid' => false]);
    }

    public function test_preview_does_not_consume_a_use(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);

        $this->preview('GEAR10', 600000)->assertOk();
        $this->preview('GEAR10', 600000)->assertOk();

        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_endpoint_is_rate_limited(): void
    {
        $this->makeCoupon();

        for ($i = 1; $i <= 20; $i++) {
            $this->preview('GEAR10', 600000)->assertOk();
        }

        $this->preview('GEAR10', 600000)->assertStatus(429);
    }
}