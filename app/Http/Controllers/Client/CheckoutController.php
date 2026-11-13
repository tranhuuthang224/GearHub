<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Hiển thị trang thanh toán đơn hàng (Checkout).
     */
    public function index(Request $request, CartService $cartService): View|RedirectResponse
    {
        // Tự động kiểm tra và đồng bộ giỏ hàng với trạng thái CSDL mới nhất
        $removedItems = $cartService->syncCart();
        if (!empty($removedItems)) {
            session()->flash('warning', 'Một số sản phẩm không còn khả dụng và đã được tự động gỡ khỏi đơn hàng: ' . implode(', ', $removedItems));
        }

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
        $addresses = $user ? $user->addresses()->get() : collect();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        // Sinh Idempotency Token một lần cho phiên thanh toán
        $checkoutToken = (string) Str::uuid();
        session(['checkout_token' => $checkoutToken]);

        return view('client.checkout.index', compact(
            'cart', 'subtotal', 'shipping', 'total', 'user', 'addresses', 'defaultAddress', 'checkoutToken'
        ));
    }

    /**
     * Xử lý đặt hàng: xác thực thông tin giao hàng và gọi OrderService.
     */
    public function store(Request $request, CartService $cartService, OrderService $orderService): RedirectResponse
    {
        // 1. Chuẩn hóa số điện thoại trước khi xác thực
        if ($request->has('phone')) {
            $normalizedPhone = preg_replace('/[\s.-]/', '', (string) $request->input('phone'));
            $request->merge(['phone' => $normalizedPhone]);
        }

        // 2. Xác thực dữ liệu đầu vào (bắt buộc checkout_token là UUID)
        $validated = $request->validate([
            'checkout_token'   => ['required', 'uuid'],
            'receiver_name'    => ['required', 'string', 'min:2', 'max:100'],
            'phone'            => ['required', 'string', 'regex:/^(0|\+84)[35789][0-9]{8}$/'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:255'],
            'payment_method'   => ['nullable', 'string', 'in:cod,bank_transfer'],
            'note'             => ['nullable', 'string', 'max:500'],
            'coupon_code'      => ['nullable', 'string', 'max:50'],
        ], [
            'checkout_token.required'   => 'Phiên đặt hàng không hợp lệ, vui lòng tải lại trang.',
            'checkout_token.uuid'       => 'Mã phiên đặt hàng không đúng định dạng.',
            'receiver_name.required'    => 'Vui lòng nhập họ và tên người nhận hàng.',
            'receiver_name.min'         => 'Họ và tên người nhận phải có ít nhất 2 ký tự.',
            'phone.required'            => 'Vui lòng nhập số điện thoại người nhận.',
            'phone.regex'               => 'Số điện thoại không đúng định dạng Việt Nam (gồm 10 số, bắt đầu bằng 03, 05, 07, 08, 09 hoặc +84).',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
            'shipping_address.min'      => 'Vui lòng nhập địa chỉ giao hàng cụ thể (tối thiểu 10 ký tự bao gồm số nhà, tên đường, phường/xã, quận/huyện).',
            'payment_method.in'         => 'Phương thức thanh toán đã chọn không hợp lệ.',
        ]);

        $checkoutToken = $validated['checkout_token'];

        // 3. Chống race condition: khóa thao tác checkout theo user/session trong 10 giây (dùng non-blocking ->get())
        $lock = null;
        try {
            $lockKey = 'checkout_lock_user_' . ($request->user()?->id ?? session()->getId());
            $lock = Cache::lock($lockKey, 10);

            if (! $lock->get()) {
                return back()->with('error', 'Đơn hàng đang được hệ thống xử lý, vui lòng không thao tác lặp lại.');
            }
        } catch (\Throwable) {
            // Nếu cache driver không hỗ trợ lock, fallback an toàn dựa vào idempotency_key trong CSDL
        }

        try {
            // Đặt sau khi lấy được khóa: kiểm tra xem request trước đã tạo đơn với token này chưa
            $existingOrder = $request->user()?->orders()
                ->where('idempotency_key', $checkoutToken)
                ->first();

            if ($existingOrder) {
                return redirect()->route('checkout.success', $existingOrder->code)
                    ->with('success', "Đơn hàng của bạn đã được ghi nhận. Mã đơn: {$existingOrder->code}");
            }

            // Kiểm tra token trong session (chống gửi token cũ hoặc token đã tiêu thụ)
            $savedToken = session('checkout_token');
            if ($savedToken !== $checkoutToken) {
                return redirect()->route('cart.index')->with(
                    'error',
                    'Phiên đặt hàng đã kết thúc hoặc không hợp lệ. Vui lòng kiểm tra lại đơn hàng.'
                );
            }

            $cart = $cartService->getCart();
            if (empty($cart)) {
                return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
            }

            $cartItems = array_map(fn ($item) => [
                'product_id' => (int) $item['id'],
                'quantity'   => (int) $item['quantity'],
            ], array_values($cart));

            $order = $orderService->createOrder(
                $request->user(),
                $cartItems,
                [
                    'receiver_name'    => $validated['receiver_name'],
                    'phone'            => $validated['phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'note'             => $validated['note'] ?? null,
                    'payment_method'   => $validated['payment_method'] ?? 'cod',
                    'idempotency_key'  => $checkoutToken,
                ],
                $validated['coupon_code'] ?? null
            );

            // Đặt hàng thành công: tiêu thụ token, xóa sạch giỏ hàng và ép lưu session ngay trước khi nhả lock
            session()->forget('checkout_token');
            $cartService->clear();
            $request->session()->save();

            return redirect()->route('checkout.success', $order->code)
                ->with('success', "Đặt hàng thành công! Mã đơn hàng: {$order->code}");
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $existing = $request->user()?->orders()->where('idempotency_key', $checkoutToken)->first();
            if ($existing) {
                return redirect()->route('checkout.success', $existing->code)
                    ->with('success', "Đơn hàng của bạn đã được ghi nhận. Mã đơn: {$existing->code}");
            }
            report($e);
            return back()->withInput()->with('error', 'Có lỗi hệ thống trong quá trình xử lý đơn hàng, vui lòng thử lại.');
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Có lỗi hệ thống trong quá trình xử lý đơn hàng, vui lòng thử lại.');
        } finally {
            try {
                optional($lock)->release();
            } catch (\Throwable) {
                // An toàn khi cache store không hỗ trợ release
            }
        }
    }

    /**
     * Màn hình cảm ơn và xác nhận đơn hàng thành công.
     */
    public function success(Request $request, string $code): View
    {
        $order = Order::query()
            ->with(['items.product', 'coupon', 'histories'])
            ->where('code', $code)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('client.checkout.success', compact('order'));
    }
}
