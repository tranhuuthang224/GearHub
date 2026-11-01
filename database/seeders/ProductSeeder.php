<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductCatalog;
use Database\Seeders\Concerns\GeneratesPlaceholderImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use GeneratesPlaceholderImages;

    public function run(): void
    {
        $categoryIds = Category::pluck('id', 'slug');
        $brandIds    = Brand::pluck('id', 'slug');

        // 1. Seed phụ kiện bổ sung từ catalog của B (dùng ảnh placeholder)
        foreach ($this->catalog() as [$category, $brand, $name, $sku, $price, $sale, $stock, $specs]) {
            $slug = Str::slug($name);
            $catSlug = Str::slug($category);
            $catId = $categoryIds[$catSlug] ?? null;
            $brandSlug = Str::slug($brand);
            $brandId = $brandIds[$brandSlug] ?? null;

            if (!$catId || !$brandId) {
                continue;
            }

            // withTrashed: chạy lại seeder không đụng SKU của sản phẩm đã xóa mềm
            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id'    => $catId,
                    'brand_id'       => $brandId,
                    'name'           => $name,
                    'slug'           => $slug,
                    'description'    => "{$name} chính hãng {$brand}. Bảo hành 12 tháng, đổi trả trong 7 ngày.",
                    'specs'          => $specs,
                    'price'          => $price,
                    'sale_price'     => $sale,
                    'stock_quantity' => $stock,
                    'thumbnail'      => $this->placeholder("products/{$slug}/thumb.svg", $name, $brand),
                    'is_active'      => true,
                ]
            );

            $product->images()->delete();
            foreach ([1, 2, 3] as $i) {
                $product->images()->create([
                    'path'       => $this->placeholder("products/{$slug}/{$i}.svg", $name, "{$brand} - Ảnh {$i}"),
                    'sort_order' => $i,
                ]);
            }
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
     * [danh mục con, thương hiệu, tên, SKU, giá gốc, giá KM|null, tồn kho, thông số]
     * Giá và thông số chỉ mang tính minh họa, không cần khớp 100% với sản phẩm thật.
     */
    private function catalog(): array
    {
        return [
            // Chuột gaming
            ['Chuột gaming', 'Logitech', 'Logitech G Pro X Superlight 2', 'LG-GPXS2', 3290000, 2990000, 25,
                ['Kết nối' => '2.4GHz Lightspeed', 'Cảm biến' => 'HERO 2', 'DPI tối đa' => '32000', 'Trọng lượng' => '60g', 'Pin' => '95 giờ']],
            ['Chuột gaming', 'Razer', 'Razer DeathAdder V3', 'RZ-DAV3', 1890000, null, 30,
                ['Kết nối' => 'Có dây', 'Cảm biến' => 'Focus Pro 30K', 'DPI tối đa' => '30000', 'Trọng lượng' => '59g']],
            ['Chuột gaming', 'Razer', 'Razer Viper V3 Pro', 'RZ-VV3P', 3990000, 3590000, 12,
                ['Kết nối' => '2.4GHz HyperSpeed', 'DPI tối đa' => '35000', 'Trọng lượng' => '54g', 'Pin' => '95 giờ']],
            ['Chuột gaming', 'SteelSeries', 'SteelSeries Aerox 3 Wireless', 'SS-AX3W', 1590000, 1290000, 4,
                ['Kết nối' => '2.4GHz + Bluetooth', 'DPI tối đa' => '18000', 'Trọng lượng' => '68g', 'Pin' => '200 giờ']],

            // Chuột văn phòng
            ['Chuột văn phòng', 'Logitech', 'Logitech MX Master 3S', 'LG-MXM3S', 2490000, 2190000, 20,
                ['Kết nối' => 'Bluetooth + Logi Bolt', 'DPI tối đa' => '8000', 'Trọng lượng' => '141g', 'Pin' => '70 ngày']],
            ['Chuột văn phòng', 'Logitech', 'Logitech M331 Silent Plus', 'LG-M331', 390000, null, 60,
                ['Kết nối' => '2.4GHz', 'DPI tối đa' => '1000', 'Đặc điểm' => 'Click êm', 'Pin' => '24 tháng']],
            ['Chuột văn phòng', 'Anker', 'Anker Vertical Ergonomic Mouse', 'ANK-VMOUSE', 450000, null, 35,
                ['Kết nối' => '2.4GHz', 'DPI tối đa' => '1600', 'Thiết kế' => 'Công thái học dọc']],

            // Bàn phím cơ không dây
            ['Bàn phím cơ không dây', 'Keychron', 'Keychron K2 Pro', 'KC-K2P', 2590000, 2390000, 18,
                ['Layout' => '75%', 'Switch' => 'Gateron G Pro Red', 'Kết nối' => 'Bluetooth 5.1 + có dây', 'Hot-swap' => 'Có']],
            ['Bàn phím cơ không dây', 'Keychron', 'Keychron K8 Pro', 'KC-K8P', 2790000, null, 14,
                ['Layout' => 'TKL', 'Switch' => 'Gateron G Pro Brown', 'Kết nối' => 'Bluetooth 5.1 + có dây', 'Hot-swap' => 'Có']],
            ['Bàn phím cơ không dây', 'Keychron', 'Keychron Q1 Max', 'KC-Q1M', 4990000, 4590000, 3,
                ['Layout' => '75%', 'Khung' => 'Nhôm CNC', 'Kết nối' => '2.4GHz + Bluetooth', 'Cấu trúc' => 'Gasket mount']],
            ['Bàn phím cơ không dây', 'Akko', 'Akko 3068B Plus', 'AK-3068BP', 1690000, 1490000, 22,
                ['Layout' => '65%', 'Switch' => 'Akko CS Jelly Pink', 'Kết nối' => '3 chế độ', 'Hot-swap' => 'Có']],

            // Bàn phím cơ có dây
            ['Bàn phím cơ có dây', 'Corsair', 'Corsair K70 RGB TKL', 'CS-K70TKL', 3290000, null, 10,
                ['Layout' => 'TKL', 'Switch' => 'Cherry MX Red', 'Đèn' => 'RGB từng phím', 'Kết nối' => 'USB-C']],
            ['Bàn phím cơ có dây', 'Razer', 'Razer BlackWidow V4 X', 'RZ-BWV4X', 3490000, 2990000, 8,
                ['Layout' => 'Full-size', 'Switch' => 'Razer Green', 'Macro' => '6 phím', 'Đèn' => 'RGB Chroma']],
            ['Bàn phím cơ có dây', 'HyperX', 'HyperX Alloy Origins 60', 'HX-AO60', 2190000, 1890000, 0,
                ['Layout' => '60%', 'Switch' => 'HyperX Red', 'Khung' => 'Nhôm', 'Kết nối' => 'USB-C']],

            // Tai nghe gaming
            ['Tai nghe gaming', 'HyperX', 'HyperX Cloud II', 'HX-CLD2', 2290000, 1990000, 28,
                ['Driver' => '53mm', 'Kết nối' => 'USB / 3.5mm', 'Micro' => 'Tháo rời', 'Âm thanh' => '7.1 ảo']],
            ['Tai nghe gaming', 'Razer', 'Razer BlackShark V2 X', 'RZ-BSV2X', 1490000, null, 33,
                ['Driver' => '50mm', 'Kết nối' => '3.5mm', 'Trọng lượng' => '240g']],
            ['Tai nghe gaming', 'SteelSeries', 'SteelSeries Arctis Nova 1', 'SS-ARN1', 1890000, 1590000, 15,
                ['Driver' => '40mm', 'Kết nối' => '3.5mm', 'Micro' => 'Khử ồn']],
            ['Tai nghe gaming', 'Corsair', 'Corsair HS80 RGB Wireless', 'CS-HS80W', 3990000, null, 9,
                ['Driver' => '50mm', 'Kết nối' => '2.4GHz', 'Pin' => '20 giờ', 'Âm thanh' => 'Dolby Atmos']],

            // Tai nghe không dây
            ['Tai nghe không dây', 'Anker', 'Anker Soundcore Liberty 4 NC', 'ANK-LB4NC', 2990000, 2490000, 24,
                ['Kiểu' => 'True Wireless', 'Chống ồn' => 'Chủ động', 'Pin' => '10 giờ (50 giờ với hộp sạc)', 'Kết nối' => 'Bluetooth 5.3']],
            ['Tai nghe không dây', 'Anker', 'Anker Soundcore Space One', 'ANK-SP1', 2490000, 1990000, 16,
                ['Kiểu' => 'Over-ear', 'Chống ồn' => 'Chủ động', 'Pin' => '40 giờ', 'Kết nối' => 'Bluetooth 5.3']],
            ['Tai nghe không dây', 'Baseus', 'Baseus Bowie WM02', 'BS-WM02', 590000, 490000, 50,
                ['Kiểu' => 'True Wireless', 'Pin' => '5 giờ (25 giờ với hộp sạc)', 'Kết nối' => 'Bluetooth 5.3']],

            // Củ sạc nhanh
            ['Củ sạc nhanh', 'Anker', 'Anker Nano II 65W', 'ANK-NANO65', 890000, 790000, 40,
                ['Công suất' => '65W', 'Cổng' => '1 USB-C', 'Công nghệ' => 'GaN II']],
            ['Củ sạc nhanh', 'Anker', 'Anker 737 GaNPrime 120W', 'ANK-737G', 1990000, null, 12,
                ['Công suất' => '120W', 'Cổng' => '2 USB-C + 1 USB-A', 'Công nghệ' => 'GaNPrime']],
            ['Củ sạc nhanh', 'Baseus', 'Baseus GaN5 Pro 65W', 'BS-GAN5P', 690000, 590000, 55,
                ['Công suất' => '65W', 'Cổng' => '2 USB-C + 1 USB-A', 'Công nghệ' => 'GaN']],
            ['Củ sạc nhanh', 'Ugreen', 'Ugreen Nexode 100W', 'UG-NX100', 1290000, 1090000, 20,
                ['Công suất' => '100W', 'Cổng' => '3 USB-C + 1 USB-A', 'Công nghệ' => 'GaNFast']],

            // Sạc dự phòng
            ['Sạc dự phòng', 'Anker', 'Anker 737 Power Bank 24000mAh', 'ANK-737PB', 2490000, null, 8,
                ['Dung lượng' => '24000mAh', 'Công suất' => '140W', 'Màn hình' => 'OLED hiển thị thông số']],
            ['Sạc dự phòng', 'Baseus', 'Baseus Blade 100W 20000mAh', 'BS-BLADE', 1390000, 1190000, 18,
                ['Dung lượng' => '20000mAh', 'Công suất' => '100W', 'Độ mỏng' => '18mm', 'Cổng' => '2 Type-C + 2 USB-A']],

            // Bàn di chuột
            ['Bàn di chuột', 'SteelSeries', 'SteelSeries QcK Heavy XXL', 'SS-QCKH-XXL', 890000, null, 30,
                ['Kích thước' => '900 x 400 x 4mm', 'Bề mặt' => 'Vải dệt vi mô', 'Đế' => 'Cao su chống trượt']],
            ['Bàn di chuột', 'Razer', 'Razer Gigantus V2 Large', 'RZ-GV2-L', 450000, 390000, 45,
                ['Kích thước' => '450 x 400 x 3mm', 'Bề mặt' => 'Vải dệt mịn', 'Bọt cao su' => 'Mật độ cao']],
            ['Bàn di chuột', 'Corsair', 'Corsair MM300 Pro Extended', 'CS-MM300P', 790000, 690000, 20,
                ['Kích thước' => '930 x 300 x 3mm', 'Chống tràn nước' => 'Có', 'Viền' => 'Bo chỉ chống sờn']],

            // Giá đỡ, đèn và phụ kiện
            ['Giá đỡ, đèn và phụ kiện', 'Baseus', 'Baseus i-Work Monitor Light Bar', 'BS-IWORK-LB', 790000, 690000, 25,
                ['Nhiệt độ màu' => '2800K - 5500K', 'Nguồn' => 'USB 5V', 'Chống lóa mắt' => 'Có']],
            ['Giá đỡ, đèn và phụ kiện', 'Baseus', 'Baseus Đèn bàn LED Smart Eye', 'BS-SMARTEYE', 1290000, 1090000, 14,
                ['Cảm biến ánh sáng' => 'Tự động', 'Chỉ số hoàn màu' => 'Ra >= 95', 'Pin' => '2200mAh']],
            ['Giá đỡ, đèn và phụ kiện', 'Ugreen', 'Ugreen Giá đỡ laptop hợp kim nhôm', 'UG-LPSTD', 390000, 3490000, 50,
                ['Chất liệu' => 'Hợp kim nhôm', 'Nấc chỉnh' => '5 góc độ', 'Tải trọng' => 'Tối đa 20kg']],
            ['Giá đỡ, đèn và phụ kiện', 'Ugreen', 'Ugreen Giá treo màn hình 17-32 inch', 'UG-MNARM', 890000, 790000, 16,
                ['Chuẩn VESA' => '75x75, 100x100', 'Tải trọng' => '9kg', 'Lò xo khí nén' => 'Có']],
            ['Giá đỡ, đèn và phụ kiện', 'Keychron', 'Keychron Kê tay gỗ Walnut', 'KC-WR-WAL', 590000, null, 22,
                ['Chất liệu' => 'Gỗ óc chó tự nhiên', 'Kích thước' => 'Phù hợp layout K2/Q1', 'Đế cao su' => 'Chống trượt']],
        ];
    }
}
