<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shop Shipping Policy
    |--------------------------------------------------------------------------
    |
    | Configuration for free shipping threshold and standard shipping fee.
    |
    */
    'free_shipping_threshold' => (int) env('SHOP_FREE_SHIPPING_THRESHOLD', 500000),
    'shipping_fee'            => (int) env('SHOP_SHIPPING_FEE', 30000),
];
