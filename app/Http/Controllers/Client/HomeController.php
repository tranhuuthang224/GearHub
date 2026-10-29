<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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

        // 8 Best Seller Products matching Figma Page 1 & Product Catalog
        $bestSellerProducts = [
            [
                'id' => 401,
                'name' => 'HyperX Cloud III Wireless',
                'slug' => 'tai-nghe-hyperx-cloud-iii-wireless',
                'price' => 3490000,
                'sale_price' => 2990000,
                'thumbnail' => asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png'),
                'category' => 'Headphones',
                'rating' => '4.9',
            ],
            [
                'id' => 201,
                'name' => 'Aula F75 Gasket Mount',
                'slug' => 'ban-phim-co-aula-f75-gasket-mount',
                'price' => 1450000,
                'sale_price' => 1250000,
                'thumbnail' => asset('images/Keyboard/Aula F75.webp'),
                'category' => 'Keyboard',
                'rating' => '4.9',
            ],
            [
                'id' => 501,
                'name' => 'Razer Viper V3 Pro',
                'slug' => 'chuot-razer-viper-v3-pro',
                'price' => 3990000,
                'sale_price' => 3590000,
                'thumbnail' => asset('images/razer viper v3.png'),
                'category' => 'Mouse',
                'rating' => '5.0',
            ],
            [
                'id' => 408,
                'name' => 'ATK Horizon IEM Gaming',
                'slug' => 'tai-nghe-atk-horizon-iem',
                'price' => 590000,
                'sale_price' => 450000,
                'thumbnail' => asset('images/HeadPhone/ATK Iem Horizon 3.5mm - White.webp'),
                'category' => 'Earphone',
                'rating' => '4.8',
            ],
            [
                'id' => 403,
                'name' => 'Logitech G Pro X Wireless',
                'slug' => 'tai-nghe-logitech-g-pro-x-lightspeed',
                'price' => 3890000,
                'sale_price' => 3390000,
                'thumbnail' => asset('images/HeadPhone/Gaming Logitech G ProX.webp'),
                'category' => 'Headphones',
                'rating' => '4.8',
            ],
            [
                'id' => 209,
                'name' => 'Logitech G515 TKL Lightspeed',
                'slug' => 'ban-phim-co-logitech-g515-lightspeed',
                'price' => 3490000,
                'sale_price' => 2990000,
                'thumbnail' => asset('images/Keyboard/Logitech G515.webp'),
                'category' => 'Keyboard',
                'rating' => '4.9',
            ],
            [
                'id' => 504,
                'name' => 'Logitech GPX Magenta Pink',
                'slug' => 'chuot-logitech-g-pro-x-superlight-magenta',
                'price' => 3290000,
                'sale_price' => 2790000,
                'thumbnail' => asset('images/Mouse/Chuột game không dây Logitech Pro X Superlight Magenta.webp'),
                'category' => 'Mouse',
                'rating' => '4.9',
            ],
            [
                'id' => 301,
                'name' => 'Asus ROG Strix G16 i9 RTX 4070',
                'slug' => 'laptop-asus-rog-strix-g16',
                'price' => 49990000,
                'sale_price' => 44990000,
                'thumbnail' => asset('images/Laptop/Laptop Asus Gaming ROG Strix G614PH-S5101W.webp'),
                'category' => 'Laptop',
                'rating' => '5.0',
            ],
        ];

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
