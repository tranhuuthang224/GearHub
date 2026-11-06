<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\QuizLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_index_page_can_be_rendered(): void
    {
        $response = $this->get(route('quiz.index'));

        $response->assertOk();
        $response->assertSee('Tìm Gear Chuẩn Gu Trong 30 Giây');
        $response->assertSee('SMART GEAR FINDER');
    }

    public function test_quiz_can_be_submitted_and_recommends_matching_products(): void
    {
        $category = Category::create([
            'name'      => 'Bàn phím cơ',
            'slug'      => 'ban-phim-co',
            'parent_id' => null,
        ]);

        $brand = Brand::create([
            'name' => 'Aula',
            'slug' => 'aula',
        ]);

        $product = Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => 'Bàn phím cơ Aula F75 Không dây Wireless',
            'slug'           => 'ban-phim-co-aula-f75-khong-day-wireless',
            'sku'            => 'KB-AULA-F75-WL',
            'price'          => 1500000,
            'sale_price'     => 1290000,
            'stock_quantity' => 10,
            'is_active'      => true,
            'specs'          => [
                'Kết nối' => 'Không dây 3 mode (Bluetooth/2.4G/Type-C)',
                'Switch'  => 'Reaper switch',
                'LED'     => 'RGB 16.8 triệu màu',
            ],
        ]);

        $response = $this->postJson(route('quiz.submit'), [
            'purpose'     => 'gaming',
            'device_type' => 'ban-phim-co',
            'budget'      => '1m_to_3m',
            'features'    => ['wireless', 'rgb'],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'name' => 'Bàn phím cơ Aula F75 Không dây Wireless',
        ]);

        $this->assertDatabaseHas('quiz_logs', [
            'recommended_product_ids' => json_encode([$product->id]),
        ]);
    }
}
