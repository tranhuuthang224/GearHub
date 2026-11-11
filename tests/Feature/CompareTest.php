<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Brand;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    protected function createProductWithCategory(string $categoryName, ?Category $parent = null): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($categoryName)],
            ['name' => $categoryName, 'parent_id' => $parent?->id]
        );

        $brand = Brand::firstOrCreate(
            ['slug' => 'gear-brand'],
            ['name' => 'Gear Brand']
        );

        return Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => 'Gear ' . uniqid(),
            'slug'           => 'gear-' . uniqid(),
            'sku'            => 'SKU-' . uniqid(),
            'price'          => 1000000,
            'stock_quantity' => 10,
            'specs'          => ['Switch' => 'Red', 'Led' => 'RGB'],
            'is_active'      => true,
        ]);
    }

    public function test_can_add_product_to_compare_session(): void
    {
        $product = $this->createProductWithCategory('Bàn phím cơ');

        $response = $this->postJson(route('compare.add', $product->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'count'   => 1,
            ]);

        $this->assertEquals([$product->id], array_keys(session('compare_items')));
    }

    public function test_cannot_compare_products_from_different_root_categories(): void
    {
        $keyboard = $this->createProductWithCategory('Bàn phím cơ');
        $mouse    = $this->createProductWithCategory('Chuột Gaming');

        // Thêm bàn phím
        $this->postJson(route('compare.add', $keyboard->id))->assertOk();

        // Thêm chuột (khác root category) -> Bị chặn
        $response = $this->postJson(route('compare.add', $mouse->id));

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_cannot_add_more_than_4_products_to_compare(): void
    {
        $parentCat = Category::create(['name' => 'Bàn phím', 'slug' => 'ban-phim']);

        $products = [];
        for ($i = 0; $i < 5; $i++) {
            $products[] = $this->createProductWithCategory("Bàn phím {$i}", $parentCat);
        }

        // Thêm 4 sản phẩm đầu tiên -> OK
        for ($i = 0; $i < 4; $i++) {
            $this->postJson(route('compare.add', $products[$i]->id))->assertOk();
        }

        // Thêm sản phẩm thứ 5 -> Bị chặn tối đa 4
        $response = $this->postJson(route('compare.add', $products[4]->id));
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_remove_product_from_compare(): void
    {
        $product = $this->createProductWithCategory('Tai nghe');

        $this->postJson(route('compare.add', $product->id));

        $response = $this->deleteJson(route('compare.remove', $product->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'count'   => 0,
            ]);

        $this->assertEmpty(session('compare_items'));
    }

    public function test_can_compare_monitors_across_subcategories_and_render_specs_matrix(): void
    {
        $rootMonitor = Category::create([
            'name'      => 'Màn hình',
            'slug'      => 'man-hinh',
            'parent_id' => null,
        ]);

        $gamingMonitorCat = Category::create([
            'name'      => 'Màn hình gaming',
            'slug'      => 'man-hinh-gaming',
            'parent_id' => $rootMonitor->id,
        ]);

        $graphicMonitorCat = Category::create([
            'name'      => 'Màn hình đồ họa',
            'slug'      => 'man-hinh-do-hoa',
            'parent_id' => $rootMonitor->id,
        ]);

        $brand = Brand::firstOrCreate(['slug' => 'samsung'], ['name' => 'Samsung']);

        $monitor1 = Product::create([
            'category_id'    => $gamingMonitorCat->id,
            'brand_id'       => $brand->id,
            'name'           => 'Màn hình Gaming LG UltraGear 32 inch 240Hz',
            'slug'           => 'man-hinh-gaming-lg-ultragear-32-inch-240hz',
            'sku'            => 'MH-LG-240HZ',
            'price'          => 25000000,
            'stock_quantity' => 5,
            'specs'          => [
                'Kích thước'   => '32 inch',
                'Độ phân giải' => '4K UHD',
                'Tần số quét'  => '240Hz',
                'Tấm nền'      => 'OLED',
            ],
            'is_active'      => true,
        ]);

        $monitor2 = Product::create([
            'category_id'    => $graphicMonitorCat->id,
            'brand_id'       => $brand->id,
            'name'           => 'Màn hình Đồ Họa Dell UltraSharp 27 inch',
            'slug'           => 'man-hinh-do-hoa-dell-ultrasharp-27-inch',
            'sku'            => 'MH-DELL-U27',
            'price'          => 15000000,
            'stock_quantity' => 8,
            'specs'          => [
                'Kích thước'   => '27 inch',
                'Độ phân giải' => '2K QHD',
                'Tần số quét'  => '100Hz',
                'Tấm nền'      => 'IPS',
            ],
            'is_active'      => true,
        ]);

        // Thêm màn hình 1
        $res1 = $this->postJson(route('compare.add', $monitor1->id));
        $res1->assertOk()->assertJson(['success' => true, 'count' => 1]);

        // Thêm màn hình 2 (thuộc subcategory khác nhưng cùng root Màn hình)
        $res2 = $this->postJson(route('compare.add', $monitor2->id));
        $res2->assertOk()->assertJson(['success' => true, 'count' => 2]);

        // Xem bảng so sánh
        $viewRes = $this->get(route('compare.index'));
        $viewRes->assertOk();
        $viewRes->assertSee('Màn hình Gaming LG UltraGear 32 inch 240Hz');
        $viewRes->assertSee('Màn hình Đồ Họa Dell UltraSharp 27 inch');
        $viewRes->assertSee('240Hz');
        $viewRes->assertSee('100Hz');
        $viewRes->assertSee('OLED');
        $viewRes->assertSee('IPS');
        $viewRes->assertSee('Đặc tả thông số kỹ thuật (Specs Matrix)');
    }

    public function test_compare_preset_loads_monitors_automatically(): void
    {
        $rootMonitor = Category::create([
            'name'      => 'Màn hình',
            'slug'      => 'man-hinh',
            'parent_id' => null,
        ]);

        $brand = Brand::firstOrCreate(['slug' => 'lg'], ['name' => 'LG']);

        for ($i = 1; $i <= 2; $i++) {
            Product::create([
                'category_id'    => $rootMonitor->id,
                'brand_id'       => $brand->id,
                'name'           => "Màn hình Test {$i}",
                'slug'           => "man-hinh-test-{$i}",
                'sku'            => "MH-TEST-{$i}",
                'price'          => 5000000 * $i,
                'stock_quantity' => 10,
                'specs'          => ['Tần số quét' => ($i * 100) . 'Hz'],
                'is_active'      => true,
            ]);
        }

        $res = $this->get(route('compare.index', ['preset' => 'monitors']));
        $res->assertRedirect(route('compare.index'));
        $this->assertCount(2, session('compare_items', []));
    }

    public function test_cannot_compare_monitor_with_keyboard(): void
    {
        $rootMonitor = Category::create([
            'name'      => 'Màn hình',
            'slug'      => 'man-hinh',
            'parent_id' => null,
        ]);

        $rootKeyboard = Category::create([
            'name'      => 'Bàn phím cơ',
            'slug'      => 'ban-phim-co',
            'parent_id' => null,
        ]);

        $brand = Brand::firstOrCreate(['slug' => 'asus'], ['name' => 'Asus']);

        $monitor = Product::create([
            'category_id'    => $rootMonitor->id,
            'brand_id'       => $brand->id,
            'name'           => 'Màn hình Asus',
            'slug'           => 'man-hinh-asus',
            'sku'            => 'MH-ASUS',
            'price'          => 5000000,
            'stock_quantity' => 10,
            'specs'          => ['Tần số quét' => '144Hz'],
            'is_active'      => true,
        ]);

        $keyboard = Product::create([
            'category_id'    => $rootKeyboard->id,
            'brand_id'       => $brand->id,
            'name'           => 'Bàn phím cơ Asus',
            'slug'           => 'ban-phim-co-asus',
            'sku'            => 'KB-ASUS',
            'price'          => 2000000,
            'stock_quantity' => 10,
            'specs'          => ['Switch' => 'ROG RX'],
            'is_active'      => true,
        ]);

        $this->postJson(route('compare.add', $monitor->id))->assertOk();
        $failRes = $this->postJson(route('compare.add', $keyboard->id));
        $failRes->assertStatus(422);
        $this->assertStringContainsString('Chỉ có thể so sánh các sản phẩm cùng nhóm', $failRes->json('message'));
    }
}