<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Nguồn duy nhất cho "ai được làm gì với đơn hàng".
 * Giao diện dùng @can('cancel', $order); Controller dùng Gate::authorize('view', $order).
 */
class OrderPolicy
{
    /** Chủ đơn hoặc admin. Người khác nhận 404 để không lộ đơn có tồn tại. */
    public function view(User $user, Order $order): Response|bool
    {
        if ($user->isAdmin() || (int) $order->user_id === (int) $user->id) {
            return true;
        }

        return Response::denyAsNotFound();
    }

    /** Khớp quy tắc của OrderService::cancelOrder, để nút Hủy chỉ hiện khi hủy được. */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return $order->canTransitionTo(Order::STATUS_CANCELLED);
        }

        return (int) $order->user_id === (int) $user->id
            && $order->status === Order::STATUS_PENDING;
    }

    /** Duyệt giao hàng, hoàn thành: chỉ admin. */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}