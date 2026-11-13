<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_SHIPPING  = 'shipping';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Nhãn hiển thị cho giao diện (huy hiệu trạng thái ở Storefront và Admin). */
    public const STATUS_LABELS = [
        self::STATUS_PENDING   => 'Chờ xử lý',
        self::STATUS_SHIPPING  => 'Đang giao',
        self::STATUS_COMPLETED => 'Hoàn thành',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    /** Luồng chuyển trạng thái hợp lệ (đề án mục 5.3). */
    public const TRANSITIONS = [
        self::STATUS_PENDING   => [self::STATUS_SHIPPING, self::STATUS_CANCELLED],
        self::STATUS_SHIPPING  => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    public const PAYMENT_METHOD_COD           = 'cod';
    public const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    public const PAYMENT_METHOD_LABELS = [
        self::PAYMENT_METHOD_COD           => 'Thanh toán khi nhận hàng (COD)',
        self::PAYMENT_METHOD_BANK_TRANSFER => 'Chuyển khoản ngân hàng (VietQR)',
    ];

    /**
     * Kiểm tra đơn hàng có thanh toán bằng chuyển khoản hay không.
     */
    public function isBankTransfer(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_BANK_TRANSFER;
    }

    /**
     * Lấy nhãn phương thức thanh toán thân thiện.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHOD_LABELS[$this->payment_method] ?? strtoupper((string) $this->payment_method);
    }

    protected $fillable = [
        'code',
        'idempotency_key',
        'user_id',
        'coupon_id',
        'receiver_name',
        'phone',
        'shipping_address',
        'note',
        'payment_method',
        'status',
        'subtotal',
        'shipping_fee',
        'discount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'     => 'integer',
            'shipping_fee' => 'integer',
            'discount'     => 'integer',
            'total'        => 'integer',
        ];
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? (string) $this->status;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Nhật ký trạng thái, cũ nhất trước. */
    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('id');
    }
}