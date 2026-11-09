<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitorCatalogTest extends TestCase
{
    use RefreshDatabase;

    private Category $parentMonitor;
    private Category $gamingMonitor;
    private Category $graphicMonitor;
    private Brand $samsung;
    private Brand $lg;
    private Product $monitorOled;
    private Product $monitorViewFinity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parentMonitor = Category::create([
            'name'      => 'Màn hình',
            'slug'      => 'man-hinh',
            'parent_id' => null,
        ]);

        $this->gamingMonitor = Category::create([
            'name'      => 'Màn hình gaming',
            'slug'      => 'man-hinh-gaming',
            'parent_id' => $this->parentMonitor->id,
        ]);

        $this->graphicMonitor = Category::create([
            'name'      => 'Màn hình đồ họa',
            'slug'      => 'man-hinh-do-hoa',
            'parent_id' => $this->parentMonitor->id,
        ]);

        $this->samsung = Brand::create(['name' => 'Samsung', 'slug' => 'samsung']);
        $this->lg = Brand::create(['name' => 'LG', 'slug' => 'lg']);

        $this->monitorOled = Product::create([
            'category_id'    => $this->gamingMonitor->id,
            'brand_id'       => $this->lg->id,
            'name'           => 'Màn hình Gaming LG UltraGear OLED 32GS95UV-B',
            'slug'           => 'man-hinh-gaming-lg-ultragear-oled-32gs95uv-b',
            'sku'            => 'MH-LG-32GS95UV',
            'price'          => 34990000,
            'sale_price'     => 31990000,
            'stock_quantity' => 5,
            'specs'          => [
                'Kích thước' => '31.5 inch',
                'Tấm nền'    => 'WOLED Dual-Mode',
                'Tần số quét' => '240Hz 4K',
            ],
            'thumbnail'      => 'images/Monitor/86015_man_hinh_lg_oled_32gs95uv_b_1.webp',
            'is_active'      => true,
        ]);

        $this->monitorViewFinity = Product::create([
            'category_id'    => $this->graphicMonitor->id,
            'brand_id'       => $this->samsung->id,
            'name'           => 'Màn hình đồ họa Samsung ViewFinity S8 LS32B800P',
            'slug'           => 'man-hinh-do-hoa-samsung-viewfinity-s8-ls32b800p',
            'sku'            => 'MH-SS-VF-S8',
            'price'          => 13990000,
            'sale_price'     => 12490000,
            'stock_quantity' => 8,
            'specs'          => [
                'Kích thước' => '32 inch',
                'Độ phân giải' => '4K UHD',
                'Tấm nền'    => 'IPS Chống chói',
            ],
            'thumbnail'      => 'images/Monitor/69884_man_hinh_samsung_viewfinity_ls32b800p_850x850_3.webp',
            'is_active'      => true,
        ]);
    }

    public function test_shop_index_displays_monitor_products_and_category_filter(): void
    {
        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertSee('Màn hình Gaming LG UltraGear OLED 32GS95UV-B');
        $response->assertSee('Màn hình đồ họa Samsung ViewFinity S8 LS32B800P');
        $response->assertSee('Màn hình');
    }

    public function test_shop_index_filters_specifically_by_monitor_category(): void
    {
        $response = $this->get(route('shop.index', ['categories' => ['Màn hình gaming']]));

        $response->assertOk();
        $response->assertSee('Màn hình Gaming LG UltraGear OLED 32GS95UV-B');
        $response->assertDontSee('Màn hình đồ họa Samsung ViewFinity S8 LS32B800P');
    }

    public function test_monitor_detail_page_renders_specs_and_image(): void
    {
        $response = $this->get(route('shop.show', $this->monitorOled->slug));

        $response->assertOk();
        $response->assertSee('Màn hình Gaming LG UltraGear OLED 32GS95UV-B');
        $response->assertSee('31.990.000đ');
        $response->assertSee('WOLED Dual-Mode');
        $response->assertSee('240Hz 4K');
    }

    public function test_quiz_recommends_monitor_when_device_type_is_man_hinh(): void
    {
        $response = $this->postJson(route('quiz.submit'), [
            'purpose'     => 'gaming',
            'device_type' => 'man-hinh',
            'budget'      => 'above_5m',
            'features'    => ['high_refresh'],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'name' => 'Màn hình Gaming LG UltraGear OLED 32GS95UV-B',
        ]);
    }

    public function test_monitors_can_be_added_to_comparison(): void
    {
        $toggle1 = $this->postJson(route('compare.toggle', $this->monitorOled->id));
        $toggle1->assertOk();
        $toggle1->assertJson(['success' => true, 'action' => 'added']);

        $toggle2 = $this->postJson(route('compare.toggle', $this->monitorViewFinity->id));
        $toggle2->assertOk();
        $toggle2->assertJson(['success' => true, 'action' => 'added']);

        $compareRes = $this->get(route('compare.index'));
        $compareRes->assertOk();
        $compareRes->assertSee('Màn hình Gaming LG UltraGear OLED 32GS95UV-B');
        $compareRes->assertSee('Màn hình đồ họa Samsung ViewFinity S8 LS32B800P');
        $compareRes->assertSee('WOLED Dual-Mode');
        $compareRes->assertSee('IPS Chống chói');
    }
}
