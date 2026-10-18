<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,   // trước Product: Product cần category_id
            BrandSeeder::class,      // trước Product: Product cần brand_id
            ProductSeeder::class,
        ]);
    }
}