<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Display the Product Catalog (Shop/All Products) matching Figma Page 4.
     */
    public function index(Request $request): View
    {
        $categories = [
            ['name' => 'Headphones', 'slug' => 'headphones', 'count' => 48],
            ['name' => 'Mouse', 'slug' => 'mouse', 'count' => 32],
            ['name' => 'Keyboard', 'slug' => 'keyboard', 'count' => 24],
            ['name' => 'Laptop', 'slug' => 'laptop', 'count' => 16],
            ['name' => 'Console', 'slug' => 'console', 'count' => 8],
            ['name' => 'Speaker', 'slug' => 'speaker', 'count' => 12],
        ];

        $brands = [
            ['name' => 'HyperX', 'slug' => 'hyperx'],
            ['name' => 'Razer', 'slug' => 'razer'],
            ['name' => 'Logitech', 'slug' => 'logitech'],
            ['name' => 'Keychron', 'slug' => 'keychron'],
            ['name' => 'Sony', 'slug' => 'sony'],
            ['name' => 'Asus', 'slug' => 'asus'],
        ];

        // 9 Catalog Products directly from Figma Page 4
        $allProducts = [
            [
                'id' => 101,
                'name' => 'HyperX Cloud III',
                'slug' => 'hyperx-cloud-iii',
                'category' => 'Headphones',
                'brand' => 'HyperX',
                'price' => 2490000,
                'sale_price' => null,
                'thumbnail' => asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png'),
                'rating' => '4.8',
            ],
            [
                'id' => 102,
                'name' => 'Razer Basilisk V3',
                'slug' => 'razer-basilisk-v3',
                'category' => 'Mouse',
                'brand' => 'Razer',
                'price' => 1990000,
                'sale_price' => 1690000,
                'thumbnail' => asset('images/mouse-razer.png'),
                'rating' => '4.8',
            ],
            [
                'id' => 103,
                'name' => 'Logitech G Pro X Superlight',
                'slug' => 'logitech-g-pro-x',
                'category' => 'Mouse',
                'brand' => 'Logitech',
                'price' => 2990000,
                'sale_price' => null,
                'thumbnail' => asset('images/mouse-atk.png'),
                'rating' => '4.8',
            ],
            [
                'id' => 104,
                'name' => 'Asus ROG Strix Scar 16',
                'slug' => 'asus-rog-strix',
                'category' => 'Laptop',
                'brand' => 'Asus',
                'price' => 32990000,
                'sale_price' => null,
                'thumbnail' => asset('images/laptop-gaming.png'),
                'rating' => '4.8',
            ],
            [
                'id' => 105,
                'name' => 'Keychron K2 Wireless Mechanical',
                'slug' => 'keychron-k2',
                'category' => 'Keyboard',
                'brand' => 'Keychron',
                'price' => 1890000,
                'sale_price' => null,
                'thumbnail' => asset('images/keyboard-katana.png'),
                'rating' => '4.8',
            ],
            [
                'id' => 106,
                'name' => 'Sony WH-1000XM5 Wireless Noise-Canceling',
                'slug' => 'sony-wh-1000xm5',
                'category' => 'Headphones',
                'brand' => 'Sony',
                'price' => 7490000,
                'sale_price' => null,
                'thumbnail' => asset('images/5b3fd7c2c7625.png'),
                'rating' => '4.8',
            ],
            [
                'id' => 107,
                'name' => 'AOC 27" 144Hz Gaming Monitor',
                'slug' => 'aoc-27-144hz',
                'category' => 'Console',
                'brand' => 'AOC',
                'price' => 4990000,
                'sale_price' => null,
                'thumbnail' => asset('images/png-transparent-lcd-television-led-backlit-lcd-lg-mp59g-p-computer-monitors-lg-electronics-lg-television-angle-computer-monitor-accessory (1).png'),
                'rating' => '4.8',
            ],
            [
                'id' => 108,
                'name' => 'Elgato Wave:3 USB Microphone',
                'slug' => 'elgato-wave-3',
                'category' => 'Speaker',
                'brand' => 'Elgato',
                'price' => 3390000,
                'sale_price' => null,
                'thumbnail' => asset('images/images (2).png'),
                'rating' => '4.8',
            ],
            [
                'id' => 109,
                'name' => 'SteelSeries Arctis Nova Pro',
                'slug' => 'steelseries-arctis',
                'category' => 'Headphones',
                'brand' => 'SteelSeries',
                'price' => 2790000,
                'sale_price' => null,
                'thumbnail' => asset('images/hero-headphone.png'),
                'rating' => '4.8',
            ],
        ];

        // Basic mock filtering based on query params (P1 requirements: query string, Eloquent scope ready)
        $selectedCategories = (array) $request->input('categories', []);
        $selectedBrands = (array) $request->input('brands', []);
        $search = $request->input('search');
        $sort = $request->input('sort', 'newest');

        $filteredProducts = collect($allProducts);

        if (!empty($selectedCategories)) {
            $filteredProducts = $filteredProducts->whereIn('category', $selectedCategories);
        }

        if (!empty($selectedBrands)) {
            $filteredProducts = $filteredProducts->whereIn('brand', $selectedBrands);
        }

        if ($search) {
            $filteredProducts = $filteredProducts->filter(function ($item) use ($search) {
                return stripos($item['name'], $search) !== false;
            });
        }

        if ($sort === 'price_asc') {
            $filteredProducts = $filteredProducts->sortBy(fn($p) => $p['sale_price'] ?? $p['price']);
        } elseif ($sort === 'price_desc') {
            $filteredProducts = $filteredProducts->sortByDesc(fn($p) => $p['sale_price'] ?? $p['price']);
        }

        $products = $filteredProducts->values();
        $totalProducts = 128; // Display total matching Figma

        return view('client.shop.index', compact(
            'categories',
            'brands',
            'products',
            'selectedCategories',
            'selectedBrands',
            'search',
            'sort',
            'totalProducts'
        ));
    }

    /**
     * Display Single Product Detail (Figma Page 5).
     */
    public function show(string $slug): View
    {
        $dbProduct = null;
        try {
            $dbProduct = Product::with(['category', 'brand', 'images'])->where('slug', $slug)->first();
        } catch (\Throwable $e) {
            // Database not yet migrated or connection unavailable, fallback to mock data
            $dbProduct = null;
        }

        if ($dbProduct) {
            $thumb = $dbProduct->thumbnail 
                ? (str_starts_with($dbProduct->thumbnail, 'http') ? $dbProduct->thumbnail : asset('storage/' . $dbProduct->thumbnail))
                : asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png');

            $gallery = collect([$thumb]);
            foreach ($dbProduct->images as $img) {
                $gallery->push(str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path));
            }

            $product = [
                'id' => $dbProduct->id,
                'name' => $dbProduct->name,
                'slug' => $dbProduct->slug,
                'sku' => $dbProduct->sku,
                'category' => $dbProduct->category?->name ?? 'Phụ kiện',
                'brand' => $dbProduct->brand?->name ?? 'GearHub',
                'price' => (int) $dbProduct->price,
                'sale_price' => $dbProduct->sale_price ? (int) $dbProduct->sale_price : null,
                'rating' => '4.9',
                'reviews_count' => 128,
                'description' => $dbProduct->description ?? 'Sản phẩm phụ kiện công nghệ chính hãng, phân phối bởi GearHub.',
                'thumbnail' => $thumb,
                'gallery' => $gallery->unique()->values()->all(),
                'stock_quantity' => (int) $dbProduct->stock_quantity,
                'specs' => is_array($dbProduct->specs) ? $dbProduct->specs : (json_decode($dbProduct->specs, true) ?? []),
            ];

            $relatedProducts = Product::where('category_id', $dbProduct->category_id)
                ->where('id', '!=', $dbProduct->id)
                ->take(4)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'category' => $p->category?->name ?? 'Phụ kiện',
                    'brand' => $p->brand?->name ?? 'GearHub',
                    'price' => (int) $p->price,
                    'sale_price' => $p->sale_price ? (int) $p->sale_price : null,
                    'thumbnail' => $p->thumbnail ? (str_starts_with($p->thumbnail, 'http') ? $p->thumbnail : asset('storage/' . $p->thumbnail)) : asset('images/hero-headphone.png'),
                    'rating' => '4.8',
                ]);
        } else {
            // Rich fallback mock product matching Figma design perfectly
            $thumb = asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png');
            $product = [
                'id' => 101,
                'name' => 'Tai nghe Gaming HyperX Cloud III Wireless Red',
                'slug' => $slug,
                'sku' => 'HX-CL3W-RD',
                'category' => 'Tai nghe',
                'brand' => 'HyperX',
                'price' => 2990000,
                'sale_price' => 2490000,
                'rating' => '4.9',
                'reviews_count' => 128,
                'description' => 'Tai nghe chơi game không dây HyperX Cloud III kế thừa và nâng cấp toàn diện từ dòng Cloud II huyền thoại. Trang bị màng loa 53mm góc nghiêng tái tạo âm thanh chuẩn xác, đệm mút hoạt tính êm ái đặc trưng và thời lượng pin vượt trội lên tới 120 giờ chỉ sau một lần sạc.',
                'thumbnail' => $thumb,
                'gallery' => [
                    $thumb,
                    asset('images/hero-headphone.png'),
                    asset('images/images (1).png'),
                    asset('images/earphone-hyperx.png'),
                ],
                'stock_quantity' => 25,
                'specs' => [
                    'Thương hiệu' => 'HyperX',
                    'Model' => 'Cloud III Wireless',
                    'Chuẩn kết nối' => 'Không dây 2.4GHz / Jack 3.5mm / USB-C',
                    'Thời lượng pin' => 'Lên đến 120 giờ liên tục',
                    'Driver âm thanh' => 'Dynamic 53mm với nam châm Neodymium',
                    'Tần số phản hồi' => '10Hz – 21.000Hz',
                    'Microphone' => '10mm khử tiếng ồn hai chiều có thể tháo rời',
                    'Trọng lượng' => '330g (kèm microphone)',
                    'Chế độ bảo hành' => '24 tháng chính hãng, 1 đổi 1 trong 30 ngày',
                ],
            ];

            $relatedProducts = [
                [
                    'id' => 102,
                    'name' => 'Chuột Razer Basilisk V3 Pro Wireless',
                    'slug' => 'razer-basilisk-v3-pro',
                    'category' => 'Chuột',
                    'brand' => 'Razer',
                    'price' => 3890000,
                    'sale_price' => 3290000,
                    'thumbnail' => asset('images/mouse-razer.png'),
                    'rating' => '5.0',
                ],
                [
                    'id' => 103,
                    'name' => 'Bàn phím cơ không dây Keychron K2 Pro',
                    'slug' => 'keychron-k2-pro',
                    'category' => 'Bàn phím cơ',
                    'brand' => 'Keychron',
                    'price' => 2390000,
                    'sale_price' => null,
                    'thumbnail' => asset('images/keyboard-katana.png'),
                    'rating' => '4.9',
                ],
                [
                    'id' => 104,
                    'name' => 'Micro HyperX QuadCast S RGB USB',
                    'slug' => 'hyperx-quadcast-s',
                    'category' => 'Microphone',
                    'brand' => 'HyperX',
                    'price' => 3690000,
                    'sale_price' => 3290000,
                    'thumbnail' => asset('images/images (2).png'),
                    'rating' => '4.8',
                ],
                [
                    'id' => 105,
                    'name' => 'Chuột Gaming ATK F1 Ultimate Carbon',
                    'slug' => 'atk-f1-ultimate',
                    'category' => 'Chuột',
                    'brand' => 'ATK',
                    'price' => 1990000,
                    'sale_price' => 1690000,
                    'thumbnail' => asset('images/mouse-atk.png'),
                    'rating' => '4.7',
                ],
            ];
        }

        return view('client.shop.show', compact('product', 'relatedProducts'));
    }
}
