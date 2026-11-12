<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GearHub Bank Transfer Settings
    |--------------------------------------------------------------------------
    | Cấu hình tài khoản ngân hàng nhận thanh toán chuyển khoản (VietQR).
    | Giá trị lấy từ .env để tránh hardcode thông tin tài chính cá nhân trong view.
    */
    'bank_name'       => env('BANK_NAME', 'VietinBank (Ngân hàng TMCP Công Thương Việt Nam)'),
    'bank_short_name' => env('BANK_SHORT_NAME', 'VietinBank'),
    'bank_branch'     => env('BANK_BRANCH', 'CN AN GIANG - PGD LONG XUYEN'),
    'bank_bin'        => env('BANK_BIN', '970415'),
    'account_number'  => env('BANK_ACCOUNT_NUMBER', '103882436377'),
    'account_name'    => env('BANK_ACCOUNT_NAME', 'TRAN HUU THANG'),
];
