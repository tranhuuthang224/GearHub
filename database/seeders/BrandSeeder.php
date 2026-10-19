<?php

namespace Database\Seeders;

use App\Models\Brand;
use Database\Seeders\Concerns\GeneratesPlaceholderImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    use GeneratesPlaceholderImages;

    public function run(): void
    {
        $brands = [
            'Keychron', 'Logitech', 'Razer', 'Anker', 'SteelSeries',
            'Corsair', 'HyperX', 'Akko', 'Baseus', 'Ugreen',
        ];

        foreach ($brands as $name) {
            $slug = Str::slug($name);

            Brand::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'logo' => $this->placeholder("brands/{$slug}.svg", $name),
                ]
            );
        }
    }
}