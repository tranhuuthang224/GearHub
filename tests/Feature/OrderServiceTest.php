<?php

namespace Tests\Feature;

use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Order\InsufficientStockException;
use App\Exceptions\Order\InvalidCouponException;
use App\Exceptions\Order\InvalidOrderTransitionException;
use App\Exceptions\Order\ProductUnavailableException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Tests\TestCase;
use App\Models\OrderStatusHistory;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;
    private User $customer;
    private User $admin;
    private Category $category;
    private Brand $brand;
    private Product $keyboard;   // 1.000.000 ₫, tồn 10
    private Product $mouse;      // 500.000 ₫ (khuyến mãi 400.000 ₫), tồn 3

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderService::class);

        $this->customer = User::create(['name' => 'Khach', 'email' => 'khach@test.vn', 'password' => 'password123']);
        $this->admin    = User::unguarded(fn () => User::create([
            'name' => 'Admin', 'email' => 'admin@test.vn', 'password' => 'password123',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]));

        $parent         = Category::create(['name' => 'Phụ kiện']);
        $this->category = Category::create(['name' => 'Chuột gaming', 'parent_id' => $parent->id]);
        $this->brand    = Brand::create(['name' => 'Logitech']);

        $this->keyboard = $this->makeProduct('KB-1', 1000000, null, 10);
        $this->mouse    = $this->makeProduct('MS-1', 500000, 400000, 3);
    }

    // ---------- Hỗ trợ ----------

    private function makeProduct(string $sku, int $price, ?int $salePrice, int $stock): Product
    {
        return Product::create([
            'category_id' => $this->category->id, 'brand_id' => $this->brand->id,
            'name' => "Sản phẩm {$sku}", 'sku' => $sku,
            'price' => $price, 'sale_price' => $salePrice, 'stock_quantity' => $stock,
        ]);
    }

    private function makeCoupon(array $attributes = []): Coupon
    {
        // used_count không nằm trong $fillable nên phải bỏ qua bảo vệ có chủ đích
        return Coupon::unguarded(fn () => Coupon::create(array_merge([
            'code' => 'GEAR10', 'type' => 'percent', 'value' => 10, 'min_order' => 500000,
            'usage_limit' => 5, 'used_count' => 0,
            'starts_at' => null, 'expires_at' => null, 'is_active' => true,
        ], $attributes)));
    }

    private function line(Product $product, int $quantity): array
    {
        return ['product_id' => $product->id, 'quantity' => $quantity];
    }

    private function shipping(array $override = []): array
    {
        return array_merge([
            'receiver_name'    => 'Nguyễn Văn An',
            'phone'            => '0901111111',
            'shipping_address' => '1 Lê Lợi, Quận 1, TP.HCM',
        ], $override);
    }

    private function place(array $items, ?string $coupon = null, ?User $user = null): Order
    {
        return $this->service->createOrder($user ?? $this->customer, $items, $this->shipping(), $coupon);
    }

    /** Tồn kho hiện tại (kể cả sản phẩm đã xóa mềm). */
    private function stock(Product $product): int
    {
        return (int) Product::withTrashed()->find($product->id)->stock_quantity;
    }

    /** Chạy một đoạn mã mong đợi ném đúng loại ngoại lệ, rồi trả về ngoại lệ đó để kiểm tra tiếp. */
    private function rejection(string $class, callable $callback): \Throwable
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            $this->assertInstanceOf($class, $e);

            return $e;
        }

        $this->fail("Mong đợi {$class} nhưng không có ngoại lệ nào được ném.");
    }

    // ---------- createOrder ----------

    public function test_creates_order_with_snapshot_prices_totals_and_stock(): void
    {
        $order = $this->service->createOrder($this->customer, [
            $this->line($this->keyboard, 2),
            $this->line($this->mouse, 1),
        ], $this->shipping(['note' => 'Giao giờ hành chính']));

        $this->assertTrue($order->relationLoaded('items'));
        $this->assertMatchesRegularExpression('/^GH-\d{6}-[A-Z0-9]{6}$/', $order->code);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame($this->customer->id, $order->user_id);
        $this->assertNull($order->coupon_id);
        $this->assertSame(2400000, $order->subtotal);    // 2 × 1.000.000 + 400.000 (giá khuyến mãi)
        $this->assertSame(0, $order->discount);
        $this->assertSame(2400000, $order->total);
        $this->assertSame('Giao giờ hành chính', $order->note);

        $items = $order->items->keyBy('product_id');
        $this->assertCount(2, $items);
        $this->assertSame(1000000, $items[$this->keyboard->id]->unit_price);
        $this->assertSame(400000, $items[$this->mouse->id]->unit_price);     // giá bán thực tế, không phải giá gốc
        $this->assertSame($this->mouse->name, $items[$this->mouse->id]->product_name);

        $this->assertSame(8, $this->stock($this->keyboard));
        $this->assertSame(2, $this->stock($this->mouse));
    }

    public function test_client_supplied_prices_are_ignored(): void
    {
        $order = $this->service->createOrder($this->customer, [
            ['product_id' => $this->keyboard->id, 'quantity' => 1, 'price' => 1, 'unit_price' => 1],
        ], $this->shipping());

        $this->assertSame(1000000, $order->items->first()->unit_price);
        $this->assertSame(1000000, $order->total);
    }

    public function test_duplicate_cart_lines_are_merged(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1), $this->line($this->keyboard, 2)]);

        $this->assertCount(1, $order->items);
        $this->assertSame(3, $order->items->first()->quantity);
        $this->assertSame(7, $this->stock($this->keyboard));
    }

    public function test_empty_cart_is_rejected(): void
    {
        $this->rejection(EmptyCartException::class, fn () => $this->place([]));

        $this->assertSame(0, Order::count());
    }

    public function test_malformed_cart_lines_are_rejected(): void
    {
        $carts = [
            [['product_id' => $this->keyboard->id, 'quantity' => 0]],
            [['product_id' => $this->keyboard->id, 'quantity' => -2]],
            [['quantity' => 1]],
            [['product_id' => 'abc', 'quantity' => 1]],
        ];

        foreach ($carts as $cart) {
            $this->rejection(InvalidArgumentException::class, fn () => $this->place($cart));
        }

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $this->stock($this->keyboard));
    }

    public function test_missing_shipping_data_is_rejected(): void
    {
        foreach (['receiver_name', 'phone', 'shipping_address'] as $key) {
            $shipping = $this->shipping();
            unset($shipping[$key]);

            $this->rejection(
                InvalidArgumentException::class,
                fn () => $this->service->createOrder($this->customer, [$this->line($this->keyboard, 1)], $shipping)
            );
        }

        $this->assertSame(0, Order::count());
    }

    public function test_unavailable_products_are_rejected(): void
    {
        // Đã xóa mềm
        $this->mouse->delete();
        $e = $this->rejection(ProductUnavailableException::class, fn () => $this->place([$this->line($this->mouse, 1)]));
        $this->assertStringContainsString($this->mouse->name, $e->getMessage());

        // Đang tắt hiển thị
        $this->keyboard->update(['is_active' => false]);
        $this->rejection(ProductUnavailableException::class, fn () => $this->place([$this->line($this->keyboard, 1)]));

        // Không tồn tại
        $e = $this->rejection(ProductUnavailableException::class, fn () => $this->place([['product_id' => 999999, 'quantity' => 1]]));
        $this->assertStringContainsString('#999999', $e->getMessage());

        $this->assertSame(0, Order::count());
    }

    public function test_insufficient_stock_is_rejected_without_touching_other_lines(): void
    {
        $e = $this->rejection(InsufficientStockException::class, fn () => $this->place([
            $this->line($this->keyboard, 2),     // đủ hàng
            $this->line($this->mouse, 5),        // chỉ còn 3
        ]));

        $this->assertSame($this->mouse->name, $e->productName);
        $this->assertSame(3, $e->available);

        $this->assertSame(10, $this->stock($this->keyboard));
        $this->assertSame(3, $this->stock($this->mouse));
        $this->assertSame(0, Order::count());
    }

    public function test_the_last_units_cannot_be_sold_twice(): void
    {
        $this->place([$this->line($this->mouse, 2)]);                      // còn 1

        $e = $this->rejection(InsufficientStockException::class, fn () => $this->place([$this->line($this->mouse, 2)]));

        $this->assertSame(1, $e->available);
        $this->assertSame(1, $this->stock($this->mouse));
        $this->assertSame(1, Order::count());
    }

    public function test_order_can_take_exactly_the_remaining_stock(): void
    {
        $this->place([$this->line($this->mouse, 3)]);

        $this->assertSame(0, $this->stock($this->mouse));

        $e = $this->rejection(InsufficientStockException::class, fn () => $this->place([$this->line($this->mouse, 1)]));
        $this->assertStringContainsString('hết hàng', $e->getMessage());
    }

    public function test_coupon_discount_and_usage_are_recorded(): void
    {
        $coupon = $this->makeCoupon();

        $order = $this->place([$this->line($this->keyboard, 2), $this->line($this->mouse, 1)], 'gear10');

        $this->assertSame(2400000, $order->subtotal);
        $this->assertSame(240000, $order->discount);              // 10%
        $this->assertSame(2160000, $order->total);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_invalid_coupon_aborts_the_whole_order(): void
    {
        $this->makeCoupon(['code' => 'OLD', 'expires_at' => now()->subDay()]);
        $this->makeCoupon(['code' => 'GEAR10']);

        // Mã hết hạn
        $this->rejection(InvalidCouponException::class, fn () => $this->place([$this->line($this->keyboard, 1)], 'OLD'));

        // Chưa đạt đơn tối thiểu 500.000 ₫ (giỏ chỉ 400.000 ₫)
        $this->rejection(InvalidCouponException::class, fn () => $this->place([$this->line($this->mouse, 1)], 'GEAR10'));

        $this->assertSame(10, $this->stock($this->keyboard));
        $this->assertSame(3, $this->stock($this->mouse));
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Coupon::where('code', 'GEAR10')->first()->used_count);
    }

    public function test_blank_coupon_is_ignored(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)], '   ');

        $this->assertNull($order->coupon_id);
        $this->assertSame(0, $order->discount);
    }

    public function test_exhausted_coupon_cannot_be_used_by_a_second_order(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);

        $this->place([$this->line($this->keyboard, 1)], 'GEAR10');

        $e = $this->rejection(InvalidCouponException::class, fn () => $this->place([$this->line($this->keyboard, 1)], 'GEAR10'));

        $this->assertStringContainsString('hết lượt', $e->getMessage());
        $this->assertSame(9, $this->stock($this->keyboard));              // chỉ trừ một lần
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame(1, Order::count());
    }

    public function test_failure_after_stock_is_taken_rolls_everything_back(): void
    {
        $coupon = $this->makeCoupon();

        // Giả lập lỗi ở bước cuối (lưu đơn), sau khi kho đã bị trừ và lượt mã đã được ghi nhận
        Order::creating(fn () => throw new RuntimeException('Lỗi giả lập khi lưu đơn'));

        try {
            $this->place([$this->line($this->keyboard, 2)], 'GEAR10');
            $this->fail('Mong đợi ngoại lệ giả lập.');
        } catch (RuntimeException $e) {
            $this->assertSame('Lỗi giả lập khi lưu đơn', $e->getMessage());
        } finally {
            Order::flushEventListeners();
        }

        $this->assertSame(10, $this->stock($this->keyboard));
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame(0, Order::count());
    }

    // ---------- cancelOrder ----------

    public function test_customer_cancels_own_pending_order_and_everything_is_restored(): void
    {
        $coupon = $this->makeCoupon();
        $order  = $this->place([$this->line($this->keyboard, 2), $this->line($this->mouse, 1)], 'GEAR10');

        $this->assertSame(8, $this->stock($this->keyboard));
        $this->assertSame(1, $coupon->fresh()->used_count);

        $cancelled = $this->service->cancelOrder($order, $this->customer);

        $this->assertSame(Order::STATUS_CANCELLED, $cancelled->status);
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(10, $this->stock($this->keyboard));
        $this->assertSame(3, $this->stock($this->mouse));
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame($coupon->id, $order->fresh()->coupon_id);       // lịch sử đơn giữ nguyên mã
    }

    public function test_admin_can_cancel_a_shipping_order(): void
    {
        $order = $this->place([$this->line($this->keyboard, 2)]);
        $this->service->updateStatus($order, Order::STATUS_SHIPPING);

        $cancelled = $this->service->cancelOrder($order, $this->admin);

        $this->assertSame(Order::STATUS_CANCELLED, $cancelled->status);
        $this->assertSame(10, $this->stock($this->keyboard));
    }

    public function test_customer_cannot_cancel_a_shipping_order(): void
    {
        $order = $this->place([$this->line($this->keyboard, 2)]);
        $this->service->updateStatus($order, Order::STATUS_SHIPPING);

        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->cancelOrder($order, $this->customer));

        $this->assertSame(Order::STATUS_SHIPPING, $order->fresh()->status);
        $this->assertSame(8, $this->stock($this->keyboard));
    }

    public function test_customer_cannot_cancel_someone_elses_order(): void
    {
        $order = $this->place([$this->line($this->keyboard, 2)]);
        $other = User::create(['name' => 'Khac', 'email' => 'khac@test.vn', 'password' => 'password123']);

        $this->rejection(AuthorizationException::class, fn () => $this->service->cancelOrder($order, $other));

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(8, $this->stock($this->keyboard));
    }

    public function test_completed_and_cancelled_orders_cannot_be_cancelled(): void
    {
        $completed = $this->place([$this->line($this->keyboard, 1)]);
        $this->service->updateStatus($completed, Order::STATUS_SHIPPING);
        $this->service->updateStatus($completed, Order::STATUS_COMPLETED);

        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->cancelOrder($completed, $this->admin));
        $this->assertSame(Order::STATUS_COMPLETED, $completed->fresh()->status);

        $cancelled = $this->place([$this->line($this->keyboard, 1)]);
        $this->service->cancelOrder($cancelled, $this->admin);

        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->cancelOrder($cancelled, $this->admin));
    }

    public function test_cancelling_twice_with_stale_copies_restores_stock_only_once(): void
    {
        $order = $this->place([$this->line($this->keyboard, 2)]);            // tồn còn 8

        // Hai yêu cầu hủy cùng nạp đơn lúc còn pending
        $first  = Order::find($order->id);
        $second = Order::find($order->id);

        $this->service->cancelOrder($first, $this->customer);

        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->cancelOrder($second, $this->customer));

        $this->assertSame(10, $this->stock($this->keyboard));                // không phải 12
    }

    public function test_cancelling_restores_stock_of_a_soft_deleted_product(): void
    {
        $order = $this->place([$this->line($this->keyboard, 2)]);
        $this->keyboard->delete();

        $this->service->cancelOrder($order, $this->customer);

        $this->assertSame(10, $this->stock($this->keyboard));
    }

    // ---------- updateStatus ----------

    public function test_status_moves_through_the_allowed_flow(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)]);

        $shipping = $this->service->updateStatus($order, Order::STATUS_SHIPPING);
        $this->assertSame(Order::STATUS_SHIPPING, $shipping->status);

        $completed = $this->service->updateStatus($shipping, Order::STATUS_COMPLETED);
        $this->assertSame(Order::STATUS_COMPLETED, $completed->status);
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
    }

    public function test_invalid_status_transitions_are_rejected(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)]);

        // Bỏ qua bước giao hàng
        $e = $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->updateStatus($order, Order::STATUS_COMPLETED));
        $this->assertStringContainsString('Chờ xử lý', $e->getMessage());

        // Trạng thái không tồn tại
        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->updateStatus($order, 'khong-ton-tai'));

        // Đã hoàn thành thì không đi lùi
        $this->service->updateStatus($order, Order::STATUS_SHIPPING);
        $this->service->updateStatus($order, Order::STATUS_COMPLETED);
        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->updateStatus($order, Order::STATUS_SHIPPING));
    }

    public function test_cancelling_through_update_status_is_a_programming_error(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)]);

        $this->rejection(LogicException::class, fn () => $this->service->updateStatus($order, Order::STATUS_CANCELLED));

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(9, $this->stock($this->keyboard));
    }

    public function test_status_changes_do_not_touch_stock(): void
    {
        $order = $this->place([$this->line($this->keyboard, 2)]);

        $this->service->updateStatus($order, Order::STATUS_SHIPPING);
        $this->service->updateStatus($order, Order::STATUS_COMPLETED);

        $this->assertSame(8, $this->stock($this->keyboard));
    }
        // ---------- Nhật ký trạng thái ----------

    /** @return array<int, array{0: ?string, 1: string, 2: ?int}> */
    private function historyOf(Order $order): array
    {
        return $order->histories()->get()
            ->map(fn ($h) => [$h->from_status, $h->to_status, $h->changed_by])
            ->all();
    }

    public function test_creating_an_order_writes_the_first_history_row(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)]);

        $this->assertSame([[null, 'pending', $this->customer->id]], $this->historyOf($order));
    }

    public function test_status_flow_is_logged_with_actors(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)]);

        $this->service->updateStatus($order, Order::STATUS_SHIPPING, $this->admin);
        $this->service->updateStatus($order, Order::STATUS_COMPLETED, $this->admin);

        $this->assertSame([
            [null, 'pending', $this->customer->id],
            ['pending', 'shipping', $this->admin->id],
            ['shipping', 'completed', $this->admin->id],
        ], $this->historyOf($order));
    }

    public function test_update_status_without_actor_logs_a_null_actor(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)]);

        $this->service->updateStatus($order, Order::STATUS_SHIPPING);

        $this->assertSame(['pending', 'shipping', null], $this->historyOf($order)[1]);
    }

    public function test_cancellation_is_logged_with_the_person_who_cancelled(): void
    {
        $byCustomer = $this->place([$this->line($this->keyboard, 1)]);
        $this->service->cancelOrder($byCustomer, $this->customer);

        $byAdmin = $this->place([$this->line($this->keyboard, 1)]);
        $this->service->updateStatus($byAdmin, Order::STATUS_SHIPPING, $this->admin);
        $this->service->cancelOrder($byAdmin, $this->admin);

        $this->assertSame(['pending', 'cancelled', $this->customer->id], $this->historyOf($byCustomer)[1]);
        $this->assertSame(['shipping', 'cancelled', $this->admin->id], $this->historyOf($byAdmin)[2]);
    }

    public function test_rejected_operations_leave_no_history_behind(): void
    {
        $order = $this->place([$this->line($this->keyboard, 1)]);

        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->updateStatus($order, Order::STATUS_COMPLETED, $this->admin));

        // Hủy hai lần với hai bản sao cũ: chỉ có một dòng nhật ký hủy
        $first  = Order::find($order->id);
        $second = Order::find($order->id);
        $this->service->cancelOrder($first, $this->customer);
        $this->rejection(InvalidOrderTransitionException::class, fn () => $this->service->cancelOrder($second, $this->customer));

        $this->assertCount(2, $this->historyOf($order));                  // tạo đơn + hủy
    }

    public function test_failed_order_creation_leaves_no_history(): void
    {
        Order::creating(fn () => throw new RuntimeException('Lỗi giả lập khi lưu đơn'));

        try {
            $this->place([$this->line($this->keyboard, 1)]);
            $this->fail('Mong đợi ngoại lệ giả lập.');
        } catch (RuntimeException) {
            // đúng như mong đợi
        } finally {
            Order::flushEventListeners();
        }

        $this->assertSame(0, OrderStatusHistory::count());
    }
}