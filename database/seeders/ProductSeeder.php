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

        // 2. Seed danh mục gaming gear thực tế (56 sản phẩm với hình ảnh thật từ public/images/)
        // Seed sau để có ID cao hơn, tự động hiển thị trước ở trang Shop theo newest/latest('id')
        foreach (ProductCatalog::all() as $item) {
            $childCategory = $this->detectChildCategory($item);
            $catSlug = Str::slug($childCategory);
            $catId = $categoryIds[$catSlug] ?? $categoryIds[Str::slug($item['category'])] ?? null;

            $brandSlug = Str::slug($item['brand']);
            $brandId = $brandIds[$brandSlug] ?? null;

            if (!$catId || !$brandId) {
                continue;
            }

            // Chuẩn hóa đường dẫn tương đối (e.g. 'images/Keyboard/Aula F75.webp')
            $thumbnail = preg_replace('#^https?://[^/]+/#', '', $item['thumbnail']);

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id'    => $catId,
                    'brand_id'       => $brandId,
                    'name'           => $item['name'],
                    'slug'           => $item['slug'],
                    'description'    => $item['description'] ?? "{$item['name']} chính hãng {$item['brand']}.",
                    'specs'          => $item['specs'] ?? [],
                    'price'          => $item['price'],
                    'sale_price'     => $item['sale_price'] ?? null,
                    'stock_quantity' => $item['stock_quantity'] ?? 20,
                    'thumbnail'      => $thumbnail,
                    'is_active'      => true,
                ]
            );

            $product->images()->delete();
            $gallery = $item['gallery'] ?? [$item['thumbnail']];
            $sort = 1;
            foreach ($gallery as $galleryImg) {
                $imgPath = preg_replace('#^https?://[^/]+/#', '', $galleryImg);
                $product->images()->create([
                    'path'       => $imgPath,
                    'sort_order' => $sort++,
                ]);
            }
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
        ];
    }
}
