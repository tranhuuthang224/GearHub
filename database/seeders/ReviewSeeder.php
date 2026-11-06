<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo thêm tài khoản reviewer mẫu nếu chưa có
        $reviewers = [
            ['name' => 'Trần Hữu Thắng', 'email' => 'thang.tran@gmail.com', 'phone' => '0912345671'],
            ['name' => 'Nguyễn Văn An', 'email' => 'an.nguyen@gmail.com', 'phone' => '0912345672'],
            ['name' => 'Lê Hoàng Cường', 'email' => 'cuong.le@gmail.com', 'phone' => '0912345673'],
            ['name' => 'Đỗ Mai Linh', 'email' => 'linh.do@gmail.com', 'phone' => '0912345674'],
            ['name' => 'Phạm Gia Bảo', 'email' => 'bao.pham@gmail.com', 'phone' => '0912345675'],
        ];

        $users = [];
        User::unguarded(function () use ($reviewers, &$users) {
            foreach ($reviewers as $r) {
                $users[] = User::firstOrCreate(
                    ['email' => $r['email']],
                    [
                        'name' => $r['name'],
                        'password' => bcrypt('Customer@123'),
                        'phone' => $r['phone'],
                        'role' => User::ROLE_CUSTOMER,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]
                );
            }
        });

        // 2. Danh sách đánh giá chi tiết theo SKU
        $catalogReviews = [
            'KB-AULA-F75' => [
                [
                    'user_index' => 0,
                    'rating' => 5,
                    'comment' => 'Phím gõ cực êm, stock đã lót sẵn foam tiêu âm đầy đủ. Switch Reaper cho âm thanh thocky trầm ấm rất thích tai. Keycap PBT dày dặn, kết nối 3 mode không hề bị delay. Đáng từng đồng bát gạo!',
                    'photos' => ['images/Keyboard/Aula F75.webp'],
                    'days_ago' => 2,
                ],
                [
                    'user_index' => 1,
                    'rating' => 5,
                    'comment' => 'Giao hàng hỏa tốc trong 2 tiếng tại TP.HCM. Hộp còn nguyên seal bọc xốp cẩn thận. Núm xoay kim loại chỉnh âm lượng tiện lợi, led RGB sáng đẹp nhiều hiệu ứng mượt mà.',
                    'photos' => [],
                    'days_ago' => 5,
                ],
                [
                    'user_index' => 2,
                    'rating' => 4,
                    'comment' => 'Chất lượng build nhựa nhưng rất đầm tay và chắc chắn (gần 1kg). Switch nhạy và mượt, chỉ có điều app driver hãng hơi khó dùng một xíu. Tổng thể rất đáng mua trong tầm giá dưới 1 triệu.',
                    'photos' => [],
                    'days_ago' => 8,
                ],
                [
                    'user_index' => 3,
                    'rating' => 5,
                    'comment' => 'Màu sắc phối ở ngoài đẹp hơn cả ảnh chụp trên web! Gasket mount êm ái, gõ văn phòng cả ngày không bị mỏi tay.',
                    'photos' => [],
                    'days_ago' => 12,
                ],
            ],
            'KB-AKKO-3087' => [
                [
                    'user_index' => 1,
                    'rating' => 5,
                    'comment' => 'Dòng phím Akko thì chuẩn chỉnh không cần bàn cãi. Keycap PBT dye-sub sắc nét dùng cả năm không sợ bóng mờ. Hàng chính hãng bảo hành Mai Hoàng 12 tháng yên tâm tuyệt đối.',
                    'photos' => ['images/Keyboard/AKKO 3087.webp'],
                    'days_ago' => 3,
                ],
                [
                    'user_index' => 4,
                    'rating' => 5,
                    'comment' => 'Switch Akko v2 gõ rất nảy và đầm. Shop tư vấn nhiệt tình, đóng gói 3 lớp bubble chống sốc an toàn.',
                    'photos' => [],
                    'days_ago' => 7,
                ],
                [
                    'user_index' => 0,
                    'rating' => 4,
                    'comment' => 'Phím bấm ngon, layout TKL gọn gàng tiết kiệm diện tích bàn làm việc. Dây Type-C có thể tháo rời tiện mang đi lại.',
                    'photos' => [],
                    'days_ago' => 14,
                ],
            ],
            'MS-LOGI-GPX2' => [
                [
                    'user_index' => 2,
                    'rating' => 5,
                    'comment' => 'Chuột siêu nhẹ chỉ 60g, cảm biến HERO 2 tracking chuẩn từng milimet trong game CS2 và Valorant. Switch quang học cơ học nhấp rất giòn và dứt khoát, không sợ double click.',
                    'photos' => ['images/Mouse/logitech g pro x superlight 2.webp'],
                    'days_ago' => 1,
                ],
                [
                    'user_index' => 0,
                    'rating' => 5,
                    'comment' => 'Pin cực trâu, mình dùng chơi game mỗi tối 4-5 tiếng mà gần 2 tuần mới phải cắm sạc. Feet chuột lướt trên pad rất mượt.',
                    'photos' => [],
                    'days_ago' => 6,
                ],
            ],
            'HP-HYPERX-C3' => [
                [
                    'user_index' => 3,
                    'rating' => 5,
                    'comment' => 'Đệm tai êm ái vô địch, đeo kính cả ngày không hề bị đau cấn thái dương. Âm trường rộng nghe tiếng bước chân địch rõ mồn một.',
                    'photos' => ['images/Headphones/HyperX Cloud III.webp'],
                    'days_ago' => 4,
                ],
                [
                    'user_index' => 1,
                    'rating' => 4,
                    'comment' => 'Mic thu âm trong và lọc tạp âm tốt hơn bản Cloud II nhiều. Chất âm thiên về gaming nên nghe nhạc acoustic ở mức vừa phải.',
                    'photos' => [],
                    'days_ago' => 10,
                ],
            ],
        ];

        foreach ($catalogReviews as $sku => $reviews) {
            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                continue;
            }

            foreach ($reviews as $item) {
                $user = $users[$item['user_index']] ?? $users[0];
                $createdDate = now()->subDays($item['days_ago'] ?? 1);

                // 2.1 Tạo đơn hàng hoàn thành để xác nhận huy hiệu "Đã mua hàng tại GearHub"
                $order = Order::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'code' => 'GH-REV-' . $product->id . '-' . $user->id,
                    ],
                    [
                        'receiver_name' => $user->name,
                        'phone' => $user->phone ?? '0912345678',
                        'shipping_address' => 'Số 123 Đường Công Nghệ, Phường Linh Trung, TP. Thủ Đức, TP.HCM',
                        'note' => 'Đơn hàng giao thành công kèm đánh giá xác thực',
                        'payment_method' => 'cod',
                        'status' => Order::STATUS_COMPLETED,
                        'subtotal' => $product->price,
                        'discount' => 0,
                        'total' => $product->price,
                        'created_at' => $createdDate->copy()->subDays(2),
                        'updated_at' => $createdDate->copy()->subDays(1),
                    ]
                );

                OrderItem::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => 1,
                    ]
                );

                // 2.2 Tạo review
                Review::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'rating' => $item['rating'],
                        'comment' => $item['comment'],
                        'photos' => $item['photos'] ?? [],
                        'created_at' => $createdDate,
                        'updated_at' => $createdDate,
                    ]
                );
            }
        }
    }
}
