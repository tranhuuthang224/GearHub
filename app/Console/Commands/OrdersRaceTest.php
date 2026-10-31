<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class OrdersRaceTest extends Command
{
    protected $signature = 'orders:race-test
        {scenario=stock : stock | multi | coupon | cancel}
        {--workers=8 : Số tiến trình chạy đồng thời}
        {--stock=3 : Tồn kho ban đầu của sản phẩm thử}
        {--rounds=3 : Số vòng lặp}';

    protected $description = 'Thử chống đua thật trên MySQL (chỉ ở môi trường local). Tạo dữ liệu tạm và tự dọn.';

    private const PREFIX = 'RACETEST-';

    /** Ngoại lệ nghiệp vụ được phép xảy ra; mọi lỗi khác (deadlock, treo, sập) là lỗi hệ thống. */
    private const DOMAIN_ERRORS = [
        'InsufficientStockException',
        'InvalidCouponException',
        'InvalidOrderTransitionException',
    ];

    public function handle(OrderService $orders): int
    {
        if (! app()->environment('local')) {
            $this->error('Lệnh này chỉ chạy ở môi trường local.');

            return self::FAILURE;
        }

        if (DB::getDriverName() !== 'mysql') {
            $this->error('Cần kết nối MySQL: SQLite không chạy đồng thời thật nên không chứng minh được gì.');

            return self::FAILURE;
        }

        $scenario = (string) $this->argument('scenario');

        if (! in_array($scenario, ['stock', 'multi', 'coupon', 'cancel'], true)) {
            $this->error('Kịch bản phải là một trong: stock, multi, coupon, cancel.');

            return self::FAILURE;
        }

        $workers   = max(2, (int) $this->option('workers'));
        $stock     = max(1, (int) $this->option('stock'));
        $rounds    = max(1, (int) $this->option('rounds'));
        $customers = User::where('role', User::ROLE_CUSTOMER)->where('is_active', true)->limit(3)->get();
        $admin     = User::where('role', User::ROLE_ADMIN)->first();

        if ($customers->isEmpty() || ! $admin || ! Category::whereNotNull('parent_id')->exists() || ! Brand::exists()) {
            $this->error('Thiếu dữ liệu mẫu: chạy php artisan migrate:fresh --seed trước.');

            return self::FAILURE;
        }

        $failed = 0;

        for ($round = 1; $round <= $rounds; $round++) {
            $this->newLine();
            $this->info("Vòng {$round}/{$rounds}: kịch bản «{$scenario}», {$workers} tiến trình");

            $this->cleanup();   // dọn sót từ lần chạy trước (nếu có)

            try {
                $passed = match ($scenario) {
                    'stock'  => $this->scenarioStock($workers, $stock, $customers),
                    'multi'  => $this->scenarioMulti($workers, $stock, $customers),
                    'coupon' => $this->scenarioCoupon($workers, $customers),
                    'cancel' => $this->scenarioCancel($orders, $workers, $stock, $customers, $admin),
                };
            } finally {
                $this->cleanup();
            }

            $failed += $passed ? 0 : 1;
        }

        $this->newLine();

        if ($failed === 0) {
            $this->info("KẾT LUẬN: đạt cả {$rounds} vòng.");

            return self::SUCCESS;
        }

        $this->error("KẾT LUẬN: {$failed}/{$rounds} vòng KHÔNG đạt. Xem bảng ở trên.");

        return self::FAILURE;
    }

    // ---------- Kịch bản ----------

    /** N người cùng mua món chỉ còn S cái. */
    private function scenarioStock(int $workers, int $stock, Collection $customers): bool
    {
        $product = $this->makeProduct($stock);
        $jobs    = [];

        for ($i = 0; $i < $workers; $i++) {
            $jobs[] = $this->orderJob($customers[$i % $customers->count()], [[$product->id, 1]]);
        }

        $results  = $this->launch($jobs);
        $expected = min($workers, $stock);

        return $this->report($results, [
            ['Số đơn thành công', $this->successes($results), $expected],
            ['Tồn kho cuối', (int) Product::find($product->id)->stock_quantity, $stock - $expected],
            ['Số dòng đơn đã lưu', OrderItem::where('product_id', $product->id)->count(), $expected],
        ]);
    }

    /** Hai nhóm mua cùng hai sản phẩm nhưng thứ tự ngược nhau: thử deadlock. */
    private function scenarioMulti(int $workers, int $stock, Collection $customers): bool
    {
        $a    = $this->makeProduct($stock);
        $b    = $this->makeProduct($stock);
        $jobs = [];

        for ($i = 0; $i < $workers; $i++) {
            $cart   = $i % 2 === 0 ? [[$a->id, 1], [$b->id, 1]] : [[$b->id, 1], [$a->id, 1]];
            $jobs[] = $this->orderJob($customers[$i % $customers->count()], $cart);
        }

        $results  = $this->launch($jobs);
        $expected = min($workers, $stock);

        return $this->report($results, [
            ['Số đơn thành công', $this->successes($results), $expected],
            ['Tồn kho sản phẩm A', (int) Product::find($a->id)->stock_quantity, $stock - $expected],
            ['Tồn kho sản phẩm B', (int) Product::find($b->id)->stock_quantity, $stock - $expected],
        ]);
    }

    /** Mỗi người mua một sản phẩm riêng nhưng cùng dùng một mã chỉ còn 1 lượt. */
    private function scenarioCoupon(int $workers, Collection $customers): bool
    {
        $perProduct = 5;
        $coupon     = $this->makeCoupon();
        $products   = [];
        $jobs       = [];

        for ($i = 0; $i < $workers; $i++) {
            $products[] = $this->makeProduct($perProduct);
            $jobs[]     = $this->orderJob($customers[$i % $customers->count()], [[$products[$i]->id, 1]], $coupon->code);
        }

        $results = $this->launch($jobs);
        $ids     = array_map(fn ($p) => $p->id, $products);

        return $this->report($results, [
            ['Số đơn thành công', $this->successes($results), 1],
            ['Lượt dùng của mã (used_count)', (int) Coupon::find($coupon->id)->used_count, 1],
            ['Số đơn gắn mã', Order::where('coupon_id', $coupon->id)->count(), 1],
            // Các đơn thất bại đã trừ kho rồi bị hoàn tác: tổng kho chỉ giảm đúng 1
            ['Tổng tồn kho các sản phẩm', (int) Product::whereIn('id', $ids)->sum('stock_quantity'), $workers * $perProduct - 1],
        ]);
    }

    /** N yêu cầu cùng hủy một đơn. */
    private function scenarioCancel(OrderService $orders, int $workers, int $stock, Collection $customers, User $admin): bool
    {
        $initial = max($stock, 2);
        $product = $this->makeProduct($initial);

        $order = $orders->createOrder($customers[0], [['product_id' => $product->id, 'quantity' => 2]], [
            'receiver_name'    => 'Race Test',
            'phone'            => '0900000000',
            'shipping_address' => 'Thử chống đua',
        ]);

        $jobs = [];

        for ($i = 0; $i < $workers; $i++) {
            $jobs[] = ['action' => 'cancel', 'user_id' => $admin->id, 'order_id' => $order->id];
        }

        $results = $this->launch($jobs);

        return $this->report($results, [
            ['Số lần hủy thành công', $this->successes($results), 1],
            ['Tồn kho cuối (hoàn đúng 1 lần)', (int) Product::find($product->id)->stock_quantity, $initial],
            ['Số dòng nhật ký "hủy"', OrderStatusHistory::where('order_id', $order->id)->where('to_status', Order::STATUS_CANCELLED)->count(), 1],
        ]);
    }

    // ---------- Chạy tiến trình và báo cáo ----------

    /** @return array<string, mixed> */
    private function orderJob(User $user, array $lines, ?string $coupon = null): array
    {
        return [
            'action'  => 'order',
            'user_id' => $user->id,
            'cart'    => array_map(fn ($line) => ['product_id' => $line[0], 'quantity' => $line[1]], $lines),
            'coupon'  => $coupon,
        ];
    }

    /**
     * Khởi chạy mọi tiến trình rồi chờ kết quả. Các tiến trình cùng đợi một giờ xuất phát chung
     * (sau khi tất cả đã khởi động xong Laravel) để thật sự đụng nhau.
     *
     * @param  array<int, array<string, mixed>>  $jobs
     * @return array<int, array<string, mixed>>
     */
    private function launch(array $jobs): array
    {
        $startAt   = microtime(true) + 3 + count($jobs) * 0.6;
        $processes = [];

        foreach ($jobs as $job) {
            $payload = base64_encode(json_encode($job + ['start_at' => $startAt]));

            $process = new Process(
                [PHP_BINARY, base_path('artisan'), 'orders:race-worker', '--payload=' . $payload],
                base_path()
            );
            $process->setTimeout(180);
            $process->start();

            $processes[] = $process;
        }

        $results = [];

        foreach ($processes as $process) {
            $process->wait();
            $results[] = $this->parse($process);
        }

        return $results;
    }

    /** @return array<string, mixed> */
    private function parse(Process $process): array
    {
        if (preg_match_all('/^RESULT:(.+)$/m', $process->getOutput(), $matches) && $matches[1] !== []) {
            $decoded = json_decode(trim($matches[1][array_key_last($matches[1])]), true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $detail = trim($process->getErrorOutput() ?: $process->getOutput());

        return ['ok' => false, 'type' => 'WorkerCrash', 'message' => $detail !== '' ? $detail : 'Không có kết quả'];
    }

    private function successes(array $results): int
    {
        return count(array_filter($results, fn ($r) => $r['ok'] ?? false));
    }

    /**
     * In bảng kiểm tra; trả về true nếu mọi kiểm tra đạt và không có lỗi hệ thống.
     *
     * @param  array<int, array{0: string, 1: int, 2: int}>  $checks  [nhãn, thực tế, mong đợi]
     */
    private function report(array $results, array $checks): bool
    {
        $systemErrors = array_filter(
            $results,
            fn ($r) => ! ($r['ok'] ?? false) && ! in_array($r['type'] ?? '', self::DOMAIN_ERRORS, true)
        );

        $checks[] = ['Lỗi hệ thống (deadlock, treo, sập)', count($systemErrors), 0];

        $summary = [];

        foreach ($results as $result) {
            $key           = ($result['ok'] ?? false) ? 'thành công' : ($result['type'] ?? 'lỗi');
            $summary[$key] = ($summary[$key] ?? 0) + 1;
        }

        $this->line('Kết quả các tiến trình: ' . implode(', ', array_map(
            fn ($key, $count) => "{$key} {$count}",
            array_keys($summary),
            $summary
        )));

        $began = array_filter(array_column($results, 'began'));

        if ($began !== []) {
            $spread = (max($began) - min($began)) * 1000;

            $this->line(sprintf('Độ lệch giờ xuất phát giữa các tiến trình: %.0f ms%s', $spread, $spread > 300
                ? ' (hơi lớn, mức đụng nhau có thể yếu: thử lại hoặc giảm --workers)'
                : ''));
        }

        $allPassed = true;
        $rows      = [];

        foreach ($checks as [$label, $actual, $expected]) {
            $ok         = $actual === $expected;
            $allPassed  = $allPassed && $ok;
            $rows[]     = [$label, $actual, $expected, $ok ? 'ĐẠT' : 'SAI'];
        }

        $this->table(['Kiểm tra', 'Thực tế', 'Mong đợi', 'Kết quả'], $rows);

        foreach (array_unique(array_map(fn ($r) => ($r['type'] ?? '?') . ': ' . ($r['message'] ?? ''), $systemErrors)) as $line) {
            $this->warn('  Lỗi hệ thống: ' . $line);
        }

        return $allPassed;
    }

    // ---------- Dữ liệu tạm ----------

    private function makeProduct(int $stock): Product
    {
        return Product::create([
            'category_id'    => Category::whereNotNull('parent_id')->value('id'),
            'brand_id'       => Brand::value('id'),
            'name'           => 'RACE ' . Str::upper(Str::random(5)),
            'sku'            => self::PREFIX . Str::upper(Str::random(8)),
            'price'          => 100000,
            'stock_quantity' => $stock,
        ]);
    }

    private function makeCoupon(): Coupon
    {
        return Coupon::unguarded(fn () => Coupon::create([
            'code'        => self::PREFIX . Str::upper(Str::random(6)),
            'type'        => Coupon::TYPE_FIXED,
            'value'       => 1000,
            'min_order'   => 0,
            'usage_limit' => 1,
            'used_count'  => 0,
            'starts_at'   => null,
            'expires_at'  => null,
            'is_active'   => true,
        ]));
    }

    /** Xóa mọi dữ liệu thử (nhận biết bằng tiền tố RACETEST-), kể cả sót từ lần chạy bị ngắt giữa chừng. */
    private function cleanup(): void
    {
        $productIds = Product::withTrashed()->where('sku', 'like', self::PREFIX . '%')->pluck('id');
        $orderIds   = OrderItem::whereIn('product_id', $productIds)->pluck('order_id')->unique();

        OrderStatusHistory::whereIn('order_id', $orderIds)->delete();
        OrderItem::whereIn('order_id', $orderIds)->delete();
        Order::whereIn('id', $orderIds)->delete();
        Product::withTrashed()->whereIn('id', $productIds)->forceDelete();
        Coupon::where('code', 'like', self::PREFIX . '%')->delete();
    }
}