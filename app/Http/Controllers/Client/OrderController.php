<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách "Đơn hàng của tôi" cho khách hàng đã đăng nhập.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->query('status', 'all');

        $query = $user->orders()
            ->with(['items.product', 'coupon'])
            ->latest('id');

        if ($status !== 'all' && in_array($status, [
            Order::STATUS_PENDING,
            Order::STATUS_SHIPPING,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ])) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(8)->withQueryString();

        // Đếm số lượng đơn cho từng trạng thái
        $counts = [
            'all'                    => $user->orders()->count(),
            Order::STATUS_PENDING   => $user->orders()->where('status', Order::STATUS_PENDING)->count(),
            Order::STATUS_SHIPPING  => $user->orders()->where('status', Order::STATUS_SHIPPING)->count(),
            Order::STATUS_COMPLETED => $user->orders()->where('status', Order::STATUS_COMPLETED)->count(),
            Order::STATUS_CANCELLED => $user->orders()->where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('client.orders.index', compact('orders', 'status', 'counts'));
    }

    /**
     * Hiển thị chi tiết một đơn hàng của khách hàng.
     */
    public function show(Request $request, string $code): View
    {
        $order = $request->user()->orders()
            ->with(['items.product', 'coupon', 'histories'])
            ->where('code', $code)
            ->firstOrFail();

        return view('client.orders.show', compact('order'));
    }

    /**
     * Khách hàng tự hủy đơn khi đơn còn ở trạng thái Chờ xử lý (pending).
     */
    public function cancel(Request $request, string $code, OrderService $orderService): RedirectResponse
    {
        $order = $request->user()->orders()
            ->where('code', $code)
            ->firstOrFail();

        try {
            $orderService->cancelOrder($order, $request->user());

            return back()->with('success', "Đã hủy đơn hàng «{$order->code}» thành công. Số lượng sản phẩm đã được hoàn lại kho.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mua lại các sản phẩm trong đơn cũ: nạp lại vào giỏ hàng.
     */
    public function reorder(Request $request, string $code, CartService $cartService): RedirectResponse
    {
        $order = $request->user()->orders()
            ->with('items')
            ->where('code', $code)
            ->firstOrFail();

        $addedCount = 0;
        foreach ($order->items as $item) {
            $result = $cartService->add($item->product_id, $item->quantity);
            if ($result['success']) {
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            return redirect()->route('cart.index')->with('success', "Đã thêm các sản phẩm từ đơn «{$order->code}» vào giỏ hàng!");
        }

        return back()->with('error', 'Không thể mua lại: sản phẩm có thể đã ngừng bán hoặc hết hàng.');
    }
}
