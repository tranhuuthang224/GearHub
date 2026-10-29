<?php

namespace App\Services;

use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Order\InsufficientStockException;
use App\Exceptions\Order\InvalidCouponException;
use App\Exceptions\Order\InvalidOrderTransitionException;
use App\Exceptions\Order\ProductUnavailableException;
use App\Models\Order;
use App\Models\User;
use BadMethodCallException;

/**
 * Nghiệp vụ đặt hàng và tồn kho, dùng chung cho Storefront (A) và Admin (B).
 *
 * TRẠNG THÁI: BẢN KHUNG. Chữ ký và ngoại lệ đã chốt, thân hàm sẽ hoàn thiện ở Thứ 5.
 * Mọi ngoại lệ bên dưới có thông báo tiếng Việt, hiển thị thẳng bằng $e->getMessage().
 */
class OrderService
{
    /**
     * Tạo đơn COD: một transaction, khóa dòng sản phẩm, trừ kho, lưu bản sao tên và giá.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $cartItems
     *         KHÔNG kèm giá: giá luôn được tính lại từ CSDL.
     * @param  array{receiver_name: string, phone: string, shipping_address: string}  $shippingData
     * @param  string|null  $couponCode  Mã giảm giá (có thể bỏ trống)
     * @return Order  Đơn mới ở trạng thái pending, đã nạp quan hệ items
     *
     * @throws EmptyCartException            Giỏ hàng trống
     * @throws ProductUnavailableException   Sản phẩm đã xóa mềm, tắt hiển thị hoặc không tồn tại
     * @throws InsufficientStockException    Không đủ tồn kho (có productName và available)
     * @throws InvalidCouponException        Mã không hợp lệ (message là lý do cụ thể)
     */
    public function createOrder(User $user, array $cartItems, array $shippingData, ?string $couponCode = null): Order
    {
        throw new BadMethodCallException('OrderService::createOrder sẽ được hoàn thiện ở Thứ 5.');
    }

    /**
     * Hủy đơn và hoàn kho. Khách chỉ hủy được đơn của mình khi còn pending;
     * admin hủy được đơn pending hoặc shipping.
     *
     * @throws InvalidOrderTransitionException  Đơn không ở trạng thái được phép hủy
     */
    public function cancelOrder(Order $order, User $actor): Order
    {
        throw new BadMethodCallException('OrderService::cancelOrder sẽ được hoàn thiện ở Thứ 5.');
    }

    /**
     * Đổi trạng thái theo luồng pending → shipping → completed (Admin).
     * Việc hủy đi qua cancelOrder() vì cần hoàn kho.
     *
     * @throws InvalidOrderTransitionException  Chuyển trạng thái không hợp lệ
     */
    public function updateStatus(Order $order, string $newStatus): Order
    {
        throw new BadMethodCallException('OrderService::updateStatus sẽ được hoàn thiện ở Thứ 5.');
    }
}