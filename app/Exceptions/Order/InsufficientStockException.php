<?php

namespace App\Exceptions\Order;

use DomainException;

class InsufficientStockException extends DomainException
{
    public function __construct(
        public readonly string $productName,
        public readonly int $available,
    ) {
        parent::__construct(
            $available > 0
                ? "Sản phẩm «{$productName}» chỉ còn {$available} sản phẩm trong kho."
                : "Sản phẩm «{$productName}» đã hết hàng."
        );
    }
}