<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\Order\InvalidOrderTransitionException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $status = $request->query('status');
        $status = is_string($status) && array_key_exists($status, Order::STATUS_LABELS) ? $status : null;

        $search = $request->query('q');
        $search = is_string($search) ? trim($search) : '';

        $orders = Order::query()
            ->withCount('items')
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(fn ($w) => $w
                    ->where('code', 'like', $like)
                    ->orWhere('receiver_name', 'like', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Trang hiện tại rỗng (vd. đổi bộ lọc khi đang ở trang 3): chuyển về trang cuối còn dữ liệu
        if ($orders->isEmpty() && $orders->currentPage() > 1) {
            return redirect()->route(
                'admin.orders.index',
                array_merge($request->query(), ['page' => $orders->lastPage()])
            );
        }

        // Số đơn theo từng trạng thái, cho các tab lọc
        $counts = Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', [
            'orders'  => $orders,
            'counts'  => $counts,
            'status'  => $status,
            'search'  => $search,
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items.product', 'histories.actor', 'user', 'coupon']);

        return view('admin.orders.show', compact('order'));
    }

    /** Duyệt giao hàng, hoàn thành hoặc hủy. Hủy đi qua cancelOrder() để hoàn kho. */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Order::STATUS_SHIPPING, Order::STATUS_COMPLETED, Order::STATUS_CANCELLED,
            ])],
        ], [
            'status.required' => 'Vui lòng chọn trạng thái mới.',
            'status.in'       => 'Trạng thái không hợp lệ.',
        ]);

        try {
            $updated = $validated['status'] === Order::STATUS_CANCELLED
                ? $this->orders->cancelOrder($order, $request->user())
                : $this->orders->updateStatus($order, $validated['status'], $request->user());
        } catch (InvalidOrderTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Đã chuyển đơn «{$updated->code}» sang «{$updated->statusLabel()}».");
    }
}