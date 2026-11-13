<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lập lịch tự động hủy các đơn hàng chuyển khoản ngân hàng quá hạn 24 giờ (chống chạy chồng chéo)
Schedule::command('orders:cancel-expired-bank-transfers --hours=24')
    ->hourly()
    ->withoutOverlapping();

