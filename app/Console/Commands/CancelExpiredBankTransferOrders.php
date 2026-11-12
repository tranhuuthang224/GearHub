<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;

class CancelExpiredBankTransferOrders extends Command
{
    /**
     * Tên và chữ ký của lệnh console.
     */
    protected $signature = 'orders:cancel-expired-bank-transfers
                            {--hours=24 : Số giờ tối đa chờ chuyển khoản trước khi tự động hủy}';

    /**
     * Mô tả chức năng lệnh.
     */
    protected $description = 'Tự động hủy các đơn hàng thanh toán chuyển khoản quá hạn chưa thanh toán và hoàn trả tồn kho';

    /**
     * Thực thi lệnh console.
     */
    public function handle(OrderService $orderService): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $threshold = now()->subHours($hours);

        $expiredOrders = Order::query()
            ->where('payment_method', Order::PAYMENT_METHOD_BANK_TRANSFER)
            ->where('status', Order::STATUS_PENDING)
            ->where('created_at', '<=', $threshold)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info("Không có đơn chuyển khoản nào quá hạn {$hours} giờ cần hủy.");
            return self::SUCCESS;
        }

        $cancelledCount = 0;
        foreach ($expiredOrders as $order) {
            try {
                $orderService->cancelOrder($order, null);
                $cancelledCount++;
                $this->line("  ✓ Đã hủy và hoàn kho đơn: {$order->code} (tạo lúc {$order->created_at->format('d/m/Y H:i')})");
            } catch (\Throwable $e) {
                $this->error("  ✗ Lỗi khi hủy đơn {$order->code}: {$e->getMessage()}");
            }
        }

        $this->info("Đã tự động hủy thành công {$cancelledCount}/{$expiredOrders->count()} đơn hàng chuyển khoản quá {$hours} giờ.");

        return self::SUCCESS;
    }
}
