<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the GearHub Storefront Home page matching Figma.
     */
    public function index(): View
    {
        // 6 Featured Categories Grid matching Figma UI Screenshot exactly
        $featuredCategories = [
            [
                'title' => 'Enjoy With EARPHONE',
                'slug' => 'earphone',
                'banner' => 'images/1.png',
                'flex' => 'sm:flex-[5] sm:basis-0 sm:min-w-0',
            ],
            [
                'title' => 'New Wireless MOUSE',
                'slug' => 'mouse',
                'banner' => 'images/2.png',
                'flex' => 'sm:flex-[5] sm:basis-0 sm:min-w-0',
            ],
            [
                'title' => 'Best Gaming LAPTOP',
                'slug' => 'laptop',
                'banner' => 'images/3.png',
                'flex' => 'sm:flex-[8] sm:basis-0 sm:min-w-0',
            ],
            [
                'title' => 'New Trend CONSOLE',
                'slug' => 'console',
                'banner' => 'images/4.png',
                'flex' => 'sm:flex-[8] sm:basis-0 sm:min-w-0',
            ],
            [
                'title' => 'Mechanical Elite DECK',
                'slug' => 'keyboard',
                'banner' => 'images/5.png',
                'flex' => 'sm:flex-[5] sm:basis-0 sm:min-w-0',
            ],
            [
                'title' => 'New Hyperx SPEAKER',
                'slug' => 'speaker',
                'banner' => 'images/6.png',
                'flex' => 'sm:flex-[5] sm:basis-0 sm:min-w-0',
            ],
        ];

        // 8 Best Seller Products lấy trực tiếp từ Database để đảm bảo luôn khớp slug và link chi tiết
        $dbProducts = Product::query()
            ->active()
            ->with(['category', 'brand'])
            ->latest('id')
            ->take(8)
            ->get();

        if ($dbProducts->isNotEmpty()) {
            $bestSellerProducts = $dbProducts->map(function ($p) {
                return [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'slug'       => $p->slug,
                    'price'      => (int) $p->price,
                    'sale_price' => $p->sale_price ? (int) $p->sale_price : null,
                    'thumbnail'  => $p->thumbnail_url ?? asset($p->thumbnail ?? 'images/hero-headphone.png'),
                    'category'   => $p->category?->name ?? 'Gaming Gear',
                    'rating'     => '4.9',
                ];
            })->all();
        } else {
            // Fallback khi database chưa seed dữ liệu
            $bestSellerProducts = [];
        }

        // 4 Key Service Value Propositions matching Figma Page 1
        $services = [
            [
                'title' => 'Free Shipping',
                'desc' => 'Free Shipping On All Order',
                'icon' => 'truck',
            ],
            [
                'title' => 'Money Guarantee',
                'desc' => '30 Day On Back Guarantee',
                'icon' => 'shield',
            ],
            [
                'title' => 'Online Support 24/7',
                'desc' => 'Technical Support 24/7',
                'icon' => 'headset',
            ],
            [
                'title' => 'Secure Payment',
                'desc' => 'All Cards Accepted & COD',
                'icon' => 'credit-card',
            ],
        ];

        return view('client.home', compact('featuredCategories', 'bestSellerProducts', 'services'));
    }
}
