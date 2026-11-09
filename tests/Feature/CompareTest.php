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
}