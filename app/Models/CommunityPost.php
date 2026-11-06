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
use Illuminate\Support\Str;

class CommunityPost extends Model
{
    use HasFactory, GeneratesSlug;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN    = 'hidden';

    public const STYLE_MINIMALIST = 'minimalist';
    public const STYLE_CYBERPUNK  = 'cyberpunk_rgb';
    public const STYLE_WHITE      = 'white_aesthetic';
    public const STYLE_WOOD       = 'wood_classic';

    public const STYLES = [
        self::STYLE_MINIMALIST => [
            'name'        => 'Tối giản (Minimalist)',
            'slug'        => 'minimalist',
            'badge_color' => 'bg-slate-100 text-slate-800 border-slate-200',
            'icon'        => '◽',
        ],
        self::STYLE_CYBERPUNK => [
            'name'        => 'Cyberpunk / RGB',
            'slug'        => 'cyberpunk_rgb',
            'badge_color' => 'bg-purple-100 text-purple-800 border-purple-200',
            'icon'        => '⚡',
        ],
        'rgb' => [
            'name'        => 'Cyberpunk / RGB',
            'slug'        => 'cyberpunk_rgb',
            'badge_color' => 'bg-purple-100 text-purple-800 border-purple-200',
            'icon'        => '⚡',
        ],
        self::STYLE_WHITE => [
            'name'        => 'White Aesthetic',
            'slug'        => 'white_aesthetic',
            'badge_color' => 'bg-sky-50 text-sky-800 border-sky-200',
            'icon'        => '✨',
        ],
        self::STYLE_WOOD => [
            'name'        => 'Gỗ & Cổ điển (Wood & Warm)',
            'slug'        => 'wood_classic',
            'badge_color' => 'bg-amber-100 text-amber-900 border-amber-200',
            'icon'        => '🪵',
        ],
        'vintage' => [
            'name'        => 'Gỗ & Cổ điển (Vintage / Retro)',
            'slug'        => 'wood_classic',
            'badge_color' => 'bg-amber-100 text-amber-900 border-amber-200',
            'icon'        => '🪵',
        ],
        'dark_mode' => [
            'name'        => 'Dark Mode Huyền Bí',
            'slug'        => 'dark_mode',
            'badge_color' => 'bg-gray-800 text-gray-100 border-gray-700',
            'icon'        => '🌑',
        ],
    ];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'content',
        'image',
        'style',
        'status',
        'likes_count',
        'comments_count',
        'views_count',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'likes_count'    => 'integer',
            'comments_count' => 'integer',
            'views_count'    => 'integer',
            'is_featured'    => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'post_product_tags')
            ->withTimestamps();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->latest();
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $user->id);
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getStyleInfoAttribute(): array
    {
        return self::STYLES[$this->style] ?? [
            'name'        => 'Phong cách khác',
            'slug'        => $this->style,
            'badge_color' => 'bg-gray-100 text-gray-800 border-gray-200',
            'icon'        => '🏷️',
        ];
    }

    public function getDisplayImageAttribute(): string
    {
        if (!$this->image) return 'https://placehold.co/800x600/png?text=GearHub';

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (Str::startsWith($this->image, ['images/', 'storage/'])) {
            return asset($this->image);
        }

        return asset('storage/' . $this->image);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->display_image);
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->attributes['description'] ?? $this->attributes['content'] ?? null;
    }

    public function getContentAttribute(): ?string
    {
        return $this->attributes['content'] ?? $this->attributes['description'] ?? null;
    }
}
