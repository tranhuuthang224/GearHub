<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Brand;


class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setupProductAndUser(): array
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'Chuột', 'slug' => 'chuot']);
        $brand = Brand::create(['name' => 'Logitech', 'slug' => 'logitech']);

        $product = Product::create([
            'category_id'    => $cat->id,
            'brand_id'       => $brand->id,
            'name'           => 'Chuột Gaming G102',
            'slug'           => 'chuot-gaming-g102',
            'sku'            => 'M-G102',
            'price'          => 400000,
            'stock_quantity' => 20,
            'is_active'      => true,
        ]);

        return [$user, $product];
    }

    public function test_guest_cannot_submit_review(): void
    {
        [$user, $product] = $this->setupProductAndUser();

        $response = $this->postJson(route('products.reviews.store', $product->slug), [
            'rating'  => 5,
            'comment' => 'Sản phẩm rất tốt!',
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_user_without_completed_order_cannot_review(): void
    {
        [$user, $product] = $this->setupProductAndUser();

        $response = $this->actingAs($user)->post(route('products.reviews.store', $product->slug), [
            'rating'  => 5,
            'comment' => 'Sản phẩm xịn xò lắm nhé',
        ]);

        // Bị từ chối vì chưa mua hàng thành công
        $this->assertDatabaseMissing('reviews', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_with_completed_order_can_submit_review(): void
    {
        [$user, $product] = $this->setupProductAndUser();

        // Tạo đơn hàng completed chứa sản phẩm
        $order = Order::create([
            'code'             => 'ORD-TEST-01',
            'user_id'          => $user->id,
            'receiver_name'    => $user->name,
            'phone'            => '0987654321',
            'shipping_address' => '123 Test Street',
            'payment_method'   => 'cod',
            'status'           => Order::STATUS_COMPLETED,
            'subtotal'         => 400000,
            'total'            => 400000,
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'unit_price'   => 400000,
            'quantity'     => 1,
        ]);

        $response = $this->actingAs($user)->post(route('products.reviews.store', $product->slug), [
            'rating'  => 5,
            'comment' => 'Chuột bấm rất nảy, giao hàng nhanh!',
        ]);

        $this->assertDatabaseHas('reviews', [
            'user_id'              => $user->id,
            'product_id'           => $product->id,
            'rating'               => 5,
            'is_verified_purchase' => true,
        ]);
    }
}