<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GearHub Bank Transfer Settings
    |--------------------------------------------------------------------------
    | Cấu hình tài khoản ngân hàng nhận thanh toán chuyển khoản (VietQR).
    | Toàn bộ giá trị đọc từ file .env.
    | Các giá trị mặc định ở đây là dữ liệu mẫu placeholder để đảm bảo an toàn bảo mật.
    */
    'bank_name'       => env('BANK_NAME', 'VietinBank (Ngân hàng TMCP Công Thương Việt Nam)'),
    'bank_short_name' => env('BANK_SHORT_NAME', 'VietinBank'),
    'bank_branch'     => env('BANK_BRANCH', 'Hội Sở Chính'),
    'bank_bin'        => env('BANK_BIN', '970415'),
    'account_number'  => env('BANK_ACCOUNT_NUMBER', '0123456789'),
    'account_name'    => env('BANK_ACCOUNT_NAME', 'GEARHUB STORE'),
];
