<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    /** Nhật ký chỉ thêm, không sửa: không có cột updated_at. */
    public const UPDATED_AT = null;

    protected $fillable = ['order_id', 'from_status', 'to_status', 'changed_by'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function toLabel(): string
    {
        return Order::STATUS_LABELS[$this->to_status] ?? (string) $this->to_status;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Người thực hiện; trống nếu tài khoản đã bị xóa hoặc do hệ thống ghi. */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}