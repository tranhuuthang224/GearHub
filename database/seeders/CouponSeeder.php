<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            // Dùng được: giảm 10%, đơn từ 500.000 ₫
            ['code' => 'GEAR10',   'type' => Coupon::TYPE_PERCENT, 'value' => 10,    'min_order' => 500000, 'usage_limit' => 100,  'used_count' => 0,   'starts_at' => null, 'expires_at' => now()->addMonths(6), 'is_active' => true],
            // Dùng được: giảm 50.000 ₫, đơn từ 300.000 ₫, không giới hạn lượt
            ['code' => 'SAVE50K',  'type' => Coupon::TYPE_FIXED,   'value' => 50000, 'min_order' => 300000, 'usage_limit' => null, 'used_count' => 0,   'starts_at' => null, 'expires_at' => null, 'is_active' => true],
            // Các trường hợp lỗi để thử thông báo ở Checkout
            ['code' => 'EXPIRED',  'type' => Coupon::TYPE_PERCENT, 'value' => 20,    'min_order' => 0,      'usage_limit' => null, 'used_count' => 0,   'starts_at' => now()->subMonths(2), 'expires_at' => now()->subDay(), 'is_active' => true],
            ['code' => 'FULL',     'type' => Coupon::TYPE_PERCENT, 'value' => 15,    'min_order' => 0,      'usage_limit' => 10,   'used_count' => 10,  'starts_at' => null, 'expires_at' => null, 'is_active' => true],
            ['code' => 'SOON',     'type' => Coupon::TYPE_FIXED,   'value' => 100000, 'min_order' => 0,     'usage_limit' => null, 'used_count' => 0,   'starts_at' => now()->addWeek(), 'expires_at' => null, 'is_active' => true],
            ['code' => 'DISABLED', 'type' => Coupon::TYPE_PERCENT, 'value' => 30,    'min_order' => 0,      'usage_limit' => null, 'used_count' => 0,   'starts_at' => null, 'expires_at' => null, 'is_active' => false],
        ];

        // unguarded: used_count cố ý không nằm trong $fillable
        Coupon::unguarded(function () use ($coupons) {
            foreach ($coupons as $coupon) {
                Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
            }
        });
    }
}