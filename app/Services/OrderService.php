<?php

namespace App\Services;

use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Order\InsufficientStockException;
use App\Exceptions\Order\InvalidCouponException;
use App\Exceptions\Order\InvalidOrderTransitionException;
use App\Exceptions\Order\ProductUnavailableException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

/**
 * Nghiệp vụ đặt hàng và tồn kho, dùng chung cho Storefront (A) và Admin (B).
 *
 * Mọi ngoại lệ nghiệp vụ có thông báo tiếng Việt, hiển thị thẳng bằng $e->getMessage().
 * InvalidArgumentException và LogicException là lỗi lập trình: đừng hiển thị cho khách, hãy sửa chỗ gọi.
 */
class OrderService
{
    public function __construct(private readonly CouponService $coupons)
    {
    }

    /**
     * Tạo đơn COD trong một transaction: khóa dòng sản phẩm, trừ kho, áp mã giảm giá, lưu bản sao tên và giá.
     * Tổng tiền = tạm tính − giảm giá (không có phí ship). Ghi dòng nhật ký đầu tiên (tạo đơn).
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $cartItems
     *         Mọi trường khác (kể cả giá) đều bị bỏ qua: giá luôn được tính lại từ CSDL.
     * @param  array{receiver_name: string, phone: string, shipping_address: string, note?: string|null}  $shippingData
     * @param  string|null  $couponCode  Mã giảm giá (trống thì bỏ qua)
     * @return Order  Đơn mới ở trạng thái pending, đã nạp quan hệ items
     *
     * @throws EmptyCartException            Giỏ hàng trống
     * @throws ProductUnavailableException   Sản phẩm đã xóa mềm, tắt hiển thị hoặc không tồn tại
     * @throws InsufficientStockException    Không đủ tồn kho (có productName và available)
     * @throws InvalidCouponException        Mã không hợp lệ (message là lý do cụ thể)
     * @throws InvalidArgumentException      Dòng giỏ hàng hoặc thông tin nhận hàng sai cấu trúc (lỗi lập trình)
     */
    public function createOrder(User $user, array $cartItems, array $shippingData, ?string $couponCode = null): Order
    {
        $this->assertShippingData($shippingData);
        $lines = $this->normalizeCart($cartItems);

        return DB::transaction(function () use ($user, $lines, $shippingData, $couponCode) {
            // Khóa các dòng sản phẩm theo thứ tự id để hai đơn đồng thời không khóa chéo nhau
            $products = Product::withTrashed()
                ->whereIn('id', array_keys($lines))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 1. Kiểm tra sản phẩm và tồn kho, dựng dòng đơn từ giá trong CSDL
            $items    = [];
            $subtotal = 0;

            foreach ($lines as $productId => $quantity) {
                $product = $products->get($productId);

                if ($product === null || $product->trashed() || ! $product->is_active) {
                    throw new ProductUnavailableException($product?->name ?? "#{$productId}");
                }

                if ($product->stock_quantity < $quantity) {
                    throw new InsufficientStockException($product->name, $product->stock_quantity);
                }

                $unitPrice = (int) $product->effective_price;

                $items[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $unitPrice,
                    'quantity'     => $quantity,
                ];

                $subtotal += $unitPrice * $quantity;
            }

            // 2. Kiểm tra mã giảm giá trước khi đụng vào kho
            $coupon   = null;
            $discount = 0;
            $code     = trim((string) $couponCode);

            if ($code !== '') {
                $coupon = $this->coupons->find($code);
                $this->coupons->assertUsable($coupon, $subtotal);
                $discount = $this->coupons->calculateDiscount($coupon, $subtotal);
            }

            // 3. Trừ kho bằng câu UPDATE có điều kiện: an toàn ngay cả khi dữ liệu đã cũ
            foreach ($items as $item) {
                $updated = Product::query()
                    ->whereKey($item['product_id'])
                    ->where('stock_quantity', '>=', $item['quantity'])
                    ->decrement('stock_quantity', $item['quantity']);

                if ($updated === 0) {
                    $available = (int) Product::query()->whereKey($item['product_id'])->value('stock_quantity');

                    throw new InsufficientStockException($item['product_name'], $available);
                }
            }

            // 4. Ghi nhận lượt dùng mã (thất bại thì toàn bộ transaction, kể cả kho, được hoàn tác)
            if ($coupon !== null) {
                $this->coupons->redeem($coupon);
            }

            // 5. Tạo đơn, các dòng đơn và dòng nhật ký đầu tiên
            $order = Order::create([
                'code'             => $this->generateCode(),
                'user_id'          => $user->id,
                'coupon_id'        => $coupon?->id,
                'receiver_name'    => trim($shippingData['receiver_name']),
                'phone'            => trim($shippingData['phone']),
                'shipping_address' => trim($shippingData['shipping_address']),
                'note'             => filled($shippingData['note'] ?? null) ? trim($shippingData['note']) : null,
                'payment_method'   => 'cod',
                'status'           => Order::STATUS_PENDING,
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'total'            => $subtotal - $discount,
            ]);

            $order->items()->createMany($items);

            $this->recordStatus($order, null, Order::STATUS_PENDING, $user);

            return $order->load('items');
        });
    }

