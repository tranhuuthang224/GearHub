<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $stranger;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner    = User::create(['name' => 'Chu', 'email' => 'chu@test.vn', 'password' => 'password123']);
        $this->stranger = User::create(['name' => 'La', 'email' => 'la@test.vn', 'password' => 'password123']);
        $this->admin    = User::unguarded(fn () => User::create([
            'name' => 'Admin', 'email' => 'admin@test.vn', 'password' => 'password123',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]));
    }

    private function orderWithStatus(string $status): Order
    {
        return Order::create([
            'code' => 'GH-' . uniqid(), 'user_id' => $this->owner->id,
            'receiver_name' => 'A', 'phone' => '0900000000', 'shipping_address' => 'HCM',
            'status' => $status, 'subtotal' => 1000000, 'total' => 1000000,
        ]);
    }

    public function test_policy_is_discovered_for_the_order_model(): void
    {
        $this->assertInstanceOf(OrderPolicy::class, Gate::getPolicyFor(Order::class));
    }

    public function test_owner_and_admin_can_view_but_strangers_get_not_found(): void
    {
        $order = $this->orderWithStatus(Order::STATUS_PENDING);

        $this->assertTrue(Gate::forUser($this->owner)->allows('view', $order));
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $order));

        $response = Gate::forUser($this->stranger)->inspect('view', $order);
        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }

    public function test_customer_can_cancel_only_own_pending_orders(): void
    {
        $pending  = $this->orderWithStatus(Order::STATUS_PENDING);
        $shipping = $this->orderWithStatus(Order::STATUS_SHIPPING);

        $this->assertTrue(Gate::forUser($this->owner)->allows('cancel', $pending));
        $this->assertFalse(Gate::forUser($this->owner)->allows('cancel', $shipping));
        $this->assertFalse(Gate::forUser($this->stranger)->allows('cancel', $pending));
    }

    public function test_admin_can_cancel_pending_and_shipping_but_not_final_orders(): void
    {
        foreach ([Order::STATUS_PENDING, Order::STATUS_SHIPPING] as $status) {
            $this->assertTrue(Gate::forUser($this->admin)->allows('cancel', $this->orderWithStatus($status)), $status);
        }

        foreach ([Order::STATUS_COMPLETED, Order::STATUS_CANCELLED] as $status) {
            $this->assertFalse(Gate::forUser($this->admin)->allows('cancel', $this->orderWithStatus($status)), $status);
        }
    }

    public function test_only_admins_can_update_status(): void
    {
        $order = $this->orderWithStatus(Order::STATUS_PENDING);

        $this->assertTrue(Gate::forUser($this->admin)->allows('updateStatus', $order));
        $this->assertFalse(Gate::forUser($this->owner)->allows('updateStatus', $order));
        $this->assertFalse(Gate::forUser($this->stranger)->allows('updateStatus', $order));
    }
}