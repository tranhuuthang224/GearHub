<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait GeneratesSlug
{
    public static function bootGeneratesSlug(): void
    {
        static::saving(function ($model) {
            $nameChanged = $model->exists
                && $model->isDirty('name')
                && ! $model->isDirty('slug');

            if (blank($model->slug) || $nameChanged) {
                $model->slug = static::uniqueSlug($model->name, $model->getKey());
            }
        });
    }

    protected static function uniqueSlug(string $name, mixed $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $i = 2;

        // withoutGlobalScopes: tính cả bản ghi đã xóa mềm, vì UNIQUE trong DB vẫn tính chúng
        while (static::withoutGlobalScopes()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}