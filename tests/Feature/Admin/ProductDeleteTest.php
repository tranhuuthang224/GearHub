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

class ProductDeleteTest extends TestCase
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

    private function simpleProduct(string $sku, string $name = 'Sản phẩm cũ'): Product
    {
        return Product::create([
            'category_id' => $this->category->id, 'brand_id' => $this->brand->id,
            'name' => $name, 'sku' => $sku, 'price' => 1000000, 'stock_quantity' => 5,
        ]);
    }

    /** Sản phẩm có 1 ảnh đại diện và 2 ảnh chi tiết (tệp thật trong disk giả). */
    private function productWithFiles(string $sku = 'DEL-001'): Product
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

    private function destroy(Product $product)
    {
        return $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));
    }

    public function test_customer_cannot_delete_product(): void
    {
        $product  = $this->productWithFiles();
        $customer = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        $this->actingAs($customer)
            ->delete(route('admin.products.destroy', $product))
            ->assertForbidden();

        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_delete_is_soft_and_keeps_images_and_files(): void
    {
        $product = $this->productWithFiles();
        $listUrl = route('admin.products.index', ['page' => 2]);

        // back() đưa quản trị viên về đúng trang đang xem
        $this->actingAs($this->admin)->from($listUrl)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect($listUrl)
            ->assertSessionHas('success');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertDatabaseCount('product_images', 2);

        Storage::disk('public')->assertExists($product->thumbnail);
        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_deleted_product_leaves_the_list_and_cannot_be_edited(): void
    {
        $product = $this->productWithFiles();

        $this->destroy($product);

        $this->actingAs($this->admin)->get(route('admin.products.index'))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->total() === 0);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $product))
            ->assertNotFound();
    }

    public function test_empty_last_page_redirects_to_last_available_page(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->simpleProduct("P-{$i}", "SP {$i}");
        }

        // Danh sách xếp mới nhất trước, nên sản phẩm cũ nhất nằm một mình ở trang 2
        $oldest = Product::orderBy('id')->first();
        $this->destroy($oldest);

        $this->actingAs($this->admin)->get(route('admin.products.index', ['page' => 2]))
            ->assertRedirect(route('admin.products.index', ['page' => 1]));
    }

    public function test_brand_and_category_stay_protected_while_a_trashed_product_remains(): void
    {
        $product = $this->productWithFiles();
        $this->destroy($product);

        $this->actingAs($this->admin)
            ->delete(route('admin.brands.destroy', $this->brand))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('brands', ['id' => $this->brand->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $this->category))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_order_history_still_reads_a_deleted_product(): void
    {
        $product = $this->productWithFiles();
        $buyer   = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        $order = Order::create([
            'code' => 'GH-0001', 'user_id' => $buyer->id, 'receiver_name' => 'A',
            'phone' => '0900000000', 'shipping_address' => 'HCM',
            'subtotal' => 1000000, 'total' => 1000000,
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'unit_price' => 1000000, 'quantity' => 1,
        ]);

        $this->destroy($product);

        $this->assertSame('Sản phẩm cũ', $item->fresh()->product->name);
    }
}