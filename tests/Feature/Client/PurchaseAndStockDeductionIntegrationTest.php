<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tích hợp phối hợp giữa A (Client Checkout) và B (Backend OrderService & Quản lý tồn kho).
 */
class PurchaseAndStockDeductionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private Product $product;
    private Coupon $coupon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'     => 'Khách Hàng Mua Sắm',
            'email'    => 'buyer@gearhub.vn',
            'password' => bcrypt('password123'),
            'phone'    => '0912345678',
            'role'     => User::ROLE_CUSTOMER,
        ]);

        $this->admin = User::create([
            'name'     => 'Quản Trị Viên',
            'email'    => 'admin@gearhub.vn',
            'password' => bcrypt('Admin@123'),
            'phone'    => '0988888888',
            'role'     => User::ROLE_ADMIN,
        ]);

        $category = Category::create(['name' => 'Chuột Gaming']);
        $brand = Brand::create(['name' => 'Logitech']);

        $this->product = Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => 'Logitech G Pro X Superlight 2',
            'sku'            => 'LOGI-GPX2',
            'price'          => 3500000,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        $this->coupon = Coupon::create([
            'code'        => 'GEAR10',
            'type'        => 'percent',
            'value'       => 10,
            'min_order'   => 1000000,
            'usage_limit' => 50,
            'used_count'  => 0,
            'is_active'   => true,
        ]);
    }

    public function test_full_purchase_flow_decrements_stock_and_records_coupon_usage(): void
    {
        // 1. Khách hàng thêm 3 chuột vào giỏ hàng
        $addRes = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 3,
        ]);
        $addRes->assertRedirect();
        $this->assertEquals(3, session('cart')[$this->product->id]['quantity']);

        // 2. Khách hàng tiến hành checkout với mã giảm giá GEAR10
        $this->actingAs($this->customer)->get(route('checkout.index'));
        $token1 = session('checkout_token');

        $checkoutRes = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'checkout_token'   => $token1,
            'receiver_name'    => 'Khách Hàng Mua Sắm',
            'phone'            => '0912345678',
            'shipping_address' => 'Số 123 Đường Linh Trung, Phường Linh Trung, TP. Thủ Đức',
            'note'             => 'Giao giờ hành chính',
            'coupon_code'      => 'GEAR10',
            'payment_method'   => 'cod',
        ]);

        // 3. Kiểm tra đơn hàng được tạo trong CSDL
        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(10500000, $order->subtotal); // 3 * 3.500.000đ
        $this->assertEquals(1050000, $order->discount); // 10% của 10.500.000đ
        $this->assertEquals(9450000, $order->total); // 10.500.000 - 1.050.000đ

        // 4. Kiểm tra TỒN KHO đã được trừ: 10 - 3 = 7
        $this->assertEquals(7, $this->product->fresh()->stock_quantity);

        // 5. Kiểm tra LƯỢT DÙNG COUPON tăng lên 1
        $this->assertEquals(1, $this->coupon->fresh()->used_count);

        // 6. Kiểm tra GIỎ HÀNG đã được dọn sạch
        $this->assertEmpty(session('cart'));

        // 7. Chuyển hướng sang trang đặt hàng thành công
        $checkoutRes->assertRedirect(route('checkout.success', $order->code));
    }

    public function test_cancelling_order_restores_stock_and_releases_coupon(): void
    {
        // Khách mua 4 sản phẩm
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 4,
        ]);

        $this->actingAs($this->customer)->get(route('checkout.index'));
        $token2 = session('checkout_token');

        $this->actingAs($this->customer)->post(route('checkout.store'), [
            'checkout_token'   => $token2,
            'receiver_name'    => 'Khách Hàng Mua Sắm',
            'phone'            => '0912345678',
            'shipping_address' => 'Số 123 Đường Linh Trung, Phường Linh Trung, TP. Thủ Đức',
            'coupon_code'      => 'GEAR10',
            'payment_method'   => 'cod',
        ]);

        $order = Order::latest('id')->first();
        // Kho bị trừ còn 6
        $this->assertEquals(6, $this->product->fresh()->stock_quantity);
        // Lượt dùng coupon là 1
        $this->assertEquals(1, $this->coupon->fresh()->used_count);

        // Khách hàng hủy đơn (khi đang ở pending)
        $cancelRes = $this->actingAs($this->customer)->post(route('orders.cancel', $order->code));
        $cancelRes->assertRedirect();

        // Trạng thái đơn đổi thành cancelled
        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);

        // Kho được hoàn lại 4 sản phẩm: 6 + 4 = 10
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);

        // Lượt dùng coupon được nhả lại: 1 - 1 = 0
        $this->assertEquals(0, $this->coupon->fresh()->used_count);
    }

    public function test_purchasing_more_than_available_stock_fails_and_preserves_stock(): void
    {
        // Kho hiện có 10, thử đặt 11 sản phẩm
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 10,
        ]);

        $token3 = (string) \Illuminate\Support\Str::uuid();
        session(['checkout_token' => $token3]);

        // Cập nhật session cart giả định vượt kho (11 cái)
        session()->put('cart', [
            $this->product->id => [
                'id'       => $this->product->id,
                'name'     => $this->product->name,
                'price'    => $this->product->price,
                'quantity' => 11,
            ]
        ]);

        $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'checkout_token'   => $token3,
            'receiver_name'    => 'Khách Hàng Mua Sắm',
            'phone'            => '0912345678',
            'shipping_address' => 'Số 123 Đường Linh Trung, Phường Linh Trung, TP. Thủ Đức',
            'payment_method'   => 'cod',
        ]);

        // Báo lỗi không đủ tồn kho
        $response->assertSessionHas('error');

        // Tồn kho trong CSDL vẫn giữ nguyên 10
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);
        $this->assertEquals(0, Order::count());
    }
}
