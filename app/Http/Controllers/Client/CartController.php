<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Hiển thị trang giỏ hàng.
     */
    public function index(Request $request): View|JsonResponse
    {
        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->getSubtotal();
        $shipping = $this->cartService->getShippingFee();
        $total = $this->cartService->getTotal();
        $count = $this->cartService->getCount();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart' => $cart,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'count' => $count,
            ]);
        }

        return view('client.cart.index', compact('cart', 'subtotal', 'shipping', 'total', 'count'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $result = $this->cartService->add($validated['product_id'], $quantity);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer',
        ]);

        $result = $this->cartService->update($validated['product_id'], (int) $validated['quantity']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng.
     */
    public function remove(int|string $id, Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->cartService->remove($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 404);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Xóa toàn bộ sản phẩm trong giỏ hàng.
     */
    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $this->cartService->clear();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã làm trống giỏ hàng thành công.',
                'cartCount' => 0,
                'subtotal' => 0,
                'shipping' => 0,
                'total' => 0,
                'cart' => [],
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã làm trống giỏ hàng thành công.');
    }
}
