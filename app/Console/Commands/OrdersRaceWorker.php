<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Throwable;

/** Tiến trình con của orders:race-test. Không dùng trực tiếp. */
class OrdersRaceWorker extends Command
{
    protected $signature = 'orders:race-worker {--payload= : Công việc (JSON mã hóa base64)}';

    protected $description = 'Tiến trình con của orders:race-test';

    protected $hidden = true;

    public function handle(OrderService $orders): int
    {
        $job = json_decode((string) base64_decode((string) $this->option('payload'), true), true);

        if (! is_array($job)) {
            $this->line('RESULT:' . json_encode(['ok' => false, 'type' => 'BadPayload', 'message' => 'Payload không hợp lệ']));

            return self::FAILURE;
        }

        // Chờ đến giờ xuất phát chung để các tiến trình đụng nhau thật
        while (microtime(true) < $job['start_at']) {
            usleep(500);
        }

        $began = microtime(true);

        try {
            $result = ['ok' => true] + $this->perform($orders, $job);
        } catch (Throwable $e) {
            $result = ['ok' => false, 'type' => class_basename($e), 'message' => $e->getMessage()];
        }

        $result['began'] = $began;
        $result['ended'] = microtime(true);

        $this->line('RESULT:' . json_encode($result));

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function perform(OrderService $orders, array $job): array
    {
        $user = User::findOrFail($job['user_id']);

        if ($job['action'] === 'cancel') {
            $orders->cancelOrder(Order::findOrFail($job['order_id']), $user);

            return ['order_id' => $job['order_id']];
        }

        $order = $orders->createOrder($user, $job['cart'], [
            'receiver_name'    => 'Race Test',
            'phone'            => '0900000000',
            'shipping_address' => 'Thử chống đua',
        ], $job['coupon'] ?? null);

        return ['order_id' => $order->id];
    }
}