<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
        'images',
        'photos',
        'is_verified_purchase',
    ];

    protected function casts(): array
    {
        return [
            'rating'               => 'integer',
            'images'               => 'array',
            'photos'               => 'array',
            'is_verified_purchase' => 'boolean',
        ];
    }

    public function getPhotosAttribute(): array
    {
        $val = $this->attributes['photos'] ?? $this->attributes['images'] ?? null;
        if (is_string($val)) {
            return json_decode($val, true) ?: [];
        }
        return is_array($val) ? $val : [];
    }

    public function getImagesAttribute(): array
    {
        return $this->photos;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
