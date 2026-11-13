<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bật phát hiện lỗi N+1 truy vấn ở môi trường local/testing
        Model::preventLazyLoading(! $this->app->isProduction());

        // 1. Giới hạn đặt hàng: Tối đa 5 lượt đặt hàng / phút / IP để chống spam đơn ảo
        RateLimiter::for('checkout-order', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip())->response(function (Request $request) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Bạn thao tác đặt hàng quá nhanh. Vui lòng chờ 1 phút.',
                    ], 429);
                }

                return back()->withInput()->with('error', 'Bạn thao tác đặt hàng quá nhanh. Vui lòng chờ 1 phút.');
            });
        });

        // 2. Giới hạn Quiz: Tối đa 15 lượt submit / phút / IP
        RateLimiter::for('quiz-submit', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip());
        });

        // 3. Giới hạn Thả tim & Bình luận: Tối đa 30 thao tác / phút để chống autoclick
        RateLimiter::for('community-interactions', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // 4. Giới hạn Gửi đánh giá: Tối đa 5 đánh giá / phút
        RateLimiter::for('reviews-submit', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}