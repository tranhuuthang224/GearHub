<?php

namespace App\Exceptions\Order;

use DomainException;

/** Sản phẩm không tồn tại, đã bị xóa mềm hoặc đang tắt hiển thị. */
class ProductUnavailableException extends DomainException
{
    public function __construct(public readonly string $productName)
    {
        parent::__construct("Sản phẩm «{$productName}» hiện không còn được bán.");
    }
}