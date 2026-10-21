<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory, GeneratesSlug;

    protected $fillable = ['name', 'slug', 'logo'];

    protected function logoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->logo ? asset('storage/' . $this->logo) : null
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}