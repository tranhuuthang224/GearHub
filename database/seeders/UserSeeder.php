<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Quản trị viên',  'email' => 'admin@gearhub.vn',  'password' => 'Admin@123456', 'phone' => '0900000000', 'role' => 'admin'],
            ['name' => 'Nguyễn Văn An',  'email' => 'khach1@gearhub.vn', 'password' => 'Customer@123', 'phone' => '0901111111', 'role' => 'customer'],
            ['name' => 'Trần Thị Bình',  'email' => 'khach2@gearhub.vn', 'password' => 'Customer@123', 'phone' => '0902222222', 'role' => 'customer'],
            ['name' => 'Lê Hoàng Cường', 'email' => 'khach3@gearhub.vn', 'password' => 'Customer@123', 'phone' => '0903333333', 'role' => 'customer'],
        ];

        // unguarded: role và is_active cố ý không nằm trong $fillable (hôm qua),
        // nên seeder phải bỏ qua bảo vệ này một cách có chủ đích.
        User::unguarded(function () use ($accounts) {
            foreach ($accounts as $account) {
                User::updateOrCreate(
                    ['email' => $account['email']],
                    $account + ['is_active' => true, 'email_verified_at' => now()]
                );
            }
        });
    }
}