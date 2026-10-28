<?php

namespace Tests\Feature;

use App\Models\Order;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    private function order(string $status): Order
    {
        return new Order(['status' => $status]);
    }

    public function test_pending_can_go_to_shipping_or_cancelled_only(): void
    {
        $order = $this->order(Order::STATUS_PENDING);

        $this->assertTrue($order->canTransitionTo(Order::STATUS_SHIPPING));
        $this->assertTrue($order->canTransitionTo(Order::STATUS_CANCELLED));
        $this->assertFalse($order->canTransitionTo(Order::STATUS_COMPLETED));
    }

    public function test_shipping_can_go_to_completed_or_cancelled_only(): void
    {
        $order = $this->order(Order::STATUS_SHIPPING);

        $this->assertTrue($order->canTransitionTo(Order::STATUS_COMPLETED));
        $this->assertTrue($order->canTransitionTo(Order::STATUS_CANCELLED));
        $this->assertFalse($order->canTransitionTo(Order::STATUS_PENDING));
    }

    public function test_final_states_cannot_change(): void
    {
        foreach ([Order::STATUS_COMPLETED, Order::STATUS_CANCELLED] as $final) {
            $order = $this->order($final);

            foreach (array_keys(Order::STATUS_LABELS) as $target) {
                $this->assertFalse($order->canTransitionTo($target), "{$final} → {$target}");
            }
        }
    }

    public function test_every_status_has_a_label_and_a_transition_entry(): void
    {
        foreach ([Order::STATUS_PENDING, Order::STATUS_SHIPPING, Order::STATUS_COMPLETED, Order::STATUS_CANCELLED] as $status) {
            $this->assertArrayHasKey($status, Order::STATUS_LABELS);
            $this->assertArrayHasKey($status, Order::TRANSITIONS);
            $this->assertSame(Order::STATUS_LABELS[$status], $this->order($status)->statusLabel());
        }
    }
}