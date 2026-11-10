<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_submit_returns_top_matches_and_logs_data(): void
    {
        $cat = Category::create(['name' => 'Bàn phím cơ', 'slug' => 'ban-phim-co']);
        $brand = Brand::create(['name' => 'Aula', 'slug' => 'aula']);

        $p1 = Product::create([
            'category_id'    => $cat->id,
            'brand_id'       => $brand->id,
            'name'           => 'Bàn phím Gaming Không Dây RGB',
            'slug'           => 'ban-phim-gaming-khong-day-rgb',
            'sku'            => 'KB-QUIZ-1',
            'price'          => 1500000,
            'stock_quantity' => 10,
            'specs'          => ['Kết nối' => 'Wireless', 'Led' => 'RGB'],
            'is_active'      => true,
        ]);

        $payload = [
            'device_type' => 'ban-phim-co',
            'budget'      => '1m_to_3m',
            'purpose'     => 'gaming',
            'features'    => ['wireless', 'rgb'],
        ];

        $response = $this->postJson(route('quiz.submit'), $payload);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'matches' => [
                    '*' => [
                        'id',
                        'name',
                        'match_score',
                        'match_percentage',
                        'match_reasons',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('quiz_logs', [
            'answers->device_type' => 'ban-phim-co',
            'answers->purpose'     => 'gaming',
        ]);
    }
}