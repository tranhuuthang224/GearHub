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
                'image' => asset('images/471-4713724_png-image-gaming-laptop-png-transparent-png.png'),
                'bg' => 'bg-gradient-to-br from-[#988D8C] via-[#483D3C] to-[#2E1E1E]',
                'subtitleColor' => 'text-white/80',
                'titleColor' => 'text-white',
                'keywordColor' => 'text-primary', // Vivid red keyword
                'btnColor' => 'bg-primary hover:bg-primary-hover text-white shadow-md shadow-primary/25',
                'colSpan' => 'sm:col-span-2 lg:col-span-2',
                'imgWrapper' => 'absolute -right-2 sm:right-0 -bottom-2 sm:-bottom-2 top-2 sm:top-1 w-[62%] sm:w-[58%] flex items-end justify-end pointer-events-none',
                'imageClass' => 'w-full h-full object-contain object-right-bottom drop-shadow-2xl scale-110 sm:scale-120 origin-bottom-right',
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
                'image' => asset('images/5b3fd7c2c7625.png'),
                'bg' => 'bg-gradient-to-br from-[#2C4750] via-[#41859B] to-[#182C34]',
                'subtitleColor' => 'text-white/80',
                'titleColor' => 'text-[#7DD3FC]', // Sky blue / cyan title
                'keywordColor' => 'text-[#10242D]',
                'btnColor' => 'bg-white text-gray-900 hover:bg-gray-100',
                'colSpan' => 'lg:col-span-1',
                'imgWrapper' => 'absolute -right-1 sm:right-1 bottom-1 sm:bottom-3 max-w-[62%] max-h-[88%] flex items-end justify-end pointer-events-none',
                'imageClass' => 'max-h-[180px] sm:max-h-[205px] w-auto object-contain',
            ],
            [
                'subtitle' => 'New',
                'title' => 'Hyperx',
                'keyword' => 'SPEAKER',
                'slug' => 'speaker',
                'image' => asset('images/images.png'),
                'bg' => 'bg-[#31A047]',
                'subtitleColor' => 'text-white/90',
                'titleColor' => 'text-white',
                'keywordColor' => 'text-[#4ADE80]', // Bright lime/neon green keyword
                'btnColor' => 'bg-white text-[#258237] hover:bg-gray-50',
                'colSpan' => 'lg:col-span-1',
                'imgWrapper' => 'absolute -right-1 sm:right-1 -bottom-2 sm:-bottom-1 top-3 sm:top-2 w-[60%] sm:w-[58%] flex items-end justify-center pointer-events-none',
                'imageClass' => 'w-full h-full object-contain rotate-[22deg] scale-125 sm:scale-135 origin-center drop-shadow-2xl',
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
