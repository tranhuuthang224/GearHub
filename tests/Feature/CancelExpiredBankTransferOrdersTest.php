<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelExpiredBankTransferOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_bank_transfer_orders_are_cancelled_and_restocked(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'Bàn phím cơ', 'slug' => 'ban-phim-co']);
        $brand = Brand::create(['name' => 'Aula', 'slug' => 'aula']);

        $product = Product::create([
            'category_id'    => $cat->id,
            'brand_id'       => $brand->id,
            'name'           => 'Bàn phím cơ test',
            'slug'           => 'ban-phim-co-test',
            'sku'            => 'TEST-KB',
            'price'          => 1000000,
            'stock_quantity' => 5,
            'is_active'      => true,
        ]);

        // Tạo 1 đơn bank_transfer đã quá hạn 25 giờ
        $expiredOrder = Order::create([
            'code'             => 'GH-TEST-EXP1',
            'user_id'          => $user->id,
            'receiver_name'    => 'Test User',
            'phone'            => '0987654321',
            'shipping_address' => '123 Đường Test, Quận 1',
            'payment_method'   => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'status'           => Order::STATUS_PENDING,
            'subtotal'         => 1000000,
            'discount'         => 0,
            'total'            => 1000000,
        ]);
        $expiredOrder->created_at = now()->subHours(25);
        $expiredOrder->saveQuietly();

        $expiredOrder->items()->create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'unit_price'   => 1000000,
            'quantity'     => 2,
        ]);

        // Tạo 1 đơn bank_transfer mới đặt 2 giờ trước (chưa hết hạn)
        $validOrder = Order::create([
            'code'             => 'GH-TEST-VAL1',
            'user_id'          => $user->id,
            'receiver_name'    => 'Test User 2',
            'phone'            => '0987654322',
            'shipping_address' => '456 Đường Test, Quận 2',
            'payment_method'   => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'status'           => Order::STATUS_PENDING,
            'subtotal'         => 1000000,
            'discount'         => 0,
            'total'            => 1000000,
            'created_at'       => now()->subHours(2),
        ]);

        $validOrder->items()->create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'unit_price'   => 1000000,
            'quantity'     => 1,
        ]);

        // Chạy lệnh artisan
        $this->artisan('orders:cancel-expired-bank-transfers', ['--hours' => 24])
            ->assertSuccessful();

        // Đơn quá hạn bị chuyển sang cancelled và hoàn kho +2
        $this->assertEquals(Order::STATUS_CANCELLED, $expiredOrder->fresh()->status);
        $this->assertEquals(7, $product->fresh()->stock_quantity); // 5 + 2

        // Đơn mới vẫn ở trạng thái pending
        $this->assertEquals(Order::STATUS_PENDING, $validOrder->fresh()->status);
    }
}
