<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    use HasFactory, GeneratesSlug;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN    = 'hidden';

    public const STYLES = [
        'minimalist'      => 'Minimalist',
        'rgb'             => 'RGB Cyberpunk',
        'white_aesthetic' => 'White Aesthetic',
        'dark_mode'       => 'Dark Mode',
        'vintage'         => 'Vintage / Retro',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'image',
        'style',
        'status',
        'likes_count',
        'comments_count',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->image) return null;
            if (str_starts_with($this->image, 'http')) return $this->image;
            return asset('storage/' . $this->image);
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->latest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    /** Danh sách các sản phẩm GearHub được gắn thẻ trong bài viết */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'post_product_tags');
    }

    /** Kiểm tra xem User hiện tại đã like bài này chưa */
    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}