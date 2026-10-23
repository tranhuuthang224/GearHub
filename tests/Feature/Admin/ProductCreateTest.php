<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
{
    return $this->makeUser('admin@test.vn', User::ROLE_ADMIN);
}

private function customer(): User
{
    return $this->makeUser('khach@test.vn', User::ROLE_CUSTOMER);
}

private function makeUser(string $email, string $role): User
{
    // role và is_active không nằm trong $fillable nên phải bỏ qua bảo vệ có chủ đích
    return User::unguarded(fn () => User::create([
        'name' => 'Test', 'email' => $email, 'password' => 'password123',
        'role' => $role, 'is_active' => true,
    ]));
}

    private function payload(array $override = []): array
    {
        $parent = Category::create(['name' => 'Chuột']);
        $child  = Category::create(['name' => 'Chuột gaming', 'parent_id' => $parent->id]);
        $brand  = Brand::create(['name' => 'Logitech']);

        return array_merge([
            'category_id'    => $child->id,
            'brand_id'       => $brand->id,
            'name'           => 'Bàn phím test',
            'sku'            => 'TEST-001',
            'price'          => 1000000,
            'sale_price'     => 900000,
            'stock_quantity' => 5,
            'is_active'      => 1,
            'specs'          => [
                ['key' => 'Kết nối', 'value' => 'Bluetooth'],
                ['key' => '', 'value' => ''],
            ],
        ], $override);
    }

    public function test_customer_cannot_open_product_form(): void
{
    $this->actingAs($this->customer())
        ->get(route('admin.products.create'))
        ->assertForbidden();
}

    public function test_admin_can_create_product_with_specs(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->payload())
            ->assertRedirect(route('admin.products.create'));

        $product = Product::firstOrFail();
        $this->assertSame('ban-phim-test', $product->slug);
        $this->assertSame(['Kết nối' => 'Bluetooth'], $product->specs);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $admin   = $this->admin();
        $payload = $this->payload();

        $this->actingAs($admin)->post(route('admin.products.store'), $payload);
        $this->actingAs($admin)->post(route('admin.products.store'), $payload)
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::count());
    }

    public function test_sale_price_must_be_lower_than_price(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->payload(['sale_price' => 1000000]))
            ->assertSessionHasErrors('sale_price');
    }

    public function test_parent_category_is_rejected(): void
    {
        $admin  = $this->admin();
        $parent = Category::create(['name' => 'Tai nghe']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->payload(['category_id' => $parent->id]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_deactivated_admin_is_logged_out(): void
{
    $admin = $this->admin();
    $admin->forceFill(['is_active' => false])->save();

    $this->actingAs($admin->fresh())
        ->get(route('admin.products.create'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
}
}

    