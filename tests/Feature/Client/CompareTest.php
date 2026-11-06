<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    private Category $keyboardCategory;
    private Category $mouseCategory;
    private Brand $brand;
    private Product $keyboard1;
    private Product $keyboard2;
    private Product $mouse1;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->brand = Brand::create([
            'name' => 'Aula',
            'slug' => 'aula',
        ]);

        $this->keyboard1 = Product::create([
            'category_id'    => $this->keyboardCategory->id,
            'brand_id'       => $this->brand->id,
            'name'           => 'Bàn phím Aula F75',
            'slug'           => 'ban-phim-aula-f75',
            'sku'            => 'KB-AULA-F75',
            'price'          => 1250000,
            'sale_price'     => 990000,
            'stock_quantity' => 10,
            'specs'          => ['Switch' => 'Reaper', 'Kết nối' => 'Wireless 2.4G', 'Layout' => '75%'],
            'is_active'      => true,
        ]);

        $this->keyboard2 = Product::create([
            'category_id'    => $this->keyboardCategory->id,
            'brand_id'       => $this->brand->id,
            'name'           => 'Bàn phím Aula S100 Pro',
            'slug'           => 'ban-phim-aula-s100-pro',
            'sku'            => 'KB-AULA-S100P',
            'price'          => 1450000,
            'sale_price'     => 1190000,
            'stock_quantity' => 5,
            'specs'          => ['Switch' => 'Custom Linear', 'Kết nối' => '3 Mode', 'Layout' => '98 phím'],
            'is_active'      => true,
        ]);

        $this->mouse1 = Product::create([
            'category_id'    => $this->mouseCategory->id,
            'brand_id'       => $this->brand->id,
            'name'           => 'Chuột gaming Razer Viper',
            'slug'           => 'chuot-gaming-razer-viper',
            'sku'            => 'MS-RZ-VIPER',
            'price'          => 1890000,
            'sale_price'     => null,
            'stock_quantity' => 8,
            'specs'          => ['DPI' => '30000', 'Kết nối' => 'Wireless', 'Trọng lượng' => '54g'],
            'is_active'      => true,
        ]);
    }

    public function test_can_view_compare_page_when_empty(): void
    {
        $response = $this->get('/compare');

        $response->assertStatus(200);
        $response->assertSee('Chưa có sản phẩm nào trong danh sách so sánh');
    }

    public function test_can_toggle_add_and_remove_product_in_compare(): void
    {
        // 1. Thêm keyboard1
        $res1 = $this->postJson("/compare/toggle/{$this->keyboard1->id}");
        $res1->assertStatus(200);
        $res1->assertJson([
            'success' => true,
            'action'  => 'added',
            'count'   => 1,
        ]);

        $this->assertTrue(session()->has('compare_items'));
        $this->assertArrayHasKey($this->keyboard1->id, session('compare_items'));

        // 2. Toggle lại keyboard1 -> tự động xóa (action: removed)
        $res2 = $this->postJson("/compare/toggle/{$this->keyboard1->id}");
        $res2->assertStatus(200);
        $res2->assertJson([
            'success' => true,
            'action'  => 'removed',
            'count'   => 0,
        ]);
        $this->assertArrayNotHasKey($this->keyboard1->id, session('compare_items'));
    }

    public function test_can_add_multiple_products_of_same_category(): void
    {
        $this->postJson("/compare/toggle/{$this->keyboard1->id}")->assertStatus(200);
        $res = $this->postJson("/compare/toggle/{$this->keyboard2->id}");

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'action'  => 'added',
            'count'   => 2,
        ]);

        // Kiểm tra xem trang /compare hiển thị bảng so sánh cả 2 sản phẩm
        $viewRes = $this->get('/compare');
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Bàn phím Aula F75');
        $viewRes->assertSee('Bàn phím Aula S100 Pro');
        $viewRes->assertSee('Reaper');
        $viewRes->assertSee('Custom Linear');
    }

    public function test_cannot_compare_products_from_different_category_families(): void
    {
        // Thêm keyboard1 trước
        $this->postJson("/compare/toggle/{$this->keyboard1->id}")->assertStatus(200);

        // Thử thêm chuột vào danh sách so sánh -> phải báo lỗi 422
        $res = $this->postJson("/compare/toggle/{$this->mouse1->id}");

        $res->assertStatus(422);
        $res->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('Chỉ có thể so sánh các sản phẩm cùng nhóm danh mục', $res->json('message'));
    }

    public function test_cannot_exceed_max_4_compare_items(): void
    {
        // Tạo thêm 3 sản phẩm cùng danh mục bàn phím
        $p3 = Product::create([
            'category_id' => $this->keyboardCategory->id,
            'brand_id'    => $this->brand->id,
            'name'        => 'KB 3',
            'slug'        => 'kb-3',
            'sku'         => 'KB-3',
            'price'       => 1000000,
            'is_active'   => true,
        ]);
        $p4 = Product::create([
            'category_id' => $this->keyboardCategory->id,
            'brand_id'    => $this->brand->id,
            'name'        => 'KB 4',
            'slug'        => 'kb-4',
            'sku'         => 'KB-4',
            'price'       => 1000000,
            'is_active'   => true,
        ]);
        $p5 = Product::create([
            'category_id' => $this->keyboardCategory->id,
            'brand_id'    => $this->brand->id,
            'name'        => 'KB 5',
            'slug'        => 'kb-5',
            'sku'         => 'KB-5',
            'price'       => 1000000,
            'is_active'   => true,
        ]);

        $this->postJson("/compare/toggle/{$this->keyboard1->id}")->assertStatus(200);
        $this->postJson("/compare/toggle/{$this->keyboard2->id}")->assertStatus(200);
        $this->postJson("/compare/toggle/{$p3->id}")->assertStatus(200);
        $this->postJson("/compare/toggle/{$p4->id}")->assertStatus(200);

        // Món thứ 5 -> phải bị từ chối với status 422
        $res = $this->postJson("/compare/toggle/{$p5->id}");
        $res->assertStatus(422);
        $res->assertJson([
            'success' => false,
            'count'   => 4,
        ]);
        $this->assertStringContainsString('tối đa 4 sản phẩm', $res->json('message'));
    }

    public function test_can_remove_and_clear_compare_items(): void
    {
        $this->postJson("/compare/toggle/{$this->keyboard1->id}")->assertStatus(200);
        $this->postJson("/compare/toggle/{$this->keyboard2->id}")->assertStatus(200);

        // Xóa 1 món
        $delRes = $this->deleteJson("/compare/remove/{$this->keyboard1->id}");
        $delRes->assertStatus(200);
        $delRes->assertJson([
            'success' => true,
            'count'   => 1,
        ]);

        // Xóa tất cả
        $clearRes = $this->deleteJson('/compare/clear');
        $clearRes->assertStatus(200);
        $clearRes->assertJson([
            'success' => true,
            'count'   => 0,
        ]);
        $this->assertEmpty(session('compare_items', []));
    }

    public function test_can_fetch_items_via_api(): void
    {
        $this->postJson("/compare/toggle/{$this->keyboard1->id}")->assertStatus(200);

        $res = $this->getJson('/compare/items');
        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'count'   => 1,
        ]);
        $this->assertEquals('Bàn phím Aula F75', $res->json('items.0.name'));
    }
}
