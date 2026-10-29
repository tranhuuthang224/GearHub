<?php

namespace App\Exceptions\Order;

use DomainException;

class EmptyCartException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Giỏ hàng đang trống.');
    }
}