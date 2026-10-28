<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTrashTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $parent         = Category::create(['name' => 'Chuột']);
        $this->category = Category::create(['name' => 'Chuột gaming', 'parent_id' => $parent->id]);
        $this->brand    = Brand::create(['name' => 'Logitech']);

        $this->admin = User::unguarded(fn () => User::create([
            'name' => 'Admin', 'email' => 'admin@test.vn', 'password' => 'password123',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]));
    }

    private function simpleProduct(string $sku): Product
    {
        return Product::create([
            'category_id' => $this->category->id, 'brand_id' => $this->brand->id,
            'name' => "Sản phẩm {$sku}", 'sku' => $sku, 'price' => 1000000, 'stock_quantity' => 5,
        ]);
    }

    /** Sản phẩm có 1 ảnh đại diện và 2 ảnh chi tiết (tệp thật trong disk giả). */
    private function productWithFiles(string $sku = 'TR-001'): Product
    {
        $product = $this->simpleProduct($sku);
        $dir     = "products/{$product->id}";

        Storage::disk('public')->put("{$dir}/thumb.png", 'x');
        $product->update(['thumbnail' => "{$dir}/thumb.png"]);

        foreach ([1, 2] as $i) {
            Storage::disk('public')->put("{$dir}/img{$i}.png", 'x');
            $product->images()->create(['path' => "{$dir}/img{$i}.png", 'sort_order' => $i]);
        }

        return $product->fresh('images');
    }

    private function trashed(string $sku = 'TR-001'): Product
    {
        $product = $this->productWithFiles($sku);
        $product->delete();

        return $product;
    }

    private function forceDelete(Product $product)
    {
        return $this->actingAs($this->admin)->delete(route('admin.trash.destroy', $product));
    }

    public function test_customer_cannot_use_the_trash(): void
    {
        $product  = $this->trashed();
        $customer = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        $this->actingAs($customer)->get(route('admin.trash.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.trash.restore', $product))->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.trash.destroy', $product))->assertForbidden();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_index_lists_only_trashed_products(): void
    {
        $this->productWithFiles('LIVE-001');
        $this->trashed('TR-001');

        $this->actingAs($this->admin)->get(route('admin.trash.index'))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->pluck('sku')->all() === ['TR-001']);
    }

    public function test_restore_brings_the_product_back(): void
    {
        $product = $this->trashed();

        $this->actingAs($this->admin)->post(route('admin.trash.restore', $product))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
        $this->assertSame(1, Product::count());
    }

    public function test_restore_of_a_live_product_is_not_found(): void
    {
        $product = $this->productWithFiles();

        $this->actingAs($this->admin)->post(route('admin.trash.restore', $product))->assertNotFound();
    }

    public function test_permanent_delete_removes_record_images_and_files(): void
    {
        $product = $this->trashed();
        $dir     = "products/{$product->id}";
        $paths   = array_merge([$product->thumbnail], $product->images->pluck('path')->all());

        $this->forceDelete($product)->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseCount('product_images', 0);

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
        Storage::disk('public')->assertMissing($dir);
    }

    public function test_permanent_delete_of_a_live_product_is_not_found(): void
    {
        $product = $this->productWithFiles();

        $this->forceDelete($product)->assertNotFound();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_in_an_order_cannot_be_deleted_permanently(): void
    {
        $product = $this->productWithFiles();
        $buyer   = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        $order = Order::create([
            'code' => 'GH-0001', 'user_id' => $buyer->id, 'receiver_name' => 'A',
            'phone' => '0900000000', 'shipping_address' => 'HCM',
            'subtotal' => 1000000, 'total' => 1000000,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'unit_price' => 1000000, 'quantity' => 1,
        ]);

        $product->delete();

        $this->forceDelete($product)->assertSessionHas('error');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        Storage::disk('public')->assertExists($product->thumbnail);
    }

    public function test_brand_and_category_become_deletable_after_permanent_delete(): void
    {
        $product = $this->trashed();

        // Còn sản phẩm trong Thùng rác: bị chặn
        $this->actingAs($this->admin)->delete(route('admin.brands.destroy', $this->brand))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('brands', ['id' => $this->brand->id]);

        $this->forceDelete($product);

        $this->actingAs($this->admin)->delete(route('admin.brands.destroy', $this->brand));
        $this->assertDatabaseMissing('brands', ['id' => $this->brand->id]);

        $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $this->category));
        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
    }

    public function test_empty_last_page_redirects_to_last_available_page(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->simpleProduct("P-{$i}")->delete();
        }

        // Sắp xếp mới xóa trước, id lớn trước: sản phẩm id nhỏ nhất nằm một mình ở trang 2
        $oldest = Product::onlyTrashed()->orderBy('id')->first();
        $this->forceDelete($oldest);

        $this->actingAs($this->admin)->get(route('admin.trash.index', ['page' => 2]))
            ->assertRedirect(route('admin.trash.index', ['page' => 1]));
    }
}