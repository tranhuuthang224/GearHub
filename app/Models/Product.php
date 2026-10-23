<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, GeneratesSlug;

    /** Dưới ngưỡng này (và còn hàng) thì coi là "sắp hết hàng". Dashboard Tuần 7 dùng lại. */
    public const LOW_STOCK_THRESHOLD = 5;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'description',
        'specs',
        'price',
        'sale_price',
        'stock_quantity',
        'thumbnail',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specs'          => 'array',
            'price'          => 'integer',
            'sale_price'     => 'integer',
            'stock_quantity' => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    /** Giá bán thực tế: giá khuyến mãi nếu có, ngược lại là giá gốc. */
    protected function effectivePrice(): Attribute
    {
        return Attribute::get(fn () => $this->sale_price ?? $this->price);
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null
        );
    }

    /** Sản phẩm đang bật hiển thị (chưa tính xóa mềm, SoftDeletes tự lo). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Sản phẩm của một danh mục, gồm cả các danh mục con của nó. */
    public function scopeInCategory(Builder $query, Category|int $category): Builder
    {
        $id = $category instanceof Category ? $category->getKey() : $category;

        $ids = Category::where('id', $id)->orWhere('parent_id', $id)->pluck('id');

        return $query->whereIn('category_id', $ids);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity < self::LOW_STOCK_THRESHOLD;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}