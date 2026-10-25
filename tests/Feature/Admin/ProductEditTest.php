<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductEditTest extends TestCase
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

    /** Sản phẩm có sẵn 1 ảnh đại diện và 2 ảnh chi tiết (đều là tệp thật trong disk giả). */
    private function productWithFiles(string $sku = 'OLD-001'): Product
    {
        $product = Product::create([
            'category_id' => $this->category->id, 'brand_id' => $this->brand->id,
            'name' => 'Sản phẩm cũ', 'sku' => $sku, 'price' => 1000000, 'stock_quantity' => 5,
            'specs' => ['Kết nối' => 'Bluetooth'],
        ]);

        $dir = "products/{$product->id}";

        Storage::disk('public')->put("{$dir}/thumb.png", 'x');
        $product->update(['thumbnail' => "{$dir}/thumb.png"]);

        foreach ([1, 2] as $i) {
            Storage::disk('public')->put("{$dir}/img{$i}.png", 'x');
            $product->images()->create(['path' => "{$dir}/img{$i}.png", 'sort_order' => $i]);
        }

        return $product->fresh('images');
    }

    private function payload(Product $product, array $override = []): array
    {
        return array_merge([
            'category_id'    => $product->category_id,
            'brand_id'       => $product->brand_id,
            'name'           => $product->name,
            'sku'            => $product->sku,
            'price'          => $product->price,
            'stock_quantity' => $product->stock_quantity,
            'is_active'      => 1,
            'specs'          => [['key' => 'Kết nối', 'value' => 'Bluetooth']],
        ], $override);
    }

    private function png(string $name): UploadedFile
    {
        // PNG 1x1, dùng createWithContent để không phụ thuộc thư viện GD
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function update(Product $product, array $payload)
    {
        return $this->actingAs($this->admin)->put(route('admin.products.update', $product), $payload);
    }

    public function test_customer_cannot_edit_or_update(): void
    {
        $product  = $this->productWithFiles();
        $customer = User::create(['name' => 'Khach', 'email' => 'k@test.vn', 'password' => 'password123']);

        $this->actingAs($customer)->get(route('admin.products.edit', $product))->assertForbidden();
        $this->actingAs($customer)->put(route('admin.products.update', $product), $this->payload($product))->assertForbidden();
    }

    public function test_edit_form_loads_existing_data(): void
    {
        $product = $this->productWithFiles();

        $this->actingAs($this->admin)->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Sản phẩm cũ')
            ->assertSee('OLD-001')
            ->assertSee('Bluetooth')
            ->assertViewHas('product', fn ($p) => $p->is($product) && $p->images->count() === 2);
    }

    public function test_admin_can_update_fields_and_keep_own_sku(): void
    {
        $product = $this->productWithFiles();

        $this->update($product, $this->payload($product, [
            'name' => 'Tên mới', 'price' => 2000000, 'sale_price' => 1500000, 'stock_quantity' => 9,
            'specs' => [['key' => 'Pin', 'value' => '70 giờ']],
        ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.products.index'));

        $fresh = $product->fresh();
        $this->assertSame('Tên mới', $fresh->name);
        $this->assertSame(2000000, $fresh->price);
        $this->assertSame(1500000, $fresh->sale_price);
        $this->assertSame(9, $fresh->stock_quantity);
        $this->assertSame(['Pin' => '70 giờ'], $fresh->specs);
        $this->assertSame('OLD-001', $fresh->sku);
    }

    public function test_sku_of_another_product_is_rejected(): void
    {
        $product = $this->productWithFiles('OLD-001');
        $other   = $this->productWithFiles('OLD-002');

        $this->update($product, $this->payload($product, ['sku' => $other->sku]))
            ->assertSessionHasErrors('sku');

        $this->assertSame('OLD-001', $product->fresh()->sku);
    }

    public function test_new_thumbnail_replaces_and_deletes_the_old_file(): void
    {
        $product  = $this->productWithFiles();
        $oldThumb = $product->thumbnail;

        $this->update($product, $this->payload($product, ['thumbnail' => $this->png('new.png')]))
            ->assertSessionHasNoErrors();

        $fresh = $product->fresh();
        $this->assertNotSame($oldThumb, $fresh->thumbnail);
        $this->assertStringStartsWith("products/{$product->id}/", $fresh->thumbnail);
        Storage::disk('public')->assertExists($fresh->thumbnail);
        Storage::disk('public')->assertMissing($oldThumb);
    }

    public function test_can_remove_thumbnail_and_selected_gallery_images(): void
    {
        $product = $this->productWithFiles();
        [$first, $second] = $product->images->all();

        $this->update($product, $this->payload($product, [
            'remove_thumbnail' => 1,
            'remove_images'    => [$first->id],
        ]))->assertSessionHasNoErrors();

        $fresh = $product->fresh('images');
        $this->assertNull($fresh->thumbnail);
        $this->assertSame([$second->id], $fresh->images->pluck('id')->all());

        Storage::disk('public')->assertMissing("products/{$product->id}/thumb.png");
        Storage::disk('public')->assertMissing($first->path);
        Storage::disk('public')->assertExists($second->path);
    }

    public function test_new_gallery_images_continue_the_sort_order(): void
    {
        $product = $this->productWithFiles();

        $this->update($product, $this->payload($product, [
            'images' => [$this->png('a.png'), $this->png('b.png')],
        ]))->assertSessionHasNoErrors();

        $images = $product->fresh()->images;
        $this->assertSame([1, 2, 3, 4], $images->pluck('sort_order')->all());
        foreach ($images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_gallery_cannot_exceed_eight_images(): void
    {
        $product = $this->productWithFiles(); // đã có 2 ảnh

        $files = [];
        for ($i = 1; $i <= 7; $i++) {          // 2 + 7 = 9 > 8
            $files[] = $this->png("n{$i}.png");
        }

        $this->update($product, $this->payload($product, ['images' => $files]))
            ->assertSessionHasErrors('images');

        $this->assertCount(2, $product->fresh()->images);
    }

    public function test_cannot_remove_images_of_another_product(): void
    {
        $product = $this->productWithFiles('OLD-001');
        $other   = $this->productWithFiles('OLD-002');
        $foreign = $other->images->first();

        $this->update($product, $this->payload($product, ['remove_images' => [$foreign->id]]))
            ->assertSessionHasErrors('remove_images.0');

        $this->assertDatabaseHas('product_images', ['id' => $foreign->id]);
        Storage::disk('public')->assertExists($foreign->path);
    }

    public function test_invalid_update_leaves_files_and_data_untouched(): void
    {
        $product = $this->productWithFiles();
        $image   = $product->images->first();

        $this->update($product, $this->payload($product, [
            'price'            => 'khong-phai-so',
            'remove_thumbnail' => 1,
            'remove_images'    => [$image->id],
        ]))->assertSessionHasErrors('price');

        $fresh = $product->fresh('images');
        $this->assertSame($product->thumbnail, $fresh->thumbnail);
        $this->assertCount(2, $fresh->images);
        Storage::disk('public')->assertExists($product->thumbnail);
        Storage::disk('public')->assertExists($image->path);
    }
}