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
        // 6 Featured Categories Grid matching Figma Page 1
        $featuredCategories = [
            [
                'title' => 'Enjoy With',
                'name' => 'EARPHONE',
                'slug' => 'earphone',
                'image' => asset('images/images (1).png'),
                'bg' => 'bg-[#212121]',
                'btnColor' => 'bg-white text-gray-900',
            ],
            [
                'title' => 'New',
                'name' => 'Wireless MOUSE',
                'slug' => 'mouse',
                'image' => asset('images/mouse-atk.png'),
                'bg' => 'bg-[#E58A32]',
                'btnColor' => 'bg-white text-gray-900',
            ],
            [
                'title' => 'Best',
                'name' => 'Gaming LAPTOP',
                'slug' => 'laptop',
                'image' => asset('images/laptop-gaming.png'),
                'bg' => 'bg-[#E5252A]',
                'btnColor' => 'bg-white text-gray-900',
            ],
            [
                'title' => 'New',
                'name' => 'Trend CONSOLE',
                'slug' => 'console',
                'image' => asset('images/images.png'),
                'bg' => 'bg-[#E4E4E7]',
                'isLight' => true,
                'btnColor' => 'bg-primary text-white',
            ],
            [
                'title' => 'Mechanical',
                'name' => 'Elite DECK',
                'slug' => 'keyboard',
                'image' => asset('images/keyboard-katana.png'),
                'bg' => 'bg-[#2563EB]',
                'btnColor' => 'bg-white text-gray-900',
            ],
            [
                'title' => 'New',
                'name' => 'HyperX SPEAKER',
                'slug' => 'speaker',
                'image' => asset('images/images (2).png'),
                'bg' => 'bg-[#10B981]',
                'btnColor' => 'bg-white text-gray-900',
            ],
        ];

        // 8 Best Seller Products matching Figma Page 1
        $bestSellerProducts = [
            [
                'id' => 1,
                'name' => 'HyperX Cloud',
                'slug' => 'hyperx-cloud',
                'price' => 1890000,
                'sale_price' => 1490000,
                'thumbnail' => asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png'),
                'category' => 'Headphones',
                'rating' => '4.8',
            ],
            [
                'id' => 2,
                'name' => 'Katana Marius 61',
                'slug' => 'katana-marius-61',
                'price' => 2190000,
                'sale_price' => null,
                'thumbnail' => asset('images/keyboard-katana.png'),
                'category' => 'Keyboard',
                'rating' => '4.9',
            ],
            [
                'id' => 3,
                'name' => 'Razer Viper V3 Pro',
                'slug' => 'razer-viper-v3-pro',
                'price' => 3890000,
                'sale_price' => 3290000,
                'thumbnail' => asset('images/mouse-razer.png'),
                'category' => 'Mouse',
                'rating' => '5.0',
            ],
            [
                'id' => 4,
                'name' => 'HyperX Cloud Earbuds 2',
                'slug' => 'hyperx-cloud-earbuds-2',
                'price' => 750000,
                'sale_price' => null,
                'thumbnail' => asset('images/images (1).png'),
                'category' => 'Earphone',
                'rating' => '4.7',
            ],
            [
                'id' => 5,
                'name' => 'Beats JBL Wireless',
                'slug' => 'beats-jbl-wireless',
                'price' => 2290000,
                'sale_price' => 1790000,
                'thumbnail' => asset('images/5b3fd7c2c7625.png'),
                'category' => 'Headphones',
                'rating' => '4.8',
            ],
            [
                'id' => 6,
                'name' => 'LCD LG MP59G Gaming',
                'slug' => 'lcd-lg-mp59g',
                'price' => 3490000,
                'sale_price' => null,
                'thumbnail' => asset('images/png-transparent-lcd-television-led-backlit-lcd-lg-mp59g-p-computer-monitors-lg-electronics-lg-television-angle-computer-monitor-accessory (1).png'),
                'category' => 'Monitor',
                'rating' => '4.6',
            ],
            [
                'id' => 7,
                'name' => 'HyperX Cloud Fighter 2',
                'slug' => 'hyperx-cloud-fighter-2',
                'price' => 2990000,
                'sale_price' => null,
                'thumbnail' => asset('images/hero-headphone.png'),
                'category' => 'Headphones',
                'rating' => '4.9',
            ],
            [
                'id' => 8,
                'name' => 'Asus ROG Zephyrus G14',
                'slug' => 'asus-rog-zephyrus',
                'price' => 44990000,
                'sale_price' => 39990000,
                'thumbnail' => asset('images/laptop-gaming.png'),
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
