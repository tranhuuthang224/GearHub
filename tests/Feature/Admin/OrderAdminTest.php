<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAdminTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;
    private User $admin;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service  = app(OrderService::class);
        $this->customer = User::create(['name' => 'Khach', 'email' => 'khach@test.vn', 'password' => 'password123']);
        $this->admin    = User::unguarded(fn () => User::create([
            'name' => 'Admin', 'email' => 'admin@test.vn', 'password' => 'password123',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]));

        $parent   = Category::create(['name' => 'Phụ kiện']);
        $category = Category::create(['name' => 'Chuột gaming', 'parent_id' => $parent->id]);
        $brand    = Brand::create(['name' => 'Logitech']);

        $this->product = Product::create([
            'category_id' => $category->id, 'brand_id' => $brand->id,
            'name' => 'Chuột thử nghiệm', 'sku' => 'MS-1', 'price' => 1000000, 'stock_quantity' => 20,
        ]);
    }

    private function makeOrder(array $shipping = [], int $quantity = 2): Order
    {
        return $this->service->createOrder(
            $this->customer,
            [['product_id' => $this->product->id, 'quantity' => $quantity]],
            array_merge([
                'receiver_name'    => 'Nguyễn Văn An',
                'phone'            => '0901111111',
                'shipping_address' => '1 Lê Lợi, Quận 1, TP.HCM',
                'note'             => 'Giao nhanh',
            ], $shipping)
        );
    }

    private function stock(): int
    {
        return (int) Product::withTrashed()->find($this->product->id)->stock_quantity;
    }

    /** Gửi yêu cầu đổi trạng thái; from() để back() trả về đúng trang chi tiết. */
    private function changeStatus(Order $order, mixed $status)
    {
        return $this->actingAs($this->admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => $status]);
    }

    public function test_customer_cannot_reach_the_order_admin(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->customer)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($this->customer)->get(route('admin.orders.show', $order))->assertForbidden();
        $this->actingAs($this->customer)
            ->patch(route('admin.orders.status', $order), ['status' => 'shipping'])
            ->assertForbidden();

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_index_lists_newest_first_with_status_counts_and_filter(): void
    {
        $first  = $this->makeOrder(['phone' => '0911000001']);
        $second = $this->makeOrder(['phone' => '0911000002']);
        $third  = $this->makeOrder(['phone' => '0911000003']);

        $this->service->updateStatus($second, Order::STATUS_SHIPPING, $this->admin);
        $this->service->cancelOrder($third, $this->admin);

        $this->actingAs($this->admin)->get(route('admin.orders.index'))
            ->assertOk()
            ->assertViewHas('orders', fn ($o) => $o->pluck('code')->all() === [$third->code, $second->code, $first->code])
            ->assertViewHas('counts', fn ($c) => (int) $c->get('pending') === 1
                && (int) $c->get('shipping') === 1
                && (int) $c->get('cancelled') === 1);

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'shipping']))
            ->assertViewHas('orders', fn ($o) => $o->pluck('code')->all() === [$second->code]);

        // Giá trị trạng thái lạ coi như không lọc
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'bat-ky']))
            ->assertViewHas('orders', fn ($o) => $o->count() === 3);
    }

    public function test_index_searches_by_code_name_and_phone(): void
    {
        $an    = $this->makeOrder(['receiver_name' => 'Nguyễn Văn An', 'phone' => '0911000001']);
        $binh  = $this->makeOrder(['receiver_name' => 'Trần Thị Bình', 'phone' => '0922000002']);

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => $an->code]))
            ->assertViewHas('orders', fn ($o) => $o->pluck('code')->all() === [$an->code]);

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => 'Bình']))
            ->assertViewHas('orders', fn ($o) => $o->pluck('code')->all() === [$binh->code]);

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => '0922000002']))
            ->assertViewHas('orders', fn ($o) => $o->pluck('code')->all() === [$binh->code]);
    }

    public function test_show_displays_items_prices_note_and_history(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee('Chuột thử nghiệm')
            ->assertSee('2.000.000')            // 2 × 1.000.000 ₫
            ->assertSee('Giao nhanh')
            ->assertSee('Nguyễn Văn An')
            ->assertSee('Đặt hàng')             // dòng nhật ký đầu tiên
            ->assertSee('Duyệt giao hàng')
            ->assertSee('Hủy đơn');
    }

    public function test_show_renders_forms_with_method_spoofing(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertSee(route('admin.orders.status', $order), false)
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee('name="status" value="shipping"', false)
            ->assertSee('name="status" value="cancelled"', false);
    }

    public function test_show_still_works_when_a_product_was_soft_deleted(): void
    {
        $order = $this->makeOrder();
        $this->product->delete();

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Chuột thử nghiệm')
            ->assertSee('Đã xóa');
    }

    public function test_admin_moves_an_order_through_the_flow_and_it_is_logged(): void
    {
        $order = $this->makeOrder();

        $this->changeStatus($order, 'shipping')
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('success');

        $this->changeStatus($order, 'completed')->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame(
            [[null, 'pending'], ['pending', 'shipping'], ['shipping', 'completed']],
            $order->histories()->get()->map(fn ($h) => [$h->from_status, $h->to_status])->all()
        );
        $this->assertSame($this->admin->id, $order->histories()->get()->last()->changed_by);

        // Đơn đã hoàn thành: không còn nút đổi trạng thái
        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertDontSee('Duyệt giao hàng')
            ->assertDontSee('Hoàn thành đơn')
            ->assertSee('Đơn đã kết thúc');
    }

    public function test_skipping_a_step_is_rejected_with_a_message(): void
    {
        $order = $this->makeOrder();

        $this->changeStatus($order, 'completed')
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertCount(1, $order->histories);
    }

    public function test_tampered_status_values_are_rejected(): void
    {
        $order = $this->makeOrder();

        $this->changeStatus($order, 'pending')->assertSessionHasErrors('status');
        $this->changeStatus($order, 'khong-ton-tai')->assertSessionHasErrors('status');
        $this->changeStatus($order, '')->assertSessionHasErrors('status');

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_admin_cancel_restores_stock_and_is_logged(): void
    {
        $order = $this->makeOrder(quantity: 3);
        $this->assertSame(17, $this->stock());

        $this->changeStatus($order, 'shipping');
        $this->changeStatus($order, 'cancelled')->assertSessionHas('success');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(20, $this->stock());
        $this->assertSame(['shipping', 'cancelled'], $order->histories()->get()->map(fn ($h) => [$h->from_status, $h->to_status])->last());
    }

    public function test_cancelled_order_cannot_be_changed_again(): void
    {
        $order = $this->makeOrder();
        $this->changeStatus($order, 'cancelled');

        $this->changeStatus($order, 'shipping')->assertSessionHas('error');
        $this->changeStatus($order, 'cancelled')->assertSessionHas('error');

        $this->assertSame(20, $this->stock());                 // chỉ hoàn kho một lần
    }
}