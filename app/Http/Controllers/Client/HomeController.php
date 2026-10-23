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
                'subtitle' => 'Enjoy',
                'title' => 'With',
                'keyword' => 'EARPHONE',
                'slug' => 'earphone',
                'image' => asset('images/earphone-hyperx.png'),
                'bg' => 'bg-[#282828]',
                'subtitleColor' => 'text-white/80',
                'titleColor' => 'text-white',
                'keywordColor' => 'text-white/20',
                'btnColor' => 'bg-white text-gray-900 hover:bg-gray-100',
                'colSpan' => 'lg:col-span-1',
                'imgWrapper' => 'absolute right-4 sm:right-7 top-2 bottom-1 max-w-[45%] flex items-center justify-center pointer-events-none',
                'imageClass' => 'h-[92%] sm:h-[96%] w-auto object-contain',
            ],
            [
                'subtitle' => 'New',
                'title' => 'Wireless',
                'keyword' => 'MOUSE',
                'slug' => 'mouse',
                'image' => asset('images/mouse-atk.png'),
                'bg' => 'bg-[#923232]',
                'subtitleColor' => 'text-white/80',
                'titleColor' => 'text-white',
                'keywordColor' => 'text-[#421515]',
                'btnColor' => 'bg-white text-gray-900 hover:bg-gray-100',
                'colSpan' => 'lg:col-span-1',
                'imgWrapper' => 'absolute -right-1 sm:right-2 bottom-3 sm:bottom-4 max-w-[55%] max-h-[85%] flex items-end justify-end pointer-events-none',
                'imageClass' => 'max-h-[160px] sm:max-h-[175px] w-auto object-contain',
            ],
            [
                'subtitle' => 'Best',
                'title' => 'Gaming',
                'keyword' => 'LAPTOP',
                'slug' => 'laptop',
                'image' => asset('images/Laptop/Laptop Asus Gaming ROG Strix G614PH-S5101W.webp'),
                'bg' => 'bg-gradient-to-br from-[#988D8C] via-[#483D3C] to-[#2E1E1E]',
                'subtitleColor' => 'text-white/80',
                'titleColor' => 'text-white',
                'keywordColor' => 'text-primary', // Vivid red keyword
                'btnColor' => 'bg-primary hover:bg-primary-hover text-white shadow-md shadow-primary/25',
                'colSpan' => 'sm:col-span-2 lg:col-span-2',
                'imgWrapper' => 'absolute right-0 sm:right-2 bottom-0 sm:bottom-1 max-w-[58%] max-h-[92%] flex items-end justify-end pointer-events-none',
                'imageClass' => 'max-h-[220px] sm:max-h-[260px] w-auto object-contain',
            ],
            [
                'subtitle' => 'New',
                'title' => 'Trend',
                'keyword' => 'CONSOLE',
                'slug' => 'console',
                'image' => asset('images/console-ps4.png'),
                'bg' => 'bg-[#DBDBDB]',
                'subtitleColor' => 'text-gray-900 font-semibold',
                'titleColor' => 'text-gray-900 font-bold',
                'keywordColor' => 'text-white drop-shadow-sm', // Pure white keyword on silver gray
                'btnColor' => 'bg-primary hover:bg-primary-hover text-white shadow-md shadow-primary/25',
                'colSpan' => 'sm:col-span-2 lg:col-span-2',
                'imgWrapper' => 'absolute right-2 sm:right-6 bottom-1 sm:bottom-2 max-w-[55%] max-h-[92%] flex items-end justify-end pointer-events-none',
                'imageClass' => 'max-h-[220px] sm:max-h-[250px] w-auto object-contain',
            ],
            [
                'subtitle' => 'Mechanical',
                'title' => 'Elite',
                'keyword' => 'DECK',
                'slug' => 'keyboard',
                'image' => asset('images/keyboard-katana.png'),
                'bg' => 'bg-gradient-to-br from-[#2C4750] via-[#41859B] to-[#182C34]',
                'subtitleColor' => 'text-white/80',
                'titleColor' => 'text-[#7DD3FC]', // Sky blue / cyan title
                'keywordColor' => 'text-[#10242D]',
                'btnColor' => 'bg-white text-gray-900 hover:bg-gray-100',
                'colSpan' => 'lg:col-span-1',
                'imgWrapper' => 'absolute -right-2 sm:right-1 bottom-1 sm:bottom-3 max-w-[60%] max-h-[85%] flex items-end justify-end pointer-events-none',
                'imageClass' => 'max-h-[175px] sm:max-h-[190px] w-auto object-contain',
            ],
            [
                'subtitle' => 'New',
                'title' => 'Hyperx',
                'keyword' => 'SPEAKER',
                'slug' => 'speaker',
                'image' => asset('images/images (2).png'),
                'bg' => 'bg-[#31A047]',
                'subtitleColor' => 'text-white/90',
                'titleColor' => 'text-white',
                'keywordColor' => 'text-[#4ADE80]', // Bright lime/neon green keyword
                'btnColor' => 'bg-white text-[#258237] hover:bg-gray-50',
                'colSpan' => 'lg:col-span-1',
                'imgWrapper' => 'absolute right-0 sm:right-3 bottom-0 sm:bottom-2 max-w-[55%] max-h-[92%] flex items-end justify-end pointer-events-none',
                'imageClass' => 'max-h-[200px] sm:max-h-[225px] w-auto object-contain',
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
                'thumbnail' => asset('images/HeadPhone/Gaming Logitech G ProX.webp'),
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
