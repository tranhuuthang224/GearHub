<?php

namespace App\Exceptions\Order;

use DomainException;

/** Thông báo là lý do cụ thể (hết hạn, hết lượt, chưa đủ giá trị tối thiểu...). */
class InvalidCouponException extends DomainException
{
}