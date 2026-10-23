<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN    = 'admin';
    public const ROLE_CUSTOMER = 'customer';

    /** Dùng chung cho LoginRequest và middleware EnsureUserIsActive. */
    public const INACTIVE_MESSAGE = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';

    /**
     * Mặc định trong bộ nhớ, khớp với mặc định của migration.
     * Không có dòng này, user vừa create() có is_active = null cho tới khi nạp lại từ CSDL.
     */
    protected $attributes = [
        'role'      => self::ROLE_CUSTOMER,
        'is_active' => true,
    ];

    // role và is_active cố ý KHÔNG nằm ở đây: khách không được tự sửa qua form
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
}



public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}

public function addresses(): HasMany
{
    return $this->hasMany(Address::class);
}

public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}

}