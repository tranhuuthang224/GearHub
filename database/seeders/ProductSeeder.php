<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::pluck('id', 'slug');
        $brandIds    = Brand::pluck('id', 'slug');

        foreach ($this->catalog() as [$category, $brand, $name, $sku, $price, $sale, $stock, $specs, $imagePath]) {
            $catSlug   = Str::slug($category);
            $brandSlug = Str::slug($brand);
            $slug      = Str::slug($name);

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id'    => $categoryIds[$catSlug] ?? 1,
                    'brand_id'       => $brandIds[$brandSlug] ?? 1,
                    'name'           => $name,
                    'slug'           => $slug,
                    'description'    => "{$name} chính hãng {$brand}. Phân phối chính hãng tại GearHub, bảo hành 12 - 24 tháng, hỗ trợ 1 đổi 1 trong 7 ngày.",
                    'specs'          => $specs,
                    'price'          => $price,
                    'sale_price'     => $sale,
                    'stock_quantity' => $stock,
                    'thumbnail'      => $imagePath,
                    'is_active'      => true,
                ]
            );

            // Cập nhật thư viện ảnh chi tiết
            $product->images()->delete();
            $product->images()->create([
                'path'       => $imagePath,
                'sort_order' => 1,
            ]);
        }
    }

    /**
     * Tự động phân loại sản phẩm thực tế vào danh mục con phù hợp
     */
    private function detectChildCategory(array $item): string
    {
        $cat = $item['category'];
        $name = mb_strtolower($item['name']);
        $desc = mb_strtolower($item['description'] ?? '');
        $text = $name . ' ' . $desc;

        if ($cat === 'Bàn phím cơ') {
            if (str_contains($text, 'không dây') || str_contains($text, 'wireless') || str_contains($text, '3-mode') || str_contains($text, 'multi-mode') || str_contains($text, 'bluetooth')) {
                return 'Bàn phím cơ không dây';
            }
            return 'Bàn phím cơ có dây';
        }

        if ($cat === 'Chuột') {
            if (str_contains($text, 'văn phòng') || str_contains($text, 'silent') || str_contains($text, 'office')) {
                return 'Chuột văn phòng';
            }
            return 'Chuột gaming';
        }

        if ($cat === 'Tai nghe') {
            if (str_contains($text, 'không dây') || str_contains($text, 'wireless') || str_contains($text, 'bluetooth') || str_contains($text, 'tws')) {
                return 'Tai nghe không dây';
            }
            return 'Tai nghe gaming';
        }

        if ($cat === 'Laptop') {
            if (str_contains($text, 'văn phòng') || str_contains($text, 'vivobook') || str_contains($text, 'swift') || str_contains($text, 'zenbook') || str_contains($text, 'thinkbook') || str_contains($text, 'ideapad') || str_contains($text, 'pavilion')) {
                return 'Laptop văn phòng';
            }
            return 'Laptop gaming';
        }

        if ($cat === 'Màn hình') {
            if (str_contains($text, 'đồ họa') || str_contains($text, 'viewfinity') || str_contains($text, 'ultrafine') || str_contains($text, 'pro plus') || str_contains($text, 'ultrawide') || str_contains($text, '4k')) {
                return 'Màn hình đồ họa';
            }
            if (str_contains($text, 'văn phòng') || str_contains($text, 'eye care') || str_contains($text, 'essential') || str_contains($text, 'thông minh') || str_contains($text, 'smart monitor') || str_contains($text, 'va27') || str_contains($text, 'p2726h')) {
                return 'Màn hình văn phòng';
            }
            return 'Màn hình gaming';
        }

        return $cat;
    }

    /**
     * [Danh mục con, Hãng, Tên sản phẩm, SKU, Giá gốc, Giá sale, Tồn kho, Specs, Ảnh]
     */
    private function catalog(): array
    {
        return [
            // ==================== BÀN PHÍM CƠ ====================
            [
                'Bàn phím cơ không dây', 'Aula', 'Bàn phím cơ Aula F75', 'KB-AULA-F75', 1250000, 990000, 45,
                ['Layout' => '75%', 'Kết nối' => 'Type-C / 2.4G / Bluetooth', 'Switch' => 'Reaper Switch', 'Keycap' => 'PBT Double-shot', 'Led' => 'RGB 16.8 triệu màu'],
                'images/Keyboard/Aula F75.webp'
            ],
            [
                'Bàn phím cơ không dây', 'Aula', 'Bàn phím cơ Aula S100 Pro', 'KB-AULA-S100P', 1450000, 1190000, 30,
                ['Layout' => '98 phím', 'Kết nối' => '3 Mode', 'Switch' => 'Custom Linear', 'Pin' => '4000mAh'],
                'images/Keyboard/Aula S100 Pro.webp'
            ],
            [
                'Bàn phím cơ không dây', 'Aula', 'Bàn phím cơ Aula L99 3-Mode Raindrop', 'KB-AULA-L99', 1690000, 1390000, 25,
                ['Layout' => '99 phím', 'Kết nối' => 'Type-C / 2.4G / BT', 'Switch' => 'Raindrop Switch', 'Cấu trúc' => 'Gasket Mount'],
                'images/Keyboard/ban-phim-co-aula-l99-3-mode-den-xam-vang-raindrop-switch-1.webp'
            ],
            [
                'Bàn phím cơ có dây', 'Akko', 'Bàn phím cơ AKKO 3087', 'KB-AKKO-3087', 1290000, 1090000, 20,
                ['Layout' => 'TKL 87 phím', 'Kết nối' => 'Type-C rời', 'Switch' => 'Akko CS v2', 'Keycap' => 'PBT Side-printed'],
                'images/Keyboard/AKKO 3087.webp'
            ],
            [
                'Bàn phím cơ có dây', 'Akko', 'Bàn phím cơ Akko 3098', 'KB-AKKO-3098', 1590000, 1350000, 20,
                ['Layout' => '98 phím', 'Kết nối' => 'Type-C', 'Switch' => 'Akko Orange / Pink', 'Tính năng' => 'Hotswap 5-pin'],
                'images/Keyboard/Akko 3098.webp'
            ],
            [
                'Bàn phím cơ có dây', 'Asus', 'Bàn phím cơ Asus TUF Gaming K3', 'KB-ASUS-K3', 1890000, 1590000, 15,
                ['Layout' => 'Fullsize 104 phím', 'Switch' => 'TUF Optical-Mech', 'Kê tay' => 'Có nam châm đi kèm', 'Khung' => 'Hợp kim nhôm'],
                'images/Keyboard/Asus Tuf K3.webp'
            ],
            [
                'Bàn phím cơ không dây', 'ATK', 'Bàn phím cơ ATK VXE V75X', 'KB-ATK-V75X', 2190000, 1890000, 18,
                ['Layout' => '75%', 'Switch' => 'Magnetic Switch Rapid Trigger', 'Độ trễ' => '0.1ms cực thấp', 'Kết nối' => '3 Mode'],
                'images/Keyboard/ATK VXE V75X.webp'
            ],
            [
                'Bàn phím cơ không dây', 'DareU', 'Bàn phím cơ DareU EK98 Pro Mystic Violet', 'KB-DAREU-EK98P', 1190000, 950000, 35,
                ['Layout' => '98 phím', 'Switch' => 'DareU Dream Switch', 'Màu sắc' => 'Mystic Violet', 'Màn hình' => 'Tích hợp TFT LCD'],
                'images/Keyboard/DareU EK98 Pro Mystic Violet.webp'
            ],
            [
                'Bàn phím cơ không dây', 'E-Dra', 'Bàn phím cơ E-Dra EK398W', 'KB-EDRA-EK398W', 950000, 790000, 40,
                ['Layout' => '98 phím', 'Switch' => 'E-Dra Custom', 'Kết nối' => 'Wireless 2.4G + Bluetooth', 'Pin' => 'Li-ion bền bỉ'],
                'images/Keyboard/EDRA EK398W.webp'
            ],
            [
                'Bàn phím cơ có dây', 'Fuhlen', 'Bàn phím cơ Fuhlen Eraser', 'KB-FUHLEN-ERASER', 850000, 690000, 25,
                ['Layout' => 'Fullsize 104 phím', 'Switch' => 'Quang cơ chống nước', 'Đèn led' => 'Rainbow đa hiệu ứng'],
                'images/Keyboard/Fuhlen Erarser.webp'
            ],
            [
                'Bàn phím cơ không dây', 'Keychron', 'Bàn phím cơ Keychron Q3 Pro Brown Switch', 'KB-KC-Q3P', 4390000, 3990000, 10,
                ['Layout' => 'TKL', 'Khung' => 'Nhôm CNC nguyên khối', 'Switch' => 'Keychron K Pro Brown', 'Kết nối' => 'Bluetooth 5.1 / Type-C'],
                'images/Keyboard/Keychron Q3U-P3 Brown Switch.webp'
            ],
            [
                'Bàn phím cơ có dây', 'Logitech', 'Bàn phím cơ Logitech G413 Carbon', 'KB-LOGI-G413', 1590000, 1390000, 15,
                ['Layout' => 'Fullsize', 'Switch' => 'Romer-G Tactile', 'Chất liệu' => 'Vỏ hợp kim nhôm 5052 hàng không'],
                'images/Keyboard/Logitech G413.webp'
            ],
            [
                'Bàn phím cơ không dây', 'Logitech', 'Bàn phím cơ Logitech G515 Lightspeed TKL', 'KB-LOGI-G515', 2990000, 2690000, 12,
                ['Layout' => 'Low profile TKL', 'Độ mỏng' => '22mm siêu gọn', 'Kết nối' => 'Lightspeed Wireless / Bluetooth'],
                'images/Keyboard/Logitech G515.webp'
            ],
            [
                'Bàn phím cơ không dây', 'MChose', 'Bàn phím cơ MChose G75 Pro Arctic Snow', 'KB-MCHOSE-G75P', 1890000, 1590000, 22,
                ['Layout' => '75%', 'Màu sắc' => 'Arctic Snow trắng tinh khôi', 'Switch' => 'TTC Iron Linear', 'Cấu trúc' => 'Gasket 5 lớp tiêu âm'],
                'images/Keyboard/MChose G75 Pro - Arctic Snow.webp'
            ],
            [
                'Bàn phím cơ có dây', 'Rapoo', 'Bàn phím cơ Rapoo V520 Chống nước', 'KB-RAPOO-V520', 650000, 520000, 30,
                ['Layout' => 'Fullsize', 'Switch' => 'Optical Switch độ bền cao', 'Đặc điểm' => 'Chống tràn nước và bụi bẩn'],
                'images/Keyboard/Rapoo V520.webp'
            ],

            // ==================== CHUỘT ====================
            [
                'Chuột gaming', 'Logitech', 'Chuột Logitech G Pro X Superlight Magenta', 'MS-LOGI-GPX-MG', 2990000, 2590000, 20,
                ['Cảm biến' => 'HERO 25K', 'Trọng lượng' => '<63g siêu nhẹ', 'Kết nối' => 'Lightspeed Wireless', 'Màu' => 'Magenta Hồng cánh sen'],
                'images/Mouse/Chuột game không dây Logitech Pro X Superlight Magenta.webp'
            ],
            [
                'Chuột gaming', 'Logitech', 'Chuột chơi game Logitech G403 Hero', 'MS-LOGI-G403H', 990000, 850000, 30,
                ['Cảm biến' => 'HERO 25K', 'DPI' => '100 - 25,600', 'Thiết kế' => 'Công thái học tay phải', 'Tạ tăng trọng lượng' => '10g tháo rời'],
                'images/Mouse/Chuột game Logitech G403 Hero.webp'
            ],
            [
                'Chuột gaming', 'HyperX', 'Chuột HyperX Pulsefire Surge RGB', 'MS-HX-PFSURGE', 890000, 690000, 25,
                ['Cảm biến' => 'Pixart 3389', 'DPI' => 'Tối đa 16000', 'Led' => 'Vòng ánh sáng RGB 360 độ', 'Switch' => 'Omron 50 triệu click'],
                'images/Mouse/Chuột gaming HyperX Pulsefire Surge RGB - Likenew (Full box).webp'
            ],
            [
                'Chuột gaming', 'Logitech', 'Chuột không dây Logitech G Pro X2 Superstrike', 'MS-LOGI-GPX2', 3690000, 3290000, 15,
                ['Cảm biến' => 'HERO 2 32000 DPI', 'Tần số quét' => 'Polling Rate 4K/8K', 'Switch' => 'Lightforce Hybrid'],
                'images/Mouse/Chuột Gaming không dây Logitech Pro X2 Superstrike (910-007778).webp'
            ],
            [
                'Chuột gaming', 'ATK', 'Chuột Gaming VXE V3 Pro Wireless Black', 'MS-ATK-V3PRO', 1190000, 990000, 35,
                ['Cảm biến' => 'PAW3395 flagship', 'Trọng lượng' => '49g siêu nhẹ', 'Pin' => 'Lên tới 100 giờ liên tục'],
                'images/Mouse/Chuột Gaming VXE V3 Pro Wireless - Black.webp'
            ],
            [
                'Chuột gaming', 'ATK', 'Chuột không dây ATK U2 Ultimate', 'MS-ATK-U2ULT', 1590000, 1390000, 20,
                ['Cảm biến' => 'PAW3950 Ultra', 'MCU' => 'Nordic 52840', 'Polling Rate' => 'Không dây 8K mượt mà'],
                'images/Mouse/Chuột không dây ATK U2 Ultimate.webp'
            ],
            [
                'Chuột gaming', 'Logitech', 'Chuột Logitech G Pro X3 Superstrike Đen', 'MS-LOGI-GPX3', 3990000, 3590000, 10,
                ['Cảm biến' => 'HERO thế hệ mới', 'Switch' => 'Optical Switch bền bỉ', 'Trọng lượng' => '58g cân bằng tuyệt đối'],
                'images/Mouse/Chuột không dây Logitech G Pro X3 Superstrike - Màu Đen (910-007880).webp'
            ],
            [
                'Chuột gaming', 'Logitech', 'Chuột không dây Logitech Pro X Superlight 2C', 'MS-LOGI-GPX2C', 3490000, 3190000, 18,
                ['Cảm biến' => 'HERO 2', 'Cổng sạc' => 'USB-C chuẩn mới', 'Thời lượng pin' => '95 giờ'],
                'images/Mouse/Chuột không dây Logitech Pro X Superlight 2C.webp'
            ],
            [
                'Chuột gaming', 'Razer', 'Chuột không dây Razer DeathAdder V4 Pro', 'MS-RZ-DAV4P', 3890000, 3490000, 12,
                ['Cảm biến' => 'Focus Pro 35K Gen-2', 'Switch' => 'Gen-3 Optical 90M click', 'Trọng lượng' => '55g công thái học đỉnh cao'],
                'images/Mouse/Chuột không dây Razer DeathAdder V4 Pro.webp'
            ],
            // ----- CHUỘT PHỔ THÔNG & VĂN PHÒNG -----
            [
                'Chuột văn phòng', 'Fuhlen', 'Chuột có dây Fuhlen L102 Black', 'MS-FUHLEN-L102', 150000, 120000, 80,
                ['Cảm biến' => 'Optical 1000 DPI', 'Kết nối' => 'USB 2.0 dây dài 1.5m', 'Thiết kế' => 'Đối xứng nhỏ gọn, công thái học văn phòng', 'Độ bền' => '5 triệu lượt click'],
                'images/Mouse/fuhlen_l102.webp'
            ],
            [
                'Chuột gaming', 'DareU', 'Chuột chơi game DareU LM103 Black RGB', 'MS-DAREU-LM103', 180000, 149000, 50,
                ['DPI' => '1000 - 1600 DPI', 'Led' => 'RGB 7 màu tự đổi', 'Kết nối' => 'USB có dây bọc dù', 'Thiết kế' => 'Đối xứng chống trơn trượt'],
                'images/Mouse/54067_chuot_choi_game_dareu_lm103_black_usb_rgb_den.webp'
            ],
            [
                'Chuột văn phòng', 'DareU', 'Chuột máy tính DareU LM130S Đen', 'MS-DAREU-LM130S', 190000, 159000, 45,
                ['DPI' => '800 - 1600 DPI', 'Kết nối' => 'USB có dây', 'Trọng lượng' => '85g', 'Màu sắc' => 'Đen nhám chống bám vân tay'],
                'images/Mouse/63917_chuot_dareu_lm130s_den_usb_0001_2.webp'
            ],
            [
                'Chuột gaming', 'E-Dra', 'Chuột Gaming có dây E-Dra EM6102 Pro Đen', 'MS-EDRA-EM6102P', 290000, 230000, 40,
                ['Cảm biến' => 'SPCP6653', 'DPI' => 'Tối đa 6400 DPI', 'Led' => 'RGB nhiều chế độ', 'Switch' => 'Huano 10 triệu click'],
                'images/Mouse/71752_chuot_gaming_co_day_edra_em6102_pro_mau_den_0001_2.webp'
            ],
            [
                'Chuột gaming', 'Razer', 'Chuột Razer DeathAdder Essential Ergonomic Trắng', 'MS-RZ-DAESS-W', 590000, 450000, 35,
                ['Cảm biến' => 'Optical 6400 DPI', 'Form' => 'Công thái học tay phải huyền thoại', 'Led' => 'Single White LED thanh lịch', 'Nút bấm' => '5 nút Hyperesponse lập trình'],
                'images/Mouse/64001_chuot_razer_deathadder_essential_ergonomic_trang_usb_led_white_1.webp'
            ],
            // ----- CHUỘT GAMING TẦM TRUNG & CAO CẤP MỚI BỔ SUNG -----
            [
                'Chuột gaming', 'Logitech', 'Chuột không dây Logitech G502 X Plus Lightspeed RGB Trắng', 'MS-LOGI-G502XP-W', 3590000, 3190000, 15,
                ['Cảm biến' => 'HERO 25K', 'Switch' => 'Lightforce Hybrid Quang - Cơ', 'Led' => 'LIGHTSYNC RGB 8 vùng', 'Thời lượng pin' => '120 giờ'],
                'images/Mouse/69519_chuot_game_khong_day_logitech_g502x_rgb_plus_lightspeed_trang_910_006148_usb_0004_5.webp'
            ],
            [
                'Chuột gaming', 'Asus', 'Chuột Gaming Asus ROG Gladius IV Ace Black', 'MS-ASUS-GLAD4', 2890000, 2490000, 18,
                ['Cảm biến' => 'ROG AimPoint Pro 42K DPI', 'Kết nối' => 'SpeedNova Wireless / BT / Type-C', 'Switch' => 'ROG Optical 100M click', 'Trọng lượng' => '54g siêu nhẹ'],
                'images/Mouse/chuot-asus-rog-gladius-iv-ace-mau-den-90mp04r0-bmua00-1.webp'
            ],
            [
                'Chuột gaming', 'Asus', 'Chuột Asus ROG Harpe II Ace Màu Đỏ Gaming', 'MS-ASUS-HARPE2-R', 3490000, 3090000, 12,
                ['Cảm biến' => 'AimPoint Pro 42,000 DPI', 'Polling Rate' => 'True 8000Hz không dây', 'Trọng lượng' => '54g Bio-based Nylon', 'Màu sắc' => 'Đỏ thể thao nổi bật'],
                'images/Mouse/chuot-asus-rog-harpe-ii-ace-mau-do-90mp0490-bmua20-1.webp'
            ],
            [
                'Chuột gaming', 'ATK', 'Chuột Gaming ATK Ghost Extreme Wireless Black', 'MS-ATK-GHOST-B', 1790000, 1490000, 25,
                ['Cảm biến' => 'PAW3950 Ultra', 'Trọng lượng' => '42g Magnesium Alloy', 'MCU' => 'Nordic 52840', 'Polling Rate' => 'Hỗ trợ 8KHz'],
                'images/Mouse/chuot-gaming-atk-ghost-extreme-wireless-black.webp'
            ],
            [
                'Chuột gaming', 'ATK', 'Chuột Gaming ATK Ghost Extreme Wireless Purple', 'MS-ATK-GHOST-P', 1850000, 1550000, 20,
                ['Cảm biến' => 'PAW3950 Ultra', 'Trọng lượng' => '42g siêu nhẹ', 'Màu sắc' => 'Tím nhám Metallic', 'Switch' => 'Omron Optical'],
                'images/Mouse/chuot-gaming-atk-ghost-extreme-wireless-purple.webp'
            ],
            [
                'Chuột gaming', 'Dell', 'Chuột không dây Dell Alienware Pro Wireless Dark Side of the Moon', 'MS-DELL-AWPRO', 3290000, 2890000, 15,
                ['Cảm biến' => '26000 DPI / 650 IPS', 'Polling Rate' => '4000Hz Wireless / 8000Hz Wired', 'Thiết kế' => 'Chuẩn Esports tuyển thủ', 'Trọng lượng' => '<60g'],
                'images/Mouse/chuot-gaming-khong-day-dell-alienware-pro-dark-side-of-the-moon-1.webp'
            ],
            [
                'Chuột gaming', 'Zowie', 'Chuột gaming không dây ZOWIE EC3-DW Wireless', 'MS-ZOWIE-EC3DW', 3690000, 3390000, 16,
                ['Cảm biến' => '3395 Sensor', 'DPI' => '400 / 800 / 1600 / 3200', 'Đầu thu' => 'Enhanced Wireless Receiver', 'Form' => 'Công thái học EC huyền thoại'],
                'images/Mouse/chuot-gaming-khong-day-zowie-ec3-dw-mezo0043-5.webp'
            ],
            [
                'Chuột gaming', 'ATK', 'Chuột không dây ATK Blazing Sky Duckbill Màu Tím', 'MS-ATK-BLZSKY-P', 1390000, 1190000, 30,
                ['Cảm biến' => 'PAW3395', 'Kiểu dáng' => 'Duckbill mỏ vịt công thái học', 'Màu sắc' => 'Tím Pastel nổi bật', 'Kết nối' => 'Wireless 2.4G / Bluetooth / Type-C'],
                'images/Mouse/chuot-khong-day-atk-blazing-sky-duckbill-mau-tim.webp'
            ],
            [
                'Chuột gaming', 'Corsair', 'Chuột không dây Corsair Sabre v2 Pro Carbon Fiber', 'MS-CS-SABREV2-CF', 2790000, 2390000, 15,
                ['Cảm biến' => 'CORSAIR MARKSMAN 26K DPI', 'Vỏ' => 'Vân Carbon siêu cứng cáp', 'Công nghệ' => 'SLIPSTREAM WIRELESS <1ms', 'Trọng lượng' => '69g'],
                'images/Mouse/chuot-khong-day-corsair-sabre-v2-pro-cf-carbon-1.webp'
            ],
            [
                'Chuột gaming', 'Corsair', 'Chuột không dây Corsair Sabre v2 Pro Magnesium White', 'MS-CS-SABREV2-W', 2890000, 2490000, 18,
                ['Cảm biến' => 'MARKSMAN 26,000 DPI', 'Chất liệu' => 'Magnesium Alloy Trắng Ngọc Trai', 'Switch' => 'CORSAIR QUICKSTRIKE nút bấm siêu tốc'],
                'images/Mouse/chuot-khong-day-corsair-sabre-v2-pro-mg-white-1.webp'
            ],
            [
                'Chuột gaming', 'Glorious', 'Chuột không dây Glorious Model O 3 Black', 'MS-GLOR-MODO3-B', 2190000, 1890000, 22,
                ['Cảm biến' => 'BAMF 2.0 26K Sensor', 'Thiết kế' => 'Lưng tổ ong thoáng khí thoát mồ hôi', 'Trọng lượng' => '58g', 'Thời lượng pin' => 'Lên tới 110 giờ'],
                'images/Mouse/chuot-khong-day-glorious-model-o-3-black-1.webp'
            ],

            // ==================== TAI NGHE ====================
            [
                'Tai nghe gaming', 'ATK', 'Tai nghe In-Ear ATK IEM Horizon 3.5mm White', 'HP-ATK-HRZW', 790000, 620000, 40,
                ['Driver' => 'Dual Dynamic Driver', 'Chuẩn cắm' => 'Jack 3.5mm mạ vàng', 'Đặc tính' => 'Chuyên dụng định vị bước chân game FPS'],
                'images/HeadPhone/ATK Iem Horizon 3.5mm - White.webp'
            ],
            [
                'Tai nghe gaming', 'Corsair', 'Tai nghe Corsair HS35 v3 Carbon', 'HP-CS-HS35V3', 1190000, 950000, 25,
                ['Driver' => 'Neodymium 50mm tùy chỉnh', 'Micro' => 'Đơn hướng lọc tiếng ồn', 'Đệm tai' => 'Memory foam êm ái'],
                'images/HeadPhone/Corsair HS35 v3 Carbon.webp'
            ],
            [
                'Tai nghe gaming', 'Corsair', 'Tai nghe Corsair HS55 Stereo White', 'HP-CS-HS55W', 1490000, 1190000, 20,
                ['Trọng lượng' => '273g siêu nhẹ', 'Driver' => '50mm', 'Micro' => 'Gập lên để tắt tiếng tiện lợi'],
                'images/HeadPhone/Corsair HS55 Stereo White.webp'
            ],
            [
                'Tai nghe gaming', 'DareU', 'Tai nghe DAREU EH925 MAX Black Red', 'HP-DAREU-EH925', 990000, 790000, 30,
                ['Hiệu ứng âm thanh' => 'Giả lập 7.1 Magic Voice', 'Led' => 'RGB Flowing', 'Khung kim loại' => 'Chắc chắn, bền bỉ'],
                'images/HeadPhone/DAREU EH925 MAX BLACK RED.webp'
            ],
            [
                'Tai nghe gaming', 'Logitech', 'Tai nghe Logitech G Pro X Gaming', 'HP-LOGI-GPROX', 2890000, 2450000, 15,
                ['Công nghệ Micro' => 'BLUE VO!CE lọc ồn chuẩn studio', 'Âm thanh' => 'DTS Headphone:X 2.0 vòm 7.1', 'Màng loa' => 'PRO-G 50mm'],
                'images/HeadPhone/Gaming Logitech G ProX.webp'
            ],
            [
                'Tai nghe không dây', 'Havit', 'Tai nghe Open-Ear Havit OWS929 Black', 'HP-HAVIT-OWS929', 850000, 680000, 35,
                ['Thiết kế' => 'Open-ear truyền âm khí bảo vệ thính giác', 'Bluetooth' => '5.3 kết nối ổn định', 'Kháng nước' => 'IPX5'],
                'images/HeadPhone/Havit OWS929 - Black.webp'
            ],
            [
                'Tai nghe gaming', 'HyperX', 'Tai nghe HyperX Cloud Alpha 2 Wireless', 'HP-HX-CLDALP2', 4290000, 3790000, 10,
                ['Thời lượng pin' => '300 giờ kỷ lục', 'Driver' => 'Dual Chamber 50mm', 'Âm thanh' => 'DTS Spatial Audio'],
                'images/HeadPhone/HyperX Cloud Alpha 2 Wireless.webp'
            ],
            [
                'Tai nghe không dây', 'MChose', 'Tai nghe MChose V9 Turbo Wireless White Gold', 'HP-MCHOSE-V9T', 1690000, 1390000, 20,
                ['Kết nối' => '2.4GHz độ trễ siêu thấp + Bluetooth 5.3', 'Thời lượng pin' => 'Lên đến 50 giờ', 'Màu' => 'Trắng viền vàng phong cách'],
                'images/HeadPhone/MChose V9 Turbo Wireless (White Gold).webp'
            ],
            [
                'Tai nghe không dây', 'Razer', 'Tai nghe Razer Barracuda X Chroma Phantom White', 'HP-RZ-BARRAX', 3190000, 2790000, 12,
                ['Kết nối kép' => 'SmartSwitch Type-C 2.4GHz + BT', 'Led' => 'Chroma RGB đa vùng', 'Driver' => 'TriForce 40mm'],
                'images/HeadPhone/Razer Barracuda X Chroma - Phantom White Edition.webp'
            ],
            [
                'Tai nghe gaming', 'Razer', 'Tai nghe Razer BlackShark V2 HyperSpeed', 'HP-RZ-BSV2HS', 3290000, 2890000, 15,
                ['Micro' => 'HyperClear Super Wideband', 'Trọng lượng' => 'Chỉ 280g', 'Âm thanh' => 'THX Spatial Audio chuyên sâu'],
                'images/HeadPhone/Razer BlackShark V2 HyperSpeed.webp'
            ],
            [
                'Tai nghe gaming', 'Razer', 'Tai nghe Razer BlackShark V3 Pro 2XKO Edition', 'HP-RZ-BSV3P', 4990000, 4490000, 8,
                ['Phiên bản' => 'Special 2XKO Edition đối kháng đỉnh cao', 'Driver' => 'TriForce Titanium 50mm', 'Đệm tai' => 'Gel làm mát'],
                'images/HeadPhone/Razer BlackShark V3 Pro 2XKO Edition.webp'
            ],
            [
                'Tai nghe gaming', 'Razer', 'Tai nghe Earphone Razer Hammerhead V3', 'HP-RZ-HMH3', 1290000, 990000, 30,
                ['Driver' => '10mm Dynamic Driver', 'Dây cáp' => 'Dẹt chống rối kèm micro thoại', 'Chuẩn cắm' => '3.5mm tương thích đa nền tảng'],
                'images/HeadPhone/Razer Hammerhead V3.webp'
            ],
            [
                'Tai nghe gaming', 'Zidii', 'Tai nghe Zidii LH1 Ultimate Gaming', 'HP-ZIDII-LH1', 690000, 550000, 35,
                ['Led' => 'RGB Breathing', 'Đệm mút' => 'Da PU thoáng khí', 'Âm bass' => 'Tăng cường âm trầm uy lực'],
                'images/HeadPhone/Zidii LH1 Ultimate.webp'
            ],

            // ==================== LAPTOP ====================
            [
                'Laptop văn phòng', 'Acer', 'Laptop Acer Aspire 7 A715-59G-59RD', 'LT-ACER-A715', 18990000, 16990000, 10,
                ['CPU' => 'Intel Core i5-12450H', 'RAM' => '16GB DDR4', 'SSD' => '512GB PCIe NVMe', 'VGA' => 'RTX 2050 4GB', 'Màn hình' => '15.6 inch FHD 144Hz'],
                'images/Laptop/Laptop Acer Aspire 7 A715-59G-59RD.webp'
            ],
            [
                'Laptop gaming', 'Acer', 'Laptop Acer Predator Helios Neo PHN16', 'LT-ACER-NEO16', 38990000, 35490000, 6,
                ['CPU' => 'Intel Core i7-14700HX', 'RAM' => '16GB DDR5 5600MHz', 'SSD' => '1TB Gen4', 'VGA' => 'RTX 4060 8GB 140W', 'Màn hình' => '16 inch 2.5K WQXGA 165Hz sRGB 100%'],
                'images/Laptop/Laptop Acer Predator Helios PHN16-I31-72XE (NH.U4RSV.001).webp'
            ],
            [
                'Laptop văn phòng', 'Acer', 'Laptop Acer Swift Air 14 Siêu mỏng nhẹ', 'LT-ACER-SFA14', 21990000, 19490000, 8,
                ['CPU' => 'Intel Core Ultra 5-125H', 'RAM' => '16GB LPDDR5X', 'SSD' => '512GB', 'Màn hình' => '14 inch OLED 2.8K 90Hz', 'Trọng lượng' => '1.2kg'],
                'images/Laptop/Laptop Acer Swift Air 14 SFA14-I31-54A2.webp'
            ],
            [
                'Laptop gaming', 'Asus', 'Laptop Asus ROG Strix G16 G614PH', 'LT-ASUS-G614PH', 42990000, 39990000, 5,
                ['CPU' => 'Intel Core i9-13980HX', 'RAM' => '16GB DDR5', 'SSD' => '1TB', 'VGA' => 'RTX 4060 8GB', 'Màn hình' => '16 inch FHD+ 165Hz', 'Tản nhiệt' => 'Kim loại lỏng Conductonaut Extreme'],
                'images/Laptop/Laptop Asus Gaming ROG Strix G614PH-S5101W.webp'
            ],
            [
                'Laptop gaming', 'Asus', 'Laptop Asus TUF Gaming A16 FA608UM', 'LT-ASUS-FA608', 32990000, 29990000, 7,
                ['CPU' => 'AMD Ryzen 9 8945HS', 'RAM' => '16GB DDR5', 'SSD' => '512GB', 'VGA' => 'RTX 4060 8GB', 'Chuẩn độ bền' => 'Quân đội Mỹ MIL-STD-810H'],
                'images/Laptop/Laptop Asus Gaming TUF FA608UM-RV266W.webp'
            ],
            [
                'Laptop gaming', 'Asus', 'Laptop Asus TUF Gaming F16 FX610JH', 'LT-ASUS-FX610', 25990000, 23490000, 10,
                ['CPU' => 'Intel Core i7-13620H', 'RAM' => '16GB DDR5', 'SSD' => '512GB', 'VGA' => 'RTX 4050 6GB', 'Màn hình' => '16 inch 165Hz viền mỏng'],
                'images/Laptop/Laptop Asus Gaming TUF FX610JH-TU009W.webp'
            ],
            [
                'Laptop gaming', 'Asus', 'Laptop Asus Gaming V3607VJ', 'LT-ASUS-V3607', 22990000, 20490000, 9,
                ['CPU' => 'Intel Core i5-13420H', 'RAM' => '16GB', 'SSD' => '512GB', 'VGA' => 'RTX 3050 6GB', 'Màn hình' => '15.6 inch 144Hz IPS'],
                'images/Laptop/Laptop Asus Gaming V3607VJ-TK189W.webp'
            ],
            [
                'Laptop văn phòng', 'Asus', 'Laptop Asus Vivobook Pro 15 K3605VC', 'LT-ASUS-K3605', 24990000, 22490000, 8,
                ['CPU' => 'Intel Core i9-13900H', 'RAM' => '16GB', 'SSD' => '512GB', 'VGA' => 'RTX 3050', 'Thiết kế' => 'Thời trang văn phòng tích hợp đồ họa'],
                'images/Laptop/Laptop Asus Gaming Vivobook K3605VC-RP431W.webp'
            ],
            [
                'Laptop gaming', 'Gigabyte', 'Laptop Gigabyte Gaming A16 CTH', 'LT-GIGA-A16', 26990000, 23990000, 7,
                ['CPU' => 'AMD Ryzen 7 7735HS', 'RAM' => '16GB DDR5', 'SSD' => '512GB Gen4', 'VGA' => 'RTX 4050 6GB', 'Màn hình' => '16 inch 165Hz tỷ lệ 16:10'],
                'images/Laptop/Laptop Gigabyte Gaming A16 (A16-CTHH3VN893SH).webp'
            ],
            [
                'Laptop gaming', 'Lenovo', 'Laptop Lenovo Legion Pro 7 FIFA Edition', 'LT-LENOVO-LEG7', 59990000, 54990000, 3,
                ['CPU' => 'Intel Core i9-14900HX', 'RAM' => '32GB DDR5 5600', 'SSD' => '2TB PCIe Gen4', 'VGA' => 'RTX 4080 12GB 175W', 'Màn hình' => '16 inch WQXGA 240Hz 500 nits HDR'],
                'images/Laptop/Laptop Lenovo Gaming Legion Pro 7 FIFA World Cup 26 Edition.webp'
            ],
            [
                'Laptop gaming', 'Lenovo', 'Laptop Lenovo Gaming LOQ 15 IRX11', 'LT-LENOVO-LOQ15', 23990000, 21490000, 12,
                ['CPU' => 'Intel Core i5-13450HX', 'RAM' => '16GB DDR5', 'SSD' => '512GB', 'VGA' => 'RTX 4050 6GB 95W', 'Màn hình' => '15.6 inch 144Hz 100% sRGB'],
                'images/Laptop/Laptop Lenovo Gaming LOQ IRX11 83SC003RVN.webp'
            ],
            [
                'Laptop gaming', 'MSI', 'Laptop MSI Cyborg 15 A13UC Translucent', 'LT-MSI-CY15', 19990000, 17990000, 11,
                ['CPU' => 'Intel Core i5-13420H', 'RAM' => '16GB DDR5', 'SSD' => '512GB', 'VGA' => 'RTX 3050 6GB', 'Thiết kế' => 'Vỏ xuyên thấu Cyberpunk độc đáo'],
                'images/Laptop/Laptop MSI Cyborg 15 A13UC-2088VN.webp'
            ],
            [
                'Laptop gaming', 'MSI', 'Laptop MSI Gaming Katana 15 B13VEK', 'LT-MSI-KAT15', 27990000, 24990000, 8,
                ['CPU' => 'Intel Core i7-13620H', 'RAM' => '16GB DDR5', 'SSD' => '1TB', 'VGA' => 'RTX 4050 6GB', 'Bàn phím' => 'Led 4 vùng RGB phím WASD nổi bật'],
                'images/Laptop/Laptop MSI Gaming Katana 15 B13VEK-2440VN.webp'
            ],
            [
                'Laptop gaming', 'MSI', 'Laptop MSI Katana 15 HX B14WEK', 'LT-MSI-KAT15HX', 33990000, 30990000, 6,
                ['CPU' => 'Intel Core i7-14650HX', 'RAM' => '16GB DDR5', 'SSD' => '1TB NVMe', 'VGA' => 'RTX 4060 8GB', 'Màn hình' => '15.6 inch QHD 165Hz 100% DCI-P3'],
                'images/Laptop/Laptop MSI Katana 15 HX (B14WEK-027VN).webp'
            ],
            [
                'Laptop văn phòng', 'HP', 'Laptop Workstation HP ZBook 15 G5', 'LT-HP-ZB15', 17500000, 15500000, 7,
                ['CPU' => 'Intel Core i7-8850H vPro', 'RAM' => '32GB DDR4 ECC', 'SSD' => '512GB NVMe', 'VGA' => 'NVIDIA Quadro P2000 4GB', 'Đặc tính' => 'Máy trạm đồ họa bền bỉ'],
                'images/Laptop/Laptop Workstation HP Zbook 15 G5 3AX12AV.webp'
            ],
            [
                'Laptop văn phòng', 'Lenovo', 'Laptop Lenovo Yoga Slim 7 Siêu mỏng nhẹ', 'LT-LENOVO-YOGA7', 26990000, 23990000, 9,
                ['CPU' => 'Intel Core Ultra 7-155H AI', 'RAM' => '32GB LPDDR5X', 'SSD' => '1TB', 'Màn hình' => '14.5 inch 3K PureSight 120Hz', 'Khung máy' => 'Nhôm cao cấp siêu nhẹ'],
                'images/Laptop/Lenovo Yoga Slim 7 14LL10.webp'
            ],

            // ==================== MÀN HÌNH MÁY TÍNH ====================
            [
                'Màn hình văn phòng', 'Dahua', 'Màn hình Dahua DHI-LM27-E241B 27 inch 100Hz', 'MH-DH-LM27', 2390000, 2090000, 20,
                ['Kích thước' => '27 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '100Hz', 'Tấm nền' => 'IPS', 'Thời gian phản hồi' => '1ms'],
                'images/Monitor/62241_man_hinh_dahua_dhi_lm27_e241b_3.webp'
            ],
            [
                'Màn hình đồ họa', 'Samsung', 'Màn hình đồ họa Samsung ViewFinity S8 LS32B800P 32 inch 4K UHD', 'MH-SS-VF-S8', 13990000, 12490000, 10,
                ['Kích thước' => '32 inch', 'Độ phân giải' => '4K UHD (3840x2160)', 'Tấm nền' => 'IPS Chống chói Matte Display', 'Độ phủ màu' => '98% DCI-P3, HDR600', 'Cổng kết nối' => 'Type-C 90W, HDMI, DP, LAN'],
                'images/Monitor/69884_man_hinh_samsung_viewfinity_ls32b800p_850x850_3.webp'
            ],
            [
                'Màn hình đồ họa', 'Samsung', 'Màn hình cong Samsung ViewFinity S5 LS34C500 34 inch Ultra WQHD', 'MH-SS-S5-34', 7990000, 6890000, 15,
                ['Kích thước' => '34 inch Cong 1000R', 'Độ phân giải' => 'Ultra WQHD (3440x1440)', 'Tỉ lệ' => '21:9', 'Tần số quét' => '100Hz', 'HDR' => 'HDR10'],
                'images/Monitor/73240_man_hinh_samsung_ls34c500gaexxv_1.webp'
            ],
            [
                'Màn hình văn phòng', 'ViewSonic', 'Màn hình ViewSonic VA3209U-4K 32 inch UHD IPS Type-C', 'MH-VS-VA3209U', 6990000, 6290000, 18,
                ['Kích thước' => '32 inch', 'Độ phân giải' => '4K UHD (3840x2160)', 'Tấm nền' => 'SuperClear IPS', 'Cổng kết nối' => 'USB-C 65W, HDMI 2.0, DP'],
                'images/Monitor/80703_man_hinh_viewsonic_va3209u_4k_850x850_7.webp'
            ],
            [
                'Màn hình gaming', 'LG', 'Màn hình Gaming LG UltraGear OLED 32GS95UV-B 32 inch 4K 240Hz / FHD 480Hz', 'MH-LG-32GS95UV', 34990000, 31990000, 5,
                ['Kích thước' => '31.5 inch', 'Tấm nền' => 'WOLED Dual-Mode', 'Tần số quét' => '240Hz (4K) / 480Hz (FHD)', 'Thời gian phản hồi' => '0.03ms (GtG)', 'Độ phủ màu' => '98.5% DCI-P3'],
                'images/Monitor/86015_man_hinh_lg_oled_32gs95uv_b_1.webp'
            ],
            [
                'Màn hình văn phòng', 'Asus', 'Màn hình Asus Eye Care VY249HGR 24 inch IPS 120Hz', 'MH-ASUS-VY249', 2890000, 2490000, 25,
                ['Kích thước' => '23.8 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '120Hz', 'Tấm nền' => 'IPS Kháng khuẩn', 'Tính năng' => 'Eye Care Plus, SmoothMotion'],
                'images/Monitor/87179_man_hinh_asus_vy249hgr_2.webp'
            ],
            [
                'Màn hình gaming', 'Samsung', 'Màn hình Gaming Cong Samsung Odyssey OLED G8 LS34DG850 34 inch 175Hz', 'MH-SS-G8-OLED', 25990000, 22990000, 8,
                ['Kích thước' => '34 inch Cong 1800R', 'Độ phân giải' => 'UWQHD (3440x1440)', 'Tấm nền' => 'OLED Neo Quantum Processor', 'Tần số quét' => '175Hz', 'Thời gian phản hồi' => '0.03ms'],
                'images/Monitor/88055_man_hinh_samsung_odyssey_g8_ls34dg850sexxv_1.webp'
            ],
            [
                'Màn hình văn phòng', 'Asus', 'Màn hình Asus VA279HG 27 inch FHD IPS 100Hz', 'MH-ASUS-VA279', 3390000, 2990000, 20,
                ['Kích thước' => '27 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '100Hz', 'Tấm nền' => 'IPS', 'Góc nhìn' => '178°/178°'],
                'images/Monitor/90181_man_hinh_asus_va279hg_2.webp'
            ],
            [
                'Màn hình văn phòng', 'Samsung', 'Màn hình cong Samsung Essential S3 S39GD 32 inch FHD 100Hz', 'MH-SS-S39GD', 4890000, 4190000, 15,
                ['Kích thước' => '32 inch Cong 1500R', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '100Hz', 'Tấm nền' => 'VA', 'Bảo vệ mắt' => 'Eye Saver & Flicker Free'],
                'images/Monitor/90249_man_hinh_samsung_s3_s39gd_ls32d390gaexxv_1.webp'
            ],
            [
                'Màn hình đồ họa', 'Dell', 'Màn hình Dell Pro Plus P2725D 27 inch 2K QHD IPS 100Hz', 'MH-DELL-P2725D', 6490000, 5790000, 18,
                ['Kích thước' => '27 inch', 'Độ phân giải' => '2K QHD (2560x1440)', 'Tần số quét' => '100Hz', 'Tấm nền' => 'IPS ComfortView Plus', 'Độ phủ màu' => '99% sRGB'],
                'images/Monitor/90519_man_hinh_dell_pro_plus_p2725d_2.webp'
            ],
            [
                'Màn hình gaming', 'HKC', 'Màn hình Gaming HKC MG25H320 24.5 inch Fast IPS 320Hz', 'MH-HKC-MG25H320', 5290000, 4790000, 12,
                ['Kích thước' => '24.5 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '320Hz Siêu tốc', 'Tấm nền' => 'Fast IPS', 'Thời gian phản hồi' => '1ms (GtG)'],
                'images/Monitor/90944_man_hinh_hkc_mg25h320_1.webp'
            ],
            [
                'Màn hình gaming', 'HKC', 'Màn hình Gaming HKC MG25H29 24.5 inch Fast IPS 200Hz', 'MH-HKC-MG25H200', 3590000, 3190000, 20,
                ['Kích thước' => '24.5 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '200Hz', 'Tấm nền' => 'Fast IPS', 'Thời gian phản hồi' => '1ms'],
                'images/Monitor/90945_man_hinh_hkc_mg25h29_200hz_1.webp'
            ],
            [
                'Màn hình gaming', 'Asus', 'Màn hình Gaming Asus TUF VG259Q5A 24.5 inch Fast IPS 180Hz', 'MH-ASUS-VG259', 3990000, 3490000, 22,
                ['Kích thước' => '24.5 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '180Hz', 'Tấm nền' => 'Fast IPS', 'Công nghệ' => 'ELMB Sync, FreeSync Premium'],
                'images/Monitor/91300_man_hinh_asus_tuf_vg259q5a_2.webp'
            ],
            [
                'Màn hình văn phòng', 'Samsung', 'Màn hình thông minh Samsung Smart Monitor M7 LS32FM700 32 inch 4K', 'MH-SS-M7-32B', 9490000, 7990000, 10,
                ['Kích thước' => '32 inch', 'Độ phân giải' => '4K UHD (3840x2160)', 'Hệ điều hành' => 'Tizen OS Smart Hub', 'Kết nối' => 'AirPlay, WiFi, Bluetooth, Type-C 65W', 'Loa' => 'Tích hợp loa ngoài stereo'],
                'images/Monitor/91333_man_hinh_thong_minh_samsung_m7_ls32fm700_3.webp'
            ],
            [
                'Màn hình văn phòng', 'Samsung', 'Màn hình thông minh Samsung Smart Monitor M7 LS32FM701 Trắng 32 inch 4K', 'MH-SS-M7-32W', 9990000, 8490000, 8,
                ['Kích thước' => '32 inch Tone Trắng', 'Độ phân giải' => '4K UHD (3840x2160)', 'Hệ điều hành' => 'Tizen OS', 'Kết nối' => 'Type-C 65W, WiFi, AirPlay', 'Thiết kế' => 'Slim Design tối giản'],
                'images/Monitor/91334_man_hinh_thong_minh_samsung_m7_ls32fm701_3.webp'
            ],
            [
                'Màn hình gaming', 'ViewSonic', 'Màn hình Gaming Cong ViewSonic VX3418C-2K 34 inch UWQHD 144Hz', 'MH-VS-VX3418C', 8990000, 7690000, 10,
                ['Kích thước' => '34 inch Cong 1500R', 'Độ phân giải' => 'UWQHD (3440x1440)', 'Tần số quét' => '144Hz', 'Thời gian phản hồi' => '1ms (MPRT)', 'Loa' => 'Loa kép 5W'],
                'images/Monitor/93041_man_hinh_viewsonic_vx3418c_2k_2.webp'
            ],
            [
                'Màn hình đồ họa', 'ViewSonic', 'Màn hình cong đồ họa ViewSonic VG3820C 38 inch WQHD+ Type-C', 'MH-VS-VG3820C', 21990000, 19500000, 5,
                ['Kích thước' => '37.5 inch Cong 2300R', 'Độ phân giải' => 'WQHD+ (3840x1600)', 'Tấm nền' => 'IPS chuyên nghiệp', 'Cổng kết nối' => 'USB-C 90W, RJ45 LAN, KVM Switch'],
                'images/Monitor/93042_man_hinh_viewsonic_vg3820c_1.webp'
            ],
            [
                'Màn hình gaming', 'Asus', 'Màn hình Gaming Asus ROG Strix XG32WCS 32 inch 2K Fast VA 180Hz', 'MH-ASUS-XG32WCS', 9890000, 8790000, 12,
                ['Kích thước' => '31.5 inch Cong', 'Độ phân giải' => '2K QHD (2560x1440)', 'Tần số quét' => '180Hz', 'Tấm nền' => 'Fast VA 1ms GtG', 'Chuẩn màu' => 'DisplayHDR 400'],
                'images/Monitor/Asus rog strix wg32wcms.webp'
            ],
            [
                'Màn hình gaming', 'MSI', 'Màn hình Gaming MSI MAG 275QF E20 27 inch 2K Rapid IPS 180Hz', 'MH-MSI-MAG275QF', 5490000, 4890000, 15,
                ['Kích thước' => '27 inch', 'Độ phân giải' => '2K QHD (2560x1440)', 'Tần số quét' => '180Hz', 'Tấm nền' => 'Rapid IPS 0.5ms', 'Độ phủ màu' => '101% sRGB'],
                'images/Monitor/File-pts-chuan-le_0000_imgi_2_13279_man_hinh_msi_mag_275qf_e20_1.webp'
            ],
            [
                'Màn hình đồ họa', 'MSI', 'Màn hình Đồ Họa MSI Modern MD271UL 27 inch 4K UHD Type-C 65W', 'MH-MSI-MD271UL', 7990000, 6990000, 12,
                ['Kích thước' => '27 inch Siêu Mỏng', 'Độ phân giải' => '4K UHD (3840x2160)', 'Tấm nền' => 'IPS 10-bit', 'Độ phủ màu' => '99% DCI-P3, 139% sRGB', 'Cổng kết nối' => 'Type-C 65W, HDMI 2.0b, DP 1.4a'],
                'images/Monitor/File-pts-chuan-le_0005_Layer-1.webp'
            ],
            [
                'Màn hình gaming', 'AOC', 'Màn hình Gaming AOC Q27G42ZE 27 inch 2K Fast IPS 240Hz', 'MH-AOC-Q27G42', 6290000, 5590000, 14,
                ['Kích thước' => '27 inch', 'Độ phân giải' => '2K QHD (2560x1440)', 'Tần số quét' => '240Hz', 'Tấm nền' => 'Fast IPS 0.5ms', 'HDR' => 'HDR400'],
                'images/Monitor/man-hinh-aoc-q27g42ze-moao0260-1.webp'
            ],
            [
                'Màn hình gaming', 'Asus', 'Màn hình Siêu Cấp Asus ROG Swift OLED PG27AQWP 27 inch 2K 480Hz', 'MH-ASUS-PG27AQWP', 29990000, 27500000, 4,
                ['Kích thước' => '26.5 inch', 'Tấm nền' => 'OLED Thế hệ 3', 'Tần số quét' => '480Hz Vô đối', 'Thời gian phản hồi' => '0.03ms', 'Tản nhiệt' => 'Custom Heatsink buồng hơi graphene'],
                'images/Monitor/man-hinh-asus-rog-swift-oled-pg27aqwp-g-edition-20-moas0386-2.webp'
            ],
            [
                'Màn hình văn phòng', 'Dell', 'Màn hình Dell Pro P2726H 27 inch FHD IPS 120Hz USB-C', 'MH-DELL-P2726H', 4690000, 4190000, 16,
                ['Kích thước' => '27 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '120Hz', 'Tấm nền' => 'IPS', 'Cổng kết nối' => 'USB-C 15W, DP 1.4, HDMI, USB 3.2 Hub'],
                'images/Monitor/Man-Hinh-Dell-Pro-P2726H-27-inchFHDIPS120Hz5msUSB-C-15W-1.webp'
            ],
            [
                'Màn hình gaming', 'Gigabyte', 'Màn hình Gaming Cong Gigabyte G34WQC A 34 inch UWQHD 144Hz', 'MH-GB-G34WQC', 8890000, 7990000, 10,
                ['Kích thước' => '34 inch Cong 1500R', 'Độ phân giải' => 'UWQHD (3440x1440)', 'Tần số quét' => '144Hz', 'Thời gian phản hồi' => '1ms (MPRT)', 'Màu sắc' => '90% DCI-P3, HDR400'],
                'images/Monitor/man-hinh-gigabyte-g34wqc2-1.webp'
            ],
            [
                'Màn hình đồ họa', 'LG', 'Màn hình LG UltraWide 34U511A-B 34 inch WFHD IPS 100Hz', 'MH-LG-34U511A', 6990000, 6190000, 12,
                ['Kích thước' => '34 inch 21:9', 'Độ phân giải' => 'WFHD (2560x1080)', 'Tấm nền' => 'IPS 1ms MBR', 'Tần số quét' => '100Hz', 'Độ phủ màu' => 'sRGB 99% (CIE1931)'],
                'images/Monitor/Man-hinh-LG-34U511A-B-34-inchWFHDIPS100Hz1ms-3.webp'
            ],
            [
                'Màn hình đồ họa', 'LG', 'Màn hình Chuyên Đồ Họa LG UltraFine 27U711B-B 27 inch 4K UHD IPS', 'MH-LG-27U711B', 11490000, 10290000, 10,
                ['Kích thước' => '27 inch', 'Độ phân giải' => '4K UHD (3840x2160)', 'Tấm nền' => 'IPS chuyên nghiệp', 'Độ phủ màu' => 'DCI-P3 95%, HDR10', 'Chân đế' => 'Ergonomic xoay dọc 90°'],
                'images/Monitor/man-hinh-lg-ultrafine-27u711b-b-molg0330-1.webp'
            ],
            [
                'Màn hình gaming', 'LG', 'Màn hình Gaming LG UltraGear 27G550B-B 27 inch FHD 180Hz', 'MH-LG-27G550B', 4490000, 3890000, 20,
                ['Kích thước' => '27 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '180Hz', 'Tấm nền' => 'IPS 1ms (GtG)', 'Công nghệ' => 'AMD FreeSync, G-Sync Compatible'],
                'images/Monitor/man-hinh-lg-ultragear-27g550b-b-molg0327-1.webp'
            ],
            [
                'Màn hình gaming', 'LG', 'Màn hình Gaming LG UltraGear 27G64NA-B 27 inch 2K QHD Nano IPS 200Hz', 'MH-LG-27G64NA', 8990000, 7990000, 10,
                ['Kích thước' => '27 inch', 'Độ phân giải' => '2K QHD (2560x1440)', 'Tần số quét' => '200Hz Overclock', 'Tấm nền' => 'Nano IPS 1ms GtG', 'Chuẩn màu' => 'DCI-P3 98%, VESA DisplayHDR 400'],
                'images/Monitor/man-hinh-lg-ultragear-27g64na-b-molg0328-1.webp'
            ],
            [
                'Màn hình gaming', 'LG', 'Màn hình Gaming LG UltraGear G4 27G440A-B 27 inch Fast IPS 240Hz', 'MH-LG-27G440A', 5990000, 5190000, 15,
                ['Kích thước' => '27 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '240Hz', 'Tấm nền' => 'Fast IPS 1ms', 'Tương thích' => 'NVIDIA G-Sync & AMD FreeSync Premium'],
                'images/Monitor/Man-hinh-LG-UltraGear-G4-27G440A-B-27-icnhFHDIPS240Hz1ms-3.webp'
            ],
            [
                'Màn hình gaming', 'MSI', 'Màn hình Gaming MSI MAG 275QF E21 27 inch 2K Rapid IPS 200Hz', 'MH-MSI-MAG275E21', 5990000, 5290000, 16,
                ['Kích thước' => '27 inch', 'Độ phân giải' => '2K QHD (2560x1440)', 'Tần số quét' => '200Hz', 'Tấm nền' => 'Rapid IPS 0.5ms', 'Tính năng' => 'AI Vision, Night Vision'],
                'images/Monitor/man-hinh-msi-mag-275qf-e21-moms0188-4.webp'
            ],
            [
                'Màn hình văn phòng', 'MSI', 'Màn hình Văn Phòng MSI PRO MP242 E14A 24 inch IPS 100Hz', 'MH-MSI-MP242', 2390000, 2050000, 25,
                ['Kích thước' => '23.8 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '100Hz', 'Tấm nền' => 'IPS Chống chói', 'Loa' => 'Tích hợp 2 loa 2W', 'Chứng nhận' => 'TÜV Rheinland bảo vệ mắt'],
                'images/Monitor/man-hinh-msi-pro-mp242-e14a-moms0181-2.webp'
            ],
            [
                'Màn hình gaming', 'Samsung', 'Màn hình Gaming Samsung Odyssey G5 G53F LS27FG530 27 inch Fast IPS 180Hz', 'MH-SS-G5-G53F', 5290000, 4590000, 18,
                ['Kích thước' => '27 inch', 'Độ phân giải' => '2K QHD (2560x1440)', 'Tần số quét' => '180Hz', 'Tấm nền' => 'Fast IPS 1ms', 'Hỗ trợ' => 'HDR10, FreeSync'],
                'images/Monitor/man-hinh-samsung-odyssey-g5-g53f-ls27fg530eexxv-mosa0345-4.webp'
            ],
            [
                'Màn hình văn phòng', 'ViewSonic', 'Màn hình ViewSonic VA27G1-H 27 inch IPS 100Hz Viền Siêu Mỏng', 'MH-VS-VA27G1', 2790000, 2390000, 20,
                ['Kích thước' => '27 inch', 'Độ phân giải' => 'FHD (1920x1080)', 'Tần số quét' => '100Hz', 'Tấm nền' => 'SuperClear IPS', 'Viền màn hình' => '3 cạnh không viền thời thượng'],
                'images/Monitor/man-hinh-viewsonic-va27g1-h-movi0282-1.webp'
            ],
        ];
    }
}
