<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(
        string $name,
        string $sku,
        int $price,
        Category $category,
        Brand $brand,
        ?int $salePrice = null,
        bool $isActive = true,
        int $stock = 10
    ): Product {
        return Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => $name,
            'sku'            => $sku,
            'price'          => $price,
            'sale_price'     => $salePrice,
            'stock_quantity' => $stock,
            'is_active'      => $isActive,
            'description'    => "Mô tả của {$name}",
            'specs'          => ['Bảo hành' => '12 tháng'],
        ]);
    }

    public function test_shop_index_queries_from_database_and_eager_loads_relations(): void
    {
        $parent = Category::create(['name' => 'Chuột']);
        $category = Category::create(['name' => 'Chuột gaming', 'parent_id' => $parent->id]);
        $brand = Brand::create(['name' => 'Razer']);

        $product = $this->createProduct('Razer Viper V3', 'RZ-V3', 2500000, $category, $brand);

        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertViewIs('client.shop.index');
        $response->assertSee('Razer Viper V3');

        // Kiểm tra Eager Loading with(['category', 'brand'])
        $products = $response->viewData('products');
        $this->assertNotEmpty($products);

        /** @var Product $firstProduct */
        $firstProduct = $products->first();
        $this->assertTrue(
            $firstProduct->relationLoaded('category'),
            'Quan hệ category chưa được Eager Loading!'
        );
        $this->assertTrue(
            $firstProduct->relationLoaded('brand'),
            'Quan hệ brand chưa được Eager Loading!'
        );
    }

    public function test_shop_index_filters_by_category(): void
    {
        $catMouse = Category::create(['name' => 'Chuột']);
        $catKey = Category::create(['name' => 'Bàn phím']);
        $brand = Brand::create(['name' => 'Logitech']);

        $this->createProduct('Chuột G102', 'LG-G102', 400000, $catMouse, $brand);
        $this->createProduct('Bàn phím G213', 'LG-G213', 900000, $catKey, $brand);

        $response = $this->get(route('shop.index', ['categories' => ['Chuột']]));

        $response->assertOk();
        $response->assertSee('Chuột G102');
        $response->assertDontSee('Bàn phím G213');
    }

    public function test_shop_index_filters_by_brand(): void
    {
        $cat = Category::create(['name' => 'Tai nghe']);
        $brandRazer = Brand::create(['name' => 'Razer']);
        $brandCorsair = Brand::create(['name' => 'Corsair']);

        $this->createProduct('Razer BlackShark', 'RZ-BS', 1500000, $cat, $brandRazer);
        $this->createProduct('Corsair HS55', 'CS-HS55', 1200000, $cat, $brandCorsair);

        $response = $this->get(route('shop.index', ['brands' => ['Razer']]));

        $response->assertOk();
        $response->assertSee('Razer BlackShark');
        $response->assertDontSee('Corsair HS55');
    }

    public function test_shop_index_filters_by_price_range_min_and_max(): void
    {
        $cat = Category::create(['name' => 'Chuột']);
        $brand = Brand::create(['name' => 'Logitech']);

        $this->createProduct('Chuột Rẻ', 'CH-01', 500000, $cat, $brand);
        $this->createProduct('Chuột Trung Cấp', 'CH-02', 1500000, $cat, $brand);
        $this->createProduct('Chuột Cao Cấp', 'CH-03', 3500000, $cat, $brand);

        // Khoảng giá từ 1tr đến 2tr
        $response = $this->get(route('shop.index', ['min_price' => 1000000, 'max_price' => 2000000]));

        $response->assertOk();
        $response->assertSee('Chuột Trung Cấp');
        $response->assertDontSee('Chuột Rẻ');
        $response->assertDontSee('Chuột Cao Cấp');
    }

    public function test_shop_index_filters_by_in_stock_status(): void
    {
        $cat = Category::create(['name' => 'Chuột']);
        $brand = Brand::create(['name' => 'Logitech']);

        $this->createProduct('Chuột Còn Hàng', 'ST-01', 1000000, $cat, $brand, null, true, 10);
        $this->createProduct('Chuột Hết Hàng', 'ST-02', 1200000, $cat, $brand, null, true, 0);

        $response = $this->get(route('shop.index', ['stock_status' => 'in_stock']));

        $response->assertOk();
        $response->assertSee('Chuột Còn Hàng');
        $response->assertDontSee('Chuột Hết Hàng');
    }

    public function test_shop_index_searches_by_name_and_sku(): void
    {
        $cat = Category::create(['name' => 'Chuột']);
        $brand = Brand::create(['name' => 'Logitech']);

        $this->createProduct('Superlight Pro', 'SL-PRO-99', 3000000, $cat, $brand);
        $this->createProduct('Deathadder', 'DA-01', 1200000, $cat, $brand);

        $response = $this->get(route('shop.index', ['search' => 'SL-PRO-99']));

        $response->assertOk();
        $response->assertSee('Superlight Pro');
        $response->assertDontSee('Deathadder');
    }

    public function test_shop_index_sorts_by_price_asc_and_desc(): void
    {
        $cat = Category::create(['name' => 'Tai nghe']);
        $brand = Brand::create(['name' => 'Sony']);

        $cheap = $this->createProduct('Tai nghe giá rẻ', 'TN-01', 500000, $cat, $brand);
        $mid = $this->createProduct('Tai nghe tầm trung', 'TN-02', 1500000, $cat, $brand);
        $expensive = $this->createProduct('Tai nghe đắt tiền', 'TN-03', 4000000, $cat, $brand);

        // Sort tăng dần
        $resAsc = $this->get(route('shop.index', ['sort' => 'price_asc']));
        $resAsc->assertOk();
        $ascIds = $resAsc->viewData('products')->pluck('id')->all();
        $this->assertEquals([$cheap->id, $mid->id, $expensive->id], $ascIds);

        // Sort giảm dần
        $resDesc = $this->get(route('shop.index', ['sort' => 'price_desc']));
        $resDesc->assertOk();
        $descIds = $resDesc->viewData('products')->pluck('id')->all();
        $this->assertEquals([$expensive->id, $mid->id, $cheap->id], $descIds);
    }

    public function test_shop_index_sorts_by_popular_best_selling(): void
    {
        $cat = Category::create(['name' => 'Bàn phím']);
        $brand = Brand::create(['name' => 'Akko']);

        $p1 = $this->createProduct('Phím Bán Ít', 'KB-01', 1000000, $cat, $brand, null, true, 5);
        $p2 = $this->createProduct('Phím Bán Chạy Nhất', 'KB-02', 1200000, $cat, $brand, null, true, 50);

        $user = \App\Models\User::create([
            'name'     => 'Khách Test',
            'email'    => 'khach@test.vn',
            'password' => 'password123',
        ]);

        // Tạo order hợp lệ theo schema
        $order = Order::create([
            'code'             => 'ORD-TEST-01',
            'user_id'          => $user->id,
            'receiver_name'    => 'Test Khách',
            'phone'            => '0901234567',
            'shipping_address' => 'HCM',
            'subtotal'         => 5000000,
            'total'            => 5000000,
            'status'           => Order::STATUS_COMPLETED,
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $p2->id,
            'product_name' => $p2->name,
            'unit_price'   => $p2->price,
            'quantity'     => 100, // Đã bán 100 cái
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $p1->id,
            'product_name' => $p1->name,
            'unit_price'   => $p1->price,
            'quantity'     => 2, // Đã bán 2 cái
        ]);

        $response = $this->get(route('shop.index', ['sort' => 'popular']));
        $response->assertOk();

        $orderedIds = $response->viewData('products')->pluck('id')->all();
        $this->assertEquals($p2->id, $orderedIds[0]);
    }

    public function test_shop_index_paginates_12_products_per_page(): void
    {
        $cat = Category::create(['name' => 'Phụ kiện']);
        $brand = Brand::create(['name' => 'Anker']);

        // Tạo 15 sản phẩm
        for ($i = 1; $i <= 15; $i++) {
            $this->createProduct("Sản phẩm {$i}", "SKU-{$i}", 100000 * $i, $cat, $brand);
        }

        // Trang 1: có 12 sản phẩm
        $resPage1 = $this->get(route('shop.index'));
        $resPage1->assertOk();
        $this->assertCount(12, $resPage1->viewData('products'));
        $this->assertEquals(15, $resPage1->viewData('totalProducts'));

        // Trang 2: có 3 sản phẩm còn lại
        $resPage2 = $this->get(route('shop.index', ['page' => 2]));
        $resPage2->assertOk();
        $this->assertCount(3, $resPage2->viewData('products'));
    }

    public function test_shop_index_does_not_show_inactive_products(): void
    {
        $cat = Category::create(['name' => 'Chuột']);
        $brand = Brand::create(['name' => 'Logitech']);

        $this->createProduct('Chuột Ẩn', 'HIDE-01', 500000, $cat, $brand, null, false);

        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertDontSee('Chuột Ẩn');
    }

    public function test_shop_show_displays_product_detail_from_database(): void
    {
        $cat = Category::create(['name' => 'Tai nghe']);
        $brand = Brand::create(['name' => 'HyperX']);

        $product = $this->createProduct(
            'HyperX Cloud Alpha Wireless',
            'HX-CAW',
            4500000,
            $cat,
            $brand,
            3990000
        );

        $response = $this->get(route('shop.show', $product->slug));

        $response->assertOk();
        $response->assertViewIs('client.shop.show');
        $response->assertSee('HyperX Cloud Alpha Wireless');
        $response->assertSee('HX-CAW');
        $response->assertSee('HyperX');
        $response->assertSee('Tai nghe');
    }

    public function test_shop_show_returns_404_for_non_existent_slug(): void
    {
        $response = $this->get(route('shop.show', 'san-pham-khong-ton-tai'));

        $response->assertNotFound();
    }

    public function test_shop_show_renders_auxiliary_images_from_product_images_relation(): void
    {
        $cat = Category::create(['name' => 'Bàn phím cơ']);
        $brand = Brand::create(['name' => 'Keychron']);

        $product = $this->createProduct('Keychron K2 Pro', 'KC-K2P', 2100000, $cat, $brand);

        \App\Models\ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/keychron-k2-side.png',
            'sort_order' => 1,
        ]);
        \App\Models\ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/keychron-k2-top.png',
            'sort_order' => 2,
        ]);

        $response = $this->get(route('shop.show', $product->slug));

        $response->assertOk();
        $productData = $response->viewData('product');
        $this->assertCount(3, $productData['gallery']); // 1 thumbnail + 2 auxiliary images
        $response->assertSee('products/keychron-k2-side.png');
        $response->assertSee('products/keychron-k2-top.png');
    }

    public function test_shop_show_parses_specs_json_dynamically(): void
    {
        $cat = Category::create(['name' => 'Bàn phím']);
        $brand = Brand::create(['name' => 'Akko']);

        $product = Product::create([
            'category_id'    => $cat->id,
            'brand_id'       => $brand->id,
            'name'           => 'Akko 3098B Plus',
            'sku'            => 'AK-3098B',
            'price'          => 1850000,
            'stock_quantity' => 15,
            'is_active'      => true,
            'specs'          => [
                'Kết nối' => 'Bluetooth 5.0 / 2.4Ghz / Type-C',
                'Switch' => 'Akko CS Jelly Pink',
                'Pin' => '3000mAh',
            ],
        ]);

        $response = $this->get(route('shop.show', $product->slug));

        $response->assertOk();
        $productData = $response->viewData('product');
        $this->assertIsArray($productData['specs']);
        $this->assertEquals('Akko CS Jelly Pink', $productData['specs']['Switch']);
        $response->assertSee('Kết nối');
        $response->assertSee('Akko CS Jelly Pink');
        $response->assertSee('3000mAh');
    }

    public function test_shop_show_renders_related_products_with_same_category_id(): void
    {
        $catMouse = Category::create(['name' => 'Chuột']);
        $catKey = Category::create(['name' => 'Bàn phím']);
        $brand = Brand::create(['name' => 'Logitech']);

        $mainProduct = $this->createProduct('Logitech G Pro X Superlight', 'LG-GPX', 3200000, $catMouse, $brand);
        $related1 = $this->createProduct('Logitech G502 Hero', 'LG-G502', 1200000, $catMouse, $brand);
        $related2 = $this->createProduct('Logitech G304', 'LG-G304', 800000, $catMouse, $brand);
        $unrelated = $this->createProduct('Logitech G913 TKL', 'LG-G913', 4500000, $catKey, $brand);

        $response = $this->get(route('shop.show', $mainProduct->slug));

        $response->assertOk();
        $relatedProducts = $response->viewData('relatedProducts');
        $relatedNames = collect($relatedProducts)->pluck('name')->all();

        $this->assertContains('Logitech G502 Hero', $relatedNames);
        $this->assertContains('Logitech G304', $relatedNames);
        $this->assertNotContains('Logitech G Pro X Superlight', $relatedNames); // không chứa chính nó
    }

    public function test_buy_now_adds_to_cart_and_redirects_to_checkout(): void
    {
        $cat = Category::create(['name' => 'Củ sạc']);
        $brand = Brand::create(['name' => 'Anker']);

        $product = $this->createProduct('Anker 65W GaN', 'AK-65W', 950000, $cat, $brand);

        // Test form POST buy_now = 1
        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 2,
            'buy_now'    => 1,
        ]);

        $response->assertRedirect(route('checkout.index'));
        $this->assertEquals(2, session('cart')[$product->id]['quantity']);

        // Test AJAX POST buy_now = 1
        $ajaxResponse = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
            'buy_now'    => 1,
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson([
            'success'      => true,
            'redirect_url' => route('checkout.index'),
        ]);
    }
}
