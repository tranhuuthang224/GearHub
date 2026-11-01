<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Category $category;
    private Brand $brand;
    private Product $productUnder500k;
    private Product $productOver500k;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'     => 'Nguyễn Văn A',
            'email'    => 'nguyenvana@gmail.com',
            'password' => bcrypt('password123'),
            'phone'    => '0912345678',
        ]);

        $this->category = Category::create(['name' => 'Tai nghe']);
        $this->brand = Brand::create(['name' => 'HyperX']);

        $this->productUnder500k = Product::create([
            'category_id'    => $this->category->id,
            'brand_id'       => $this->brand->id,
            'name'           => 'Tai nghe Earbud HyperX',
            'sku'            => 'HX-EB-01',
            'price'          => 350000,
            'sale_price'     => null,
            'stock_quantity' => 20,
            'is_active'      => true,
        ]);

        $this->productOver500k = Product::create([
            'category_id'    => $this->category->id,
            'brand_id'       => $this->brand->id,
            'name'           => 'Tai nghe HyperX Cloud II Wireless',
            'sku'            => 'HX-C2W',
            'price'          => 2890000,
            'sale_price'     => null,
            'stock_quantity' => 15,
            'is_active'      => true,
        ]);
    }

    public function test_guest_cannot_access_checkout_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_checkout_redirects_to_cart_if_cart_is_empty(): void
    {
        $response = $this->actingAs($this->customer)->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_index_renders_with_cart_items_and_default_cod(): void
    {
        // Thêm sản phẩm vào giỏ
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productUnder500k->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->customer)->get(route('checkout.index'));

        $response->assertOk();
        $response->assertViewIs('client.checkout.index');
        $response->assertSee('Thanh toán đơn hàng');
        $response->assertSee('Tai nghe Earbud HyperX');
        $response->assertSee('350.000đ');
        // Phí ship dưới 500K là 30.000đ
        $response->assertSee('30.000đ');
        // Phương thức mặc định COD
        $response->assertSee('Thanh toán khi nhận hàng (COD)');
        $response->assertSee('receiver_name', false);
        $response->assertSee('phone', false);
        $response->assertSee('shipping_address', false);
    }

    public function test_checkout_index_shows_free_shipping_for_subtotal_over_500k(): void
    {
        // Thêm sản phẩm trên 500K vào giỏ
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productOver500k->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->customer)->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('Tai nghe HyperX Cloud II Wireless');
        $response->assertSee('Miễn phí (Free ship)');
        $response->assertSee('Đủ điều kiện Free Ship');
    }

    public function test_checkout_store_creates_order_and_redirects_to_success(): void
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productOver500k->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'receiver_name'    => 'Trần Hữu Thắng',
            'phone'            => '0987654321',
            'shipping_address' => 'Khu phố 6, Linh Trung, TP. Thủ Đức, TP. Hồ Chí Minh',
            'note'             => 'Giao giờ hành chính',
            'payment_method'   => 'cod',
        ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('Trần Hữu Thắng', $order->receiver_name);
        $this->assertEquals('0987654321', $order->phone);
        $this->assertEquals('Khu phố 6, Linh Trung, TP. Thủ Đức, TP. Hồ Chí Minh', $order->shipping_address);
        $this->assertEquals('Giao giờ hành chính', $order->note);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        // Kho bị trừ 1 sản phẩm
        $this->assertEquals(14, $this->productOver500k->fresh()->stock_quantity);

        // Giỏ hàng bị xóa sạch sau khi đặt
        $this->assertEmpty(session('cart'));

        // Chuyển hướng sang màn hình thành công
        $response->assertRedirect(route('checkout.success', $order->code));

        // Màn hình thành công hiển thị đúng
        $successResponse = $this->actingAs($this->customer)->get(route('checkout.success', $order->code));
        $successResponse->assertOk();
        $successResponse->assertSee($order->code);
        $successResponse->assertSee('Đặt hàng thành công');
    }

    public function test_checkout_store_validates_required_fields(): void
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productUnder500k->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'receiver_name'    => '',
            'phone'            => '',
            'shipping_address' => '',
        ]);

        $response->assertSessionHasErrors(['receiver_name', 'phone', 'shipping_address']);
        $this->assertEquals(0, Order::count());
    }

    public function test_checkout_store_validates_vietnam_phone_number_format(): void
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productUnder500k->id,
            'quantity'   => 1,
        ]);

        // Sai đầu số hoặc độ dài
        $invalidPhones = ['0123456789', '0281234567', '091234', '1234567890', 'abcdefghij'];

        foreach ($invalidPhones as $phone) {
            $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
                'receiver_name'    => 'Nguyễn Văn Test',
                'phone'            => $phone,
                'shipping_address' => 'Số 123 Đường Số 1, Phường Linh Trung, TP. Thủ Đức',
            ]);

            $response->assertSessionHasErrors('phone');
        }
    }

    public function test_checkout_store_normalizes_phone_with_spaces_or_dashes(): void
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productUnder500k->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'receiver_name'    => 'Nguyễn Văn B',
            'phone'            => '0987 654 321', // Chứa khoảng trắng
            'shipping_address' => 'Số 456 Đường Số 2, Phường Linh Tây, TP. Thủ Đức',
            'payment_method'   => 'cod',
        ]);

        $response->assertRedirect();
        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('0987654321', $order->phone); // Đã được chuẩn hóa
    }

    public function test_checkout_store_validates_minimum_address_length(): void
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productUnder500k->id,
            'quantity'   => 1,
        ]);

        // Địa chỉ quá ngắn (dưới 10 ký tự)
        $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'receiver_name'    => 'Nguyễn Văn C',
            'phone'            => '0912345678',
            'shipping_address' => 'HCM', // Quá ngắn
        ]);

        $response->assertSessionHasErrors('shipping_address');
    }

    public function test_checkout_store_with_coupon_applies_discount_and_records_redemption(): void
    {
        // Tạo Coupon giảm 10%
        $coupon = \App\Models\Coupon::create([
            'code'        => 'GEAR10',
            'type'        => 'percent',
            'value'       => 10,
            'min_order'   => 500000,
            'usage_limit' => 100,
            'used_count'  => 0,
            'is_active'   => true,
        ]);

        // Thêm sản phẩm 2.890.000đ vào giỏ
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productOver500k->id,
            'quantity'   => 1,
        ]);

        // 1. Kiểm tra preview Coupon qua API
        $previewResponse = $this->actingAs($this->customer)->postJson(route('checkout.coupon'), [
            'code'     => 'GEAR10',
            'subtotal' => 2890000,
        ]);

        $previewResponse->assertOk();
        $previewResponse->assertJson([
            'valid'    => true,
            'code'     => 'GEAR10',
            'discount' => 289000,
            'total'    => 2601000,
        ]);

        // 2. Gửi request đặt hàng kèm Coupon
        $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'receiver_name'    => 'Trần Hữu Thắng',
            'phone'            => '0987654321',
            'shipping_address' => 'Khu phố 6, Linh Trung, TP. Thủ Đức, TP. Hồ Chí Minh',
            'coupon_code'      => 'GEAR10',
            'payment_method'   => 'cod',
        ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals($coupon->id, $order->coupon_id);
        $this->assertEquals(2890000, $order->subtotal);
        $this->assertEquals(289000, $order->discount);
        $this->assertEquals(2601000, $order->total);

        // Lượt dùng của coupon tăng lên 1
        $this->assertEquals(1, $coupon->fresh()->used_count);

        // Giỏ hàng bị xóa sạch
        $this->assertEmpty(session('cart'));

        // Chuyển hướng sang màn hình thành công
        $response->assertRedirect(route('checkout.success', $order->code));

        // Màn hình thành công hiển thị mã giảm giá và tổng tiền đã trừ
        $successResponse = $this->actingAs($this->customer)->get(route('checkout.success', $order->code));
        $successResponse->assertOk();
        $successResponse->assertSee('GEAR10');
        $successResponse->assertSee('289.000đ');
        $successResponse->assertSee('2.601.000đ');
    }
}
