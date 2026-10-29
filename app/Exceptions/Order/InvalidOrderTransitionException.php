<?php

namespace App\Exceptions\Order;

use App\Models\Order;
use DomainException;

class InvalidOrderTransitionException extends DomainException
{
    public function __construct(string $from, string $to)
    {
        $fromLabel = Order::STATUS_LABELS[$from] ?? $from;
        $toLabel   = Order::STATUS_LABELS[$to] ?? $to;

        parent::__construct("Không thể chuyển đơn hàng từ «{$fromLabel}» sang «{$toLabel}».");
    }
}