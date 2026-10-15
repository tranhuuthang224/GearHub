<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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
        // Placeholder for single product detail view (Week 2/3)
        $product = [
            'id' => 101,
            'name' => 'HyperX Cloud III',
            'slug' => $slug,
            'category' => 'Headphones',
            'brand' => 'HyperX',
            'price' => 2990000,
            'sale_price' => 2490000,
            'rating' => '4.8',
            'reviews_count' => 126,
            'description' => 'Wired gaming headset with 53mm drivers, memory foam ear cushions and a detachable noise-cancelling microphone.',
            'thumbnail' => asset('images/hero-headphone.png'),
            'stock_quantity' => 40,
        ];

        return view('client.shop.show', compact('product'));
    }
}