    /**
     * Hủy đơn và hoàn kho (cả tồn kho lẫn lượt dùng mã giảm giá). Ghi nhật ký.
     * Khách chỉ hủy được đơn của mình khi còn pending; admin hủy được đơn pending hoặc shipping.
     *
     * @throws AuthorizationException           Khách hủy đơn của người khác
     * @throws InvalidOrderTransitionException  Đơn không ở trạng thái được phép hủy
     */
    public function cancelOrder(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            // Đọc lại và khóa dòng đơn: hai yêu cầu hủy đồng thời thì chỉ yêu cầu đầu tiên hoàn kho
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if (! $actor->isAdmin() && (int) $locked->user_id !== (int) $actor->id) {
                throw new AuthorizationException('Bạn không có quyền hủy đơn hàng này.');
            }

            $allowed = $actor->isAdmin()
                ? $locked->canTransitionTo(Order::STATUS_CANCELLED)
                : $locked->status === Order::STATUS_PENDING;

            if (! $allowed) {
                throw new InvalidOrderTransitionException($locked->status, Order::STATUS_CANCELLED);
            }

            $locked->load('items');

            // withTrashed: sản phẩm đã xóa mềm vẫn được hoàn kho để khôi phục lại có số liệu đúng
            foreach ($locked->items->sortBy('product_id') as $item) {
                Product::withTrashed()->whereKey($item->product_id)->increment('stock_quantity', $item->quantity);
            }

            if ($locked->coupon_id !== null) {
                $coupon = Coupon::find($locked->coupon_id);

                if ($coupon !== null) {
                    $this->coupons->release($coupon);
                }
            }

            $from = $locked->status;

            $locked->update(['status' => Order::STATUS_CANCELLED]);
            $this->recordStatus($locked, $from, Order::STATUS_CANCELLED, $actor);

            return $locked;
        });
    }

    /**
     * Đổi trạng thái theo luồng pending → shipping → completed (Admin). Ghi nhật ký.
     * Việc hủy đi qua cancelOrder() vì cần hoàn kho.
     *
     * @param  User|null  $actor  Người thực hiện, ghi vào nhật ký (trống thì ghi là hệ thống)
     *
     * @throws InvalidOrderTransitionException  Chuyển trạng thái không hợp lệ
     * @throws LogicException                   Gọi nhầm với trạng thái cancelled (lỗi lập trình)
     */
    public function updateStatus(Order $order, string $newStatus, ?User $actor = null): Order
    {
        if ($newStatus === Order::STATUS_CANCELLED) {
            throw new LogicException('Dùng OrderService::cancelOrder() để hủy đơn, vì việc hủy phải hoàn kho.');
        }

        return DB::transaction(function () use ($order, $newStatus, $actor) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->canTransitionTo($newStatus)) {
                throw new InvalidOrderTransitionException($locked->status, $newStatus);
            }

            $from = $locked->status;

            $locked->update(['status' => $newStatus]);
            $this->recordStatus($locked, $from, $newStatus, $actor);

            return $locked;
        });
    }

    /** Ghi một dòng nhật ký. Luôn gọi bên trong transaction đổi trạng thái. */
    private function recordStatus(Order $order, ?string $from, string $to, ?User $actor): void
    {
        $order->histories()->create([
            'from_status' => $from,
            'to_status'   => $to,
            'changed_by'  => $actor?->id,
        ]);
    }

    /**
     * Gộp các dòng trùng sản phẩm, kiểm tra cấu trúc, sắp theo id tăng dần (thứ tự khóa dòng).
     *
     * @return array<int, int>  [product_id => quantity]
     */
    private function normalizeCart(array $cartItems): array
    {
        $lines = [];

        foreach ($cartItems as $item) {
            $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity  = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);

            if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1) {
                throw new InvalidArgumentException(
                    'Dòng giỏ hàng không hợp lệ: product_id và quantity phải là số nguyên dương.'
                );
            }

            $lines[$productId] = ($lines[$productId] ?? 0) + $quantity;
        }

        if ($lines === []) {
            throw new EmptyCartException();
        }

        ksort($lines);

        return $lines;
    }

    private function assertShippingData(array $shippingData): void
    {
        foreach (['receiver_name', 'phone', 'shipping_address'] as $key) {
            if (blank($shippingData[$key] ?? null)) {
                throw new InvalidArgumentException("Thiếu thông tin nhận hàng: {$key}.");
            }
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'GH-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('code', $code)->exists());

        return $code;
    }
}