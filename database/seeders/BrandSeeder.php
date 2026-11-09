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
            'Aula', 'Akko', 'Asus', 'ATK', 'DareU', 'Keychron', 'Logitech', 'MChose',
            'E-Dra', 'Fuhlen', 'Rapoo', 'Acer', 'Lenovo', 'MSI', 'Gigabyte', 'HP',
            'HyperX', 'Razer', 'Corsair', 'Havit', 'Zidii', 'Anker', 'SteelSeries',
            'Baseus', 'Ugreen', 'Samsung', 'LG', 'Dell', 'ViewSonic', 'AOC', 'HKC', 'Dahua'
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