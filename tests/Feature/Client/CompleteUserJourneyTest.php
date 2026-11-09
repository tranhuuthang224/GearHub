<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CommunityPost;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompleteUserJourneyTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Category $keyboardCategory;
    private Category $mouseCategory;
    private Brand $aulaBrand;
    private Brand $keychronBrand;
    private Brand $razerBrand;
    private Product $keyboard1;
    private Product $keyboard2;
    private Product $mouseGaming;
    private Coupon $coupon;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo tài khoản khách hàng thực tế
        $this->customer = User::create([
            'name'     => 'Nguyễn Văn An',
            'email'    => 'nguyenvanan@gearhub.vn',
            'password' => bcrypt('password123'),
            'phone'    => '0912345678',
        ]);

        // 2. Tạo danh mục sản phẩm
        $this->keyboardCategory = Category::create([
            'name'      => 'Bàn phím cơ',
            'slug'      => 'ban-phim-co',
            'parent_id' => null,
        ]);

        $this->mouseCategory = Category::create([
            'name'      => 'Chuột gaming',
            'slug'      => 'chuot-gaming',
            'parent_id' => null,
        ]);

        // 3. Tạo các thương hiệu
        $this->aulaBrand = Brand::create(['name' => 'Aula', 'slug' => 'aula']);
        $this->keychronBrand = Brand::create(['name' => 'Keychron', 'slug' => 'keychron']);
        $this->razerBrand = Brand::create(['name' => 'Razer', 'slug' => 'razer']);

        // 4. Tạo 2 bàn phím phục vụ việc so sánh specs
        $this->keyboard1 = Product::create([
            'category_id'    => $this->keyboardCategory->id,
            'brand_id'       => $this->aulaBrand->id,
            'name'           => 'Bàn phím cơ Aula F75 Pro Wireless',
            'slug'           => 'ban-phim-co-aula-f75-pro-wireless',
            'sku'            => 'KB-AULA-F75P',
            'price'          => 1290000,
            'sale_price'     => 990000,
            'stock_quantity' => 10,
            'specs'          => [
                'Switch'  => 'Reaper Linear Switch',
                'Kết nối' => 'Wireless 2.4G / Bluetooth / Type-C',
                'Layout'  => '75% gọn gàng',
                'LED'     => 'RGB 16.8 triệu màu',
            ],
            'is_active'      => true,
        ]);

        $this->keyboard2 = Product::create([
            'category_id'    => $this->keyboardCategory->id,
            'brand_id'       => $this->keychronBrand->id,
            'name'           => 'Bàn phím cơ Keychron Q1 Pro',
            'slug'           => 'ban-phim-co-keychron-q1-pro',
            'sku'            => 'KB-KC-Q1P',
            'price'          => 4290000,
            'sale_price'     => null,
            'stock_quantity' => 8,
            'specs'          => [
                'Switch'  => 'Keychron K Pro Red',
                'Kết nối' => 'Bluetooth 5.1 / Type-C',
                'Layout'  => '75% Knob Nhôm nguyên khối',
                'LED'     => 'RGB mạch xuôi',
            ],
            'is_active'      => true,
        ]);

        // 5. Tạo 1 chuột gaming phục vụ Quiz thông minh
        $this->mouseGaming = Product::create([
            'category_id'    => $this->mouseCategory->id,
            'brand_id'       => $this->razerBrand->id,
            'name'           => 'Chuột gaming Razer Viper V3 HyperSpeed',
            'slug'           => 'chuot-gaming-razer-viper-v3-hyperspeed',
            'sku'            => 'MS-RZ-V3H',
            'price'          => 1690000,
            'sale_price'     => null,
            'stock_quantity' => 15,
            'specs'          => [
                'Cảm biến' => 'Focus Pro 30K Optical Sensor',
                'Kết nối'  => 'Không dây Wireless HyperSpeed',
                'LED'      => 'RGB Chroma',
                'Trọng lượng' => '59g siêu nhẹ',
            ],
            'is_active'      => true,
        ]);

        // 6. Tạo mã giảm giá 10%
        $this->coupon = Coupon::unguarded(fn () => Coupon::create([
            'code'        => 'GEAR10',
            'type'        => 'percent',
            'value'       => 10,
            'min_order'   => 500000,
            'usage_limit' => 100,
            'used_count'  => 0,
            'starts_at'   => null,
            'expires_at'  => null,
            'is_active'   => true,
        ]));
    }

    /**
     * Test toàn bộ hành trình khách hàng xuyên suốt 10 bước:
     * Vào web -> Lọc sản phẩm -> So sánh 2 bàn phím -> Làm Quiz tìm chuột -> Bỏ giỏ
     * -> Áp mã giảm giá -> Checkout COD -> Xem lịch sử đơn -> Hủy đơn thử nghiệm
     * -> Đăng bài khoe góc máy & tag bàn phím vừa mua.
     */
    public function test_complete_customer_journey_from_browsing_to_cancellation_and_community_posting(): void
    {
        // -------------------------------------------------------------
        // BƯỚC 1: Vào web (Trang chủ) và Danh mục Shop
        // -------------------------------------------------------------
        $homeRes = $this->get(route('home'));
        $homeRes->assertOk();
        $homeRes->assertSee('GearHub');

        $shopRes = $this->get(route('shop.index'));
        $shopRes->assertOk();
        $shopRes->assertSee('Bàn phím cơ Aula F75 Pro Wireless');
        $shopRes->assertSee('Bàn phím cơ Keychron Q1 Pro');
        $shopRes->assertSee('Chuột gaming Razer Viper V3 HyperSpeed');

        // -------------------------------------------------------------
        // BƯỚC 2: Lọc sản phẩm theo danh mục "Bàn phím cơ"
        // -------------------------------------------------------------
        $filterRes = $this->get(route('shop.index', ['categories' => ['Bàn phím cơ']]));
        $filterRes->assertOk();
        $filterRes->assertSee('Bàn phím cơ Aula F75 Pro Wireless');
        $filterRes->assertSee('Bàn phím cơ Keychron Q1 Pro');
        $filterRes->assertDontSee('Chuột gaming Razer Viper V3 HyperSpeed');

        // -------------------------------------------------------------
        // BƯỚC 3: So sánh 2 bàn phím cơ (Specs Matrix & Different Highlighting)
        // -------------------------------------------------------------
        // Thêm bàn phím 1 vào so sánh
        $compareToggle1 = $this->postJson(route('compare.toggle', $this->keyboard1->id));
        $compareToggle1->assertOk();
        $compareToggle1->assertJson([
            'success' => true,
            'action'  => 'added',
            'count'   => 1,
        ]);

        // Thêm bàn phím 2 vào so sánh
        $compareToggle2 = $this->postJson(route('compare.toggle', $this->keyboard2->id));
        $compareToggle2->assertOk();
        $compareToggle2->assertJson([
            'success' => true,
            'action'  => 'added',
            'count'   => 2,
        ]);

        // Mở trang so sánh /compare
        $comparePageRes = $this->get(route('compare.index'));
        $comparePageRes->assertOk();
        $comparePageRes->assertSee('Bàn phím cơ Aula F75 Pro Wireless');
        $comparePageRes->assertSee('Bàn phím cơ Keychron Q1 Pro');
        $comparePageRes->assertSee('Reaper Linear Switch');
        $comparePageRes->assertSee('Keychron K Pro Red');
        $comparePageRes->assertSee('Đặc tả thông số kỹ thuật (Specs Matrix)');

        // -------------------------------------------------------------
        // BƯỚC 4: Làm Quiz Smart Gear Finder tìm chuột gaming phù hợp
        // -------------------------------------------------------------
        $quizSubmitRes = $this->postJson(route('quiz.submit'), [
            'purpose'     => 'gaming',
            'device_type' => 'chuot-gaming',
            'budget'      => '1m_to_3m',
            'features'    => ['wireless', 'rgb'],
        ]);
        $quizSubmitRes->assertOk();
        $quizSubmitRes->assertJson([
            'success' => true,
        ]);
        $quizSubmitRes->assertJsonFragment([
            'name' => 'Chuột gaming Razer Viper V3 HyperSpeed',
        ]);

        $quizLog = \App\Models\QuizLog::latest('id')->first();
        $this->assertNotNull($quizLog);
        $this->assertEquals('chuot-gaming', $quizLog->answers['device_type'] ?? null);
        $this->assertContains($this->mouseGaming->id, $quizLog->recommended_product_ids);

        // -------------------------------------------------------------
        // BƯỚC 5: Thêm bàn phím đã chọn và chuột vừa tìm qua Quiz vào giỏ hàng
        // -------------------------------------------------------------
        // Khách hàng đăng nhập thực hiện mua
        $addKeyboardRes = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->keyboard1->id,
            'quantity'   => 1,
        ]);
        $addKeyboardRes->assertRedirect();

        $addMouseRes = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->mouseGaming->id,
            'quantity'   => 1,
        ]);
        $addMouseRes->assertRedirect();

        $this->assertCount(2, session('cart'));

        // Kiểm tra trang giỏ hàng hiển thị cả 2 sản phẩm
        $cartPageRes = $this->actingAs($this->customer)->get(route('cart.index'));
        $cartPageRes->assertOk();
        $cartPageRes->assertSee('Bàn phím cơ Aula F75 Pro Wireless');
        $cartPageRes->assertSee('Chuột gaming Razer Viper V3 HyperSpeed');

        // -------------------------------------------------------------
        // BƯỚC 6: Áp dụng mã giảm giá GEAR10 tại Checkout
        // -------------------------------------------------------------
        // Subtotal = 990.000 (sale) + 1.690.000 = 2.680.000đ
        $subtotal = 990000 + 1690000;
        $expectedDiscount = (int) ($subtotal * 0.1); // 268.000đ

        $couponCheckRes = $this->actingAs($this->customer)->postJson(route('checkout.coupon'), [
            'code'     => 'GEAR10',
            'subtotal' => $subtotal,
        ]);
        $couponCheckRes->assertOk();
        $couponCheckRes->assertJson([
            'valid'    => true,
            'code'     => 'GEAR10',
            'discount' => $expectedDiscount,
        ]);

        // -------------------------------------------------------------
        // BƯỚC 7: Đặt hàng thanh toán COD (Trừ tồn kho, sinh mã đơn GH-...)
        // -------------------------------------------------------------
        $stockBeforeKeyboard = $this->keyboard1->stock_quantity; // 10
        $stockBeforeMouse = $this->mouseGaming->stock_quantity;   // 15

        $checkoutSubmitRes = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'receiver_name'    => 'Nguyễn Văn An',
            'phone'            => '0912345678',
            'shipping_address' => '181 Quốc lộ 1K, Phường Đông Hòa, TP. Dĩ An, Tỉnh Bình Dương',
            'note'             => 'Vui lòng gọi trước khi giao 15 phút',
            'payment_method'   => 'cod',
            'coupon_code'      => 'GEAR10',
        ]);

        // Đơn hàng tạo thành công trong CSDL
        $order = Order::where('user_id', $this->customer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals($expectedDiscount, $order->discount);
        $this->assertStringStartsWith('GH-', $order->code);

        // Kho bị trừ chính xác 1 đơn vị mỗi sản phẩm
        $this->assertEquals($stockBeforeKeyboard - 1, $this->keyboard1->fresh()->stock_quantity);
        $this->assertEquals($stockBeforeMouse - 1, $this->mouseGaming->fresh()->stock_quantity);

        // Giỏ hàng được xóa sạch sau khi checkout
        $this->assertEmpty(session('cart'));

        // Chuyển hướng đến màn hình thành công
        $checkoutSubmitRes->assertRedirect(route('checkout.success', $order->code));

        // -------------------------------------------------------------
        // BƯỚC 8: Xem lịch sử đơn hàng và chi tiết đơn hàng
        // -------------------------------------------------------------
        $ordersListRes = $this->actingAs($this->customer)->get(route('orders.index'));
        $ordersListRes->assertOk();
        $ordersListRes->assertSee($order->code);

        $orderDetailRes = $this->actingAs($this->customer)->get(route('orders.show', $order->code));
        $orderDetailRes->assertOk();
        $orderDetailRes->assertSee($order->code);
        $orderDetailRes->assertSee('Nguyễn Văn An');
        $orderDetailRes->assertSee('181 Quốc lộ 1K, Phường Đông Hòa, TP. Dĩ An, Tỉnh Bình Dương');
        $orderDetailRes->assertSee('Bàn phím cơ Aula F75 Pro Wireless');
        $orderDetailRes->assertSee('Chuột gaming Razer Viper V3 HyperSpeed');
        $orderDetailRes->assertSee($order->statusLabel());

        // -------------------------------------------------------------
        // BƯỚC 9: Hủy đơn hàng thử nghiệm và kiểm tra kho được hoàn lại
        // -------------------------------------------------------------
        $cancelRes = $this->actingAs($this->customer)->post(route('orders.cancel', $order->code));
        $cancelRes->assertRedirect();
        $cancelRes->assertSessionHas('success');

        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);

        // Tồn kho được hoàn trả nguyên vẹn về số lượng ban đầu
        $this->assertEquals($stockBeforeKeyboard, $this->keyboard1->fresh()->stock_quantity);
        $this->assertEquals($stockBeforeMouse, $this->mouseGaming->fresh()->stock_quantity);

        // -------------------------------------------------------------
        // BƯỚC 10: Đăng bài khoe góc máy & Gắn thẻ (Tag) bàn phím vừa mua
        // -------------------------------------------------------------
        Storage::fake('public');
        $setupImage = UploadedFile::fake()->image('goc-may-chill.jpg', 1920, 1080);

        $communityStoreRes = $this->actingAs($this->customer)->post(route('community.store'), [
            'title'       => 'Góc làm việc Tone Gỗ cùng Aula F75 Pro siêu chill',
            'style'       => CommunityPost::STYLE_WOOD,
            'description' => 'Góc setup mộc mạc với phím cơ gõ âm trầm đục cực thư giãn sau giờ làm việc.',
            'image'       => $setupImage,
            'product_ids' => [$this->keyboard1->id],
        ]);

        $post = CommunityPost::where('title', 'Góc làm việc Tone Gỗ cùng Aula F75 Pro siêu chill')->first();
        $this->assertNotNull($post);
        $communityStoreRes->assertRedirect(route('community.show', $post->slug));

        // Kiểm tra ảnh đã được lưu vào Storage public
        Storage::disk('public')->assertExists($post->image);

        // Kiểm tra sản phẩm bàn phím đã được gắn thẻ vào bài viết
        $this->assertTrue($post->products->contains($this->keyboard1->id));

        // Xem bài viết chi tiết trên Community
        $postDetailRes = $this->actingAs($this->customer)->get(route('community.show', $post->slug));
        $postDetailRes->assertOk();
        $postDetailRes->assertSee('Góc làm việc Tone Gỗ cùng Aula F75 Pro siêu chill');
        $postDetailRes->assertSee('Bàn phím cơ Aula F75 Pro Wireless');
        $postDetailRes->assertSee('Gear có trong ảnh');

        // Thả tim bài viết
        $likeRes = $this->actingAs($this->customer)->postJson(route('community.like', $post->slug));
        $likeRes->assertOk();
        $likeRes->assertJson([
            'success'     => true,
            'liked'       => true,
            'likes_count' => 1,
        ]);

        // Bình luận vào bài viết
        $commentRes = $this->actingAs($this->customer)->postJson(route('community.comment', $post->slug), [
            'content' => 'Góc máy đẹp và phím gõ êm lắm nha mọi người!',
        ]);
        $commentRes->assertOk();
        $commentRes->assertJson([
            'success'        => true,
            'comments_count' => 1,
        ]);

        $this->assertDatabaseHas('post_comments', [
            'community_post_id' => $post->id,
            'user_id'           => $this->customer->id,
            'content'           => 'Góc máy đẹp và phím gõ êm lắm nha mọi người!',
        ]);
    }
}
