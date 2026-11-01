<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $customerA;
    private User $customerB;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerA = User::create([
            'name'     => 'Khách Hàng A',
            'email'    => 'khach_a@gearhub.vn',
            'password' => bcrypt('password123'),
            'phone'    => '0912345678',
        ]);

        $this->customerB = User::create([
            'name'     => 'Khách Hàng B',
            'email'    => 'khach_b@gearhub.vn',
            'password' => bcrypt('password123'),
            'phone'    => '0987654321',
        ]);

        $category = Category::create(['name' => 'Bàn phím cơ']);
        $brand = Brand::create(['name' => 'Keychron']);

        $this->product = Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => 'Keychron Q1 Pro',
            'sku'            => 'KC-Q1P',
            'price'          => 4290000,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);
    }

    private function createOrderFor(User $user, string $status = Order::STATUS_PENDING, int $quantity = 2): Order
    {
        $subtotal = $this->product->price * $quantity;
        $order = Order::create([
            'code'             => 'GH-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'user_id'          => $user->id,
            'receiver_name'    => $user->name,
            'phone'            => $user->phone ?? '0912345678',
            'shipping_address' => '123 Đường Linh Trung, TP. Thủ Đức',
            'payment_method'   => 'cod',
            'status'           => $status,
            'subtotal'         => $subtotal,
            'discount'         => 0,
            'total'            => $subtotal,
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price'   => $this->product->price,
            'quantity'     => $quantity,
        ]);

        return $order;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
        $this->get(route('orders.show', 'GH-ANY'))->assertRedirect(route('login'));
    }

    public function test_customer_can_see_only_their_own_orders(): void
    {
        $orderA = $this->createOrderFor($this->customerA);
        $orderB = $this->createOrderFor($this->customerB);

        $response = $this->actingAs($this->customerA)->get(route('orders.index'));

        $response->assertOk();
        $response->assertViewIs('client.orders.index');
        $response->assertSee($orderA->code);
        $response->assertDontSee($orderB->code);
    }

    public function test_customer_can_filter_orders_by_status(): void
    {
        $pendingOrder = $this->createOrderFor($this->customerA, Order::STATUS_PENDING);
        $completedOrder = $this->createOrderFor($this->customerA, Order::STATUS_COMPLETED);

        // Lọc Pending
        $resPending = $this->actingAs($this->customerA)->get(route('orders.index', ['status' => 'pending']));
        $resPending->assertOk();
        $resPending->assertSee($pendingOrder->code);
        $resPending->assertDontSee($completedOrder->code);

        // Lọc Completed
        $resCompleted = $this->actingAs($this->customerA)->get(route('orders.index', ['status' => 'completed']));
        $resCompleted->assertOk();
        $resCompleted->assertSee($completedOrder->code);
        $resCompleted->assertDontSee($pendingOrder->code);
    }

    public function test_customer_can_view_order_detail(): void
    {
        $order = $this->createOrderFor($this->customerA);

        $response = $this->actingAs($this->customerA)->get(route('orders.show', $order->code));

        $response->assertOk();
        $response->assertViewIs('client.orders.show');
        $response->assertSee($order->code);
        $response->assertSee('Keychron Q1 Pro');
        $response->assertSee('123 Đường Linh Trung, TP. Thủ Đức');
    }

    public function test_customer_cannot_view_another_customer_order(): void
    {
        $orderB = $this->createOrderFor($this->customerB);

        $response = $this->actingAs($this->customerA)->get(route('orders.show', $orderB->code));

        $response->assertNotFound();
    }

    public function test_customer_can_cancel_pending_order_and_restores_stock(): void
    {
        // Giả sử kho còn 10, khách A đặt 2 -> kho còn 8
        $this->product->update(['stock_quantity' => 8]);
        $order = $this->createOrderFor($this->customerA, Order::STATUS_PENDING, 2);

        $response = $this->actingAs($this->customerA)->post(route('orders.cancel', $order->code));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);

        // Tồn kho được hoàn lại: 8 + 2 = 10
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);
    }

    public function test_customer_cannot_cancel_shipping_or_completed_order(): void
    {
        $shippingOrder = $this->createOrderFor($this->customerA, Order::STATUS_SHIPPING);

        $response = $this->actingAs($this->customerA)->post(route('orders.cancel', $shippingOrder->code));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_SHIPPING, $shippingOrder->fresh()->status);
    }

    public function test_customer_can_reorder_products_from_previous_order(): void
    {
        $order = $this->createOrderFor($this->customerA, Order::STATUS_COMPLETED, 1);

        $response = $this->actingAs($this->customerA)->post(route('orders.reorder', $order->code));

        $response->assertRedirect(route('cart.index'));
        $this->assertEquals(1, session('cart')[$this->product->id]['quantity']);
    }
}
