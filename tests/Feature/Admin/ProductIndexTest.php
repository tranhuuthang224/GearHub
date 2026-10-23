<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    /** Tạo admin một lần mỗi test; gọi nhiều lần vẫn dùng lại cùng một tài khoản. */
    private function asAdmin(): static
    {
        $this->admin ??= User::unguarded(fn () => User::create([
            'name' => 'Admin', 'email' => 'admin@test.vn', 'password' => 'password123',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]));

        return $this->actingAs($this->admin);
    }

    /** Trả về [danh mục cha, danh mục con]. */
    private function categoryPair(string $parentName, string $childName): array
    {
        $parent = Category::create(['name' => $parentName]);
        $child  = Category::create(['name' => $childName, 'parent_id' => $parent->id]);

        return [$parent, $child];
    }

    private function product(string $name, string $sku, Category $category, Brand $brand): Product
    {
        return Product::create([
            'category_id' => $category->id, 'brand_id' => $brand->id,
            'name' => $name, 'sku' => $sku, 'price' => 1000000, 'stock_quantity' => 10,
        ]);
    }

    public function test_customer_cannot_view_product_list(): void
    {
        $customer = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        $this->actingAs($customer)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_search_matches_name_and_sku(): void
    {
        [, $child] = $this->categoryPair('Chuột', 'Chuột gaming');
        $brand = Brand::create(['name' => 'Logitech']);
        $this->product('Chuột alpha', 'MS-001', $child, $brand);
        $this->product('Phím beta', 'KB-002', $child, $brand);

        $this->asAdmin()->get(route('admin.products.index', ['q' => 'MS-001']))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->pluck('sku')->all() === ['MS-001']);

        $this->asAdmin()->get(route('admin.products.index', ['q' => 'beta']))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->pluck('sku')->all() === ['KB-002']);
    }

    public function test_parent_category_filter_includes_child_categories(): void
    {
        [$mouse, $mouseGaming] = $this->categoryPair('Chuột', 'Chuột gaming');
        [, $headsetGaming]     = $this->categoryPair('Tai nghe', 'Tai nghe gaming');
        $brand = Brand::create(['name' => 'Razer']);
        $this->product('Chuột X', 'MS-X', $mouseGaming, $brand);
        $this->product('Tai nghe Y', 'HS-Y', $headsetGaming, $brand);

        $this->asAdmin()->get(route('admin.products.index', ['category' => $mouse->id]))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->pluck('sku')->all() === ['MS-X']);
    }

    public function test_brand_filter(): void
    {
        [, $child] = $this->categoryPair('Chuột', 'Chuột gaming');
        $logitech = Brand::create(['name' => 'Logitech']);
        $razer    = Brand::create(['name' => 'Razer']);
        $this->product('Chuột L', 'MS-L', $child, $logitech);
        $this->product('Chuột R', 'MS-R', $child, $razer);

        $this->asAdmin()->get(route('admin.products.index', ['brand' => $razer->id]))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->pluck('sku')->all() === ['MS-R']);
    }

    public function test_list_is_paginated_ten_per_page(): void
    {
        [, $child] = $this->categoryPair('Chuột', 'Chuột gaming');
        $brand = Brand::create(['name' => 'Logitech']);

        for ($i = 1; $i <= 12; $i++) {
            $this->product("Sản phẩm {$i}", "SKU-{$i}", $child, $brand);
        }

        $this->asAdmin()->get(route('admin.products.index'))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->count() === 10 && $p->total() === 12);

        $this->asAdmin()->get(route('admin.products.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->count() === 2);
    }
}