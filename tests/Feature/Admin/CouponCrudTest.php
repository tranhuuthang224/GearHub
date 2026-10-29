<?php

namespace Tests\Feature\Admin;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::unguarded(fn () => User::create([
            'name' => 'Admin', 'email' => 'admin@test.vn', 'password' => 'password123',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]));
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'code'        => 'SAVE10',
            'type'        => 'percent',
            'value'       => 10,
            'min_order'   => 500000,
            'usage_limit' => 100,
            'starts_at'   => now()->addHour()->format('Y-m-d\TH:i'),
            'expires_at'  => now()->addMonth()->format('Y-m-d\TH:i'),
            'is_active'   => 1,
        ], $override);
    }

    private function makeCoupon(string $code = 'SAVE10', array $attributes = []): Coupon
    {
        return Coupon::unguarded(fn () => Coupon::create(array_merge([
            'code' => $code, 'type' => 'percent', 'value' => 10, 'min_order' => 0,
            'usage_limit' => null, 'used_count' => 0, 'is_active' => true,
        ], $attributes)));
    }

    private function store(array $override = [])
    {
        return $this->actingAs($this->admin)->post(route('admin.coupons.store'), $this->payload($override));
    }

    public function test_customer_cannot_manage_coupons(): void
    {
        $customer = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        $this->actingAs($customer)->get(route('admin.coupons.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.coupons.create'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.coupons.store'), $this->payload())->assertForbidden();

        $this->assertSame(0, Coupon::count());
    }

    public function test_index_filters_by_activation(): void
    {
        $this->makeCoupon('ON-1', ['is_active' => true]);
        $this->makeCoupon('OFF-1', ['is_active' => false]);

        $this->actingAs($this->admin)->get(route('admin.coupons.index', ['status' => 'off']))
            ->assertOk()
            ->assertViewHas('coupons', fn ($c) => $c->pluck('code')->all() === ['OFF-1']);

        $this->actingAs($this->admin)->get(route('admin.coupons.index', ['status' => 'on']))
            ->assertViewHas('coupons', fn ($c) => $c->pluck('code')->all() === ['ON-1']);

        $this->actingAs($this->admin)->get(route('admin.coupons.index', ['status' => 'bat-ky']))
            ->assertViewHas('coupons', fn ($c) => $c->count() === 2);
    }

    public function test_pages_render_forms_with_method_spoofing(): void
    {
        $coupon = $this->makeCoupon();

        $this->actingAs($this->admin)->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee(route('admin.coupons.destroy', $coupon), false)
            ->assertSee('name="_method" value="DELETE"', false);

        $this->actingAs($this->admin)->get(route('admin.coupons.edit', $coupon))
            ->assertOk()
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('SAVE10');
    }

    public function test_admin_can_create_coupon_with_normalized_code(): void
    {
        $this->store(['code' => '  save10 ', 'used_count' => 99])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::firstOrFail();
        $this->assertSame('SAVE10', $coupon->code);
        $this->assertSame(10, $coupon->value);
        $this->assertSame(500000, $coupon->min_order);
        $this->assertSame(0, $coupon->used_count);          // used_count không nhận từ form
        $this->assertTrue($coupon->is_active);
    }

    public function test_blank_optional_fields_become_unlimited(): void
    {
        $this->store(['usage_limit' => '', 'starts_at' => '', 'expires_at' => '', 'min_order' => ''])
            ->assertSessionHasNoErrors();

        $coupon = Coupon::firstOrFail();
        $this->assertNull($coupon->usage_limit);
        $this->assertNull($coupon->starts_at);
        $this->assertNull($coupon->expires_at);
        $this->assertSame(0, $coupon->min_order);
    }

    public function test_duplicate_code_is_rejected_regardless_of_case(): void
    {
        $this->makeCoupon('SAVE10');

        $this->store(['code' => 'save10'])->assertSessionHasErrors('code');

        $this->assertSame(1, Coupon::count());
    }

    public function test_code_must_use_safe_characters(): void
    {
        $this->store(['code' => 'SAVE 10'])->assertSessionHasErrors('code');
        $this->store(['code' => 'GIẢM10'])->assertSessionHasErrors('code');
        $this->store(['code' => ''])->assertSessionHasErrors('code');
    }

    public function test_value_rules_depend_on_type(): void
    {
        $this->store(['type' => 'percent', 'value' => 101])->assertSessionHasErrors('value');
        $this->store(['type' => 'percent', 'value' => 0])->assertSessionHasErrors('value');
        $this->store(['type' => 'fixed', 'value' => 1000.5])->assertSessionHasErrors('value');
        $this->store(['type' => 'khac', 'value' => 10])->assertSessionHasErrors('type');

        $this->store(['code' => 'FIXED50', 'type' => 'fixed', 'value' => 50000])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, Coupon::count());
    }

    public function test_dates_must_be_consistent(): void
    {
        // Hết hạn trước ngày bắt đầu
        $this->store([
            'starts_at'  => now()->addDays(5)->format('Y-m-d\TH:i'),
            'expires_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('expires_at');

        // Mã mới không được hết hạn trong quá khứ
        $this->store(['starts_at' => '', 'expires_at' => now()->subDay()->format('Y-m-d\TH:i')])
            ->assertSessionHasErrors('expires_at');

        $this->assertSame(0, Coupon::count());
    }

    public function test_admin_can_update_and_keep_own_code(): void
    {
        $coupon = $this->makeCoupon('SAVE10');

        $this->actingAs($this->admin)
            ->put(route('admin.coupons.update', $coupon), $this->payload(['value' => 15, 'is_active' => 0]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.coupons.index'));

        $fresh = $coupon->fresh();
        $this->assertSame('SAVE10', $fresh->code);
        $this->assertSame(15, $fresh->value);
        $this->assertFalse($fresh->is_active);
    }

    public function test_update_cannot_use_another_coupons_code(): void
    {
        $this->makeCoupon('AAA');
        $coupon = $this->makeCoupon('BBB');

        $this->actingAs($this->admin)
            ->put(route('admin.coupons.update', $coupon), $this->payload(['code' => 'AAA']))
            ->assertSessionHasErrors('code');
    }

    public function test_usage_limit_cannot_drop_below_used_count(): void
    {
        $coupon = $this->makeCoupon('USED', ['usage_limit' => 10, 'used_count' => 7]);

        $this->actingAs($this->admin)
            ->put(route('admin.coupons.update', $coupon), $this->payload(['code' => 'USED', 'usage_limit' => 5]))
            ->assertSessionHasErrors('usage_limit');

        $this->actingAs($this->admin)
            ->put(route('admin.coupons.update', $coupon), $this->payload(['code' => 'USED', 'usage_limit' => 7]))
            ->assertSessionHasNoErrors();
    }

    public function test_update_allows_expiry_in_the_past_to_disable_a_coupon(): void
    {
        $coupon = $this->makeCoupon('KILL');

        $this->actingAs($this->admin)
            ->put(route('admin.coupons.update', $coupon), $this->payload([
                'code' => 'KILL', 'starts_at' => '', 'expires_at' => now()->subDay()->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(Coupon::STATUS_EXPIRED, $coupon->fresh()->status());
    }

    public function test_unused_coupon_can_be_deleted(): void
    {
        $coupon = $this->makeCoupon();

        $this->actingAs($this->admin)->delete(route('admin.coupons.destroy', $coupon))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_coupon_used_by_an_order_cannot_be_deleted(): void
    {
        $coupon = $this->makeCoupon();
        $buyer  = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        Order::create([
            'code' => 'GH-0001', 'user_id' => $buyer->id, 'coupon_id' => $coupon->id,
            'receiver_name' => 'A', 'phone' => '0900000000', 'shipping_address' => 'HCM',
            'subtotal' => 1000000, 'discount' => 100000, 'total' => 900000,
        ]);

        $this->actingAs($this->admin)->delete(route('admin.coupons.destroy', $coupon))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);
    }
}