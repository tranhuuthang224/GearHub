<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $nonBuyer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'Bàn phím cơ',
            'slug' => 'ban-phim-co',
            'is_active' => true,
        ]);

        $brand = Brand::create([
            'name' => 'Aula',
            'slug' => 'aula',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Bàn phím cơ Aula F75',
            'slug' => 'ban-phim-co-aula-f75',
            'sku' => 'KB-AULA-F75',
            'price' => 1250000,
            'sale_price' => 990000,
            'stock_quantity' => 20,
            'is_active' => true,
            'specs' => ['Layout' => '75%', 'Switch' => 'Reaper Switch'],
            'thumbnail' => 'images/Keyboard/Aula F75.webp',
        ]);

        $this->buyer = User::create([
            'name' => 'Nguyễn Mua Hàng',
            'email' => 'buyer@gearhub.vn',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        $this->nonBuyer = User::create([
            'name' => 'Lê Chưa Mua',
            'email' => 'nonbuyer@gearhub.vn',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        // Tạo đơn hàng hoàn thành cho buyer
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'code' => 'GH-TEST-ORDER-1',
            'receiver_name' => $this->buyer->name,
            'phone' => '0901234567',
            'shipping_address' => 'UIT Thủ Đức',
            'payment_method' => 'cod',
            'status' => Order::STATUS_COMPLETED,
            'subtotal' => 1250000,
            'discount' => 0,
            'total' => 1250000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 1250000,
            'quantity' => 1,
        ]);
    }

    public function test_product_detail_page_renders_reviews_overview_and_distribution(): void
    {
        // Tạo 2 review: 1 cái 5 sao từ buyer, 1 cái 4 sao từ non-buyer
        Review::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'comment' => 'Phím gõ cực kỳ êm ái, switch mượt mà!',
        ]);

        Review::create([
            'user_id' => $this->nonBuyer->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'comment' => 'Sản phẩm đẹp nhưng giao hàng hơi chậm.',
        ]);

        $response = $this->get(route('shop.show', $this->product->slug));

        $response->assertOk();
        $response->assertSee('4.5'); // Average of 5 and 4
        $response->assertSee('đánh giá thực tế từ khách hàng');
        $response->assertSee('Phím gõ cực kỳ êm ái');
        $response->assertSee('Sản phẩm đẹp nhưng giao hàng hơi chậm.');

        // Buyer có nhãn "Đã mua hàng tại GearHub"
        $response->assertSee('Đã mua hàng tại GearHub');
    }

    public function test_authenticated_user_can_submit_review(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('shop.reviews.store', $this->product->slug), [
                'rating' => 5,
                'comment' => 'Cực kỳ hài lòng về bàn phím cơ này, 10 điểm!',
            ]);

        $response->assertRedirect(route('shop.show', $this->product->slug) . '#reviews');
        $response->assertSessionHas('review_success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'comment' => 'Cực kỳ hài lòng về bàn phím cơ này, 10 điểm!',
        ]);
    }

    public function test_authenticated_user_can_submit_review_with_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('setup-desk.jpg', 800, 600);

        $response = $this->actingAs($this->buyer)
            ->post(route('shop.reviews.store', $this->product->slug), [
                'rating' => 5,
                'comment' => 'Ảnh góc setup thực tế với phím Aula F75 cực đẹp!',
                'photos' => [$photo],
            ]);

        $response->assertRedirect(route('shop.show', $this->product->slug) . '#reviews');

        $review = Review::where('user_id', $this->buyer->id)->where('product_id', $this->product->id)->first();
        $this->assertNotNull($review);
        $this->assertNotEmpty($review->photos);
        Storage::disk('public')->assertExists($review->photos[0]);
    }

    public function test_guest_cannot_submit_review(): void
    {
        $response = $this->post(route('shop.reviews.store', $this->product->slug), [
            'rating' => 5,
            'comment' => 'Nhận xét từ khách vãng lai.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('reviews', [
            'comment' => 'Nhận xét từ khách vãng lai.',
        ]);
    }

    public function test_review_submission_validates_inputs(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('shop.reviews.store', $this->product->slug), [
                'rating' => 6, // Invalid > 5
                'comment' => 'abc', // Invalid < 5 chars
            ]);

        $response->assertSessionHasErrors(['rating', 'comment']);
    }

    public function test_user_can_delete_their_own_review(): void
    {
        Review::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'comment' => 'Đánh giá sắp bị xóa.',
        ]);

        $response = $this->actingAs($this->buyer)
            ->delete(route('shop.reviews.destroy', $this->product->slug));

        $response->assertRedirect(route('shop.show', $this->product->slug) . '#reviews');
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
    }
}
