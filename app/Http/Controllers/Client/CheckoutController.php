<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Hiển thị trang thanh toán đơn hàng (Checkout).
     */
    public function index(Request $request, CartService $cartService): View|RedirectResponse
    {
        $cart = $cartService->getCart();

        if (empty($cart)) {
            return redirect()->route('cart.index')->with(
                'error',
                'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi thanh toán!'
            );
        }

        $subtotal = $cartService->getSubtotal();
        $shipping = $cartService->getShippingFee();
        $total = $cartService->getTotal();
        $user = $request->user();

        return view('client.checkout.index', compact('cart', 'subtotal', 'shipping', 'total', 'user'));
    }

    /**
     * Xử lý đặt hàng: xác thực thông tin giao hàng và gọi OrderService.
     */
    public function store(Request $request, CartService $cartService, OrderService $orderService): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_name'    => ['required', 'string', 'max:100'],
            'phone'            => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'note'             => ['nullable', 'string', 'max:500'],
            'coupon_code'      => ['nullable', 'string', 'max:50'],
        ], [
            'receiver_name.required'    => 'Vui lòng nhập họ và tên người nhận hàng.',
            'phone.required'            => 'Vui lòng nhập số điện thoại người nhận.',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng chi tiết.',
        ]);

        $cart = $cartService->getCart();
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $cartItems = array_map(fn ($item) => [
            'product_id' => (int) $item['id'],
            'quantity'   => (int) $item['quantity'],
        ], array_values($cart));

        try {
            $order = $orderService->createOrder(
                $request->user(),
                $cartItems,
                [
                    'receiver_name'    => $validated['receiver_name'],
                    'phone'            => $validated['phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'note'             => $validated['note'] ?? null,
                ],
                $validated['coupon_code'] ?? null
            );

            // Xóa sạch giỏ hàng sau khi đặt thành công
            $cartService->clear();

            return redirect()->route('checkout.success', $order->code)
                ->with('success', "Đặt hàng thành công! Mã đơn hàng: {$order->code}");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Màn hình cảm ơn và xác nhận đơn hàng thành công.
     */
    public function success(Request $request, string $code): View
    {
        $order = Order::query()
            ->with(['items.product', 'coupon', 'statusHistories'])
            ->where('code', $code)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('client.checkout.success', compact('order'));
    }
}
