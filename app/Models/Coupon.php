<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED   = 'fixed';

    public const TYPE_LABELS = [
        self::TYPE_PERCENT => 'Phần trăm (%)',
        self::TYPE_FIXED   => 'Số tiền cố định (₫)',
    ];

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_EXHAUSTED = 'exhausted';

    public const STATUS_LABELS = [
        self::STATUS_ACTIVE    => 'Đang hoạt động',
        self::STATUS_INACTIVE  => 'Đã tắt',
        self::STATUS_SCHEDULED => 'Chưa bắt đầu',
        self::STATUS_EXPIRED   => 'Hết hạn',
        self::STATUS_EXHAUSTED => 'Hết lượt',
    ];

    // used_count cố ý KHÔNG nằm ở đây: chỉ tăng bằng increment() khi đặt hàng thành công
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order',
        'usage_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'       => 'integer',
            'min_order'   => 'integer',
            'usage_limit' => 'integer',
            'used_count'  => 'integer',
            'starts_at'   => 'datetime',
            'expires_at'  => 'datetime',
            'is_active'   => 'boolean',
        ];
    }

    /**
     * Tình trạng hiện tại của mã. Thứ tự kiểm tra: tắt, chưa bắt đầu, hết hạn, hết lượt.
     * CouponService (Thứ 4) dùng lại hàm này để quyết định mã có dùng được không.
     */
    public function status(?CarbonInterface $at = null): string
    {
        $at ??= now();

        return match (true) {
            ! $this->is_active                                           => self::STATUS_INACTIVE,
            $this->starts_at !== null && $this->starts_at->gt($at)       => self::STATUS_SCHEDULED,
            $this->expires_at !== null && $this->expires_at->lt($at)     => self::STATUS_EXPIRED,
            $this->usage_limit !== null && $this->used_count >= $this->usage_limit => self::STATUS_EXHAUSTED,
            default                                                      => self::STATUS_ACTIVE,
        };
    }

    public function statusLabel(?CarbonInterface $at = null): string
    {
        return self::STATUS_LABELS[$this->status($at)];
    }

    /** "10%" hoặc "50.000 ₫". */
    public function discountLabel(): string
    {
        return $this->type === self::TYPE_PERCENT
            ? $this->value . '%'
            : number_format($this->value, 0, ',', '.') . ' ₫';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}