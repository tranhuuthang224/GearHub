<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Concerns\GeneratesPlaceholderImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use GeneratesPlaceholderImages;

    public function run(): void
    {
        $tree = [
            'Chuột'              => ['Chuột gaming', 'Chuột văn phòng'],
            'Bàn phím cơ'        => ['Bàn phím cơ không dây', 'Bàn phím cơ có dây'],
            'Tai nghe'           => ['Tai nghe gaming', 'Tai nghe không dây'],
            'Củ sạc'             => ['Củ sạc nhanh', 'Sạc dự phòng'],
            'Setup góc làm việc' => ['Bàn di chuột', 'Giá đỡ, đèn và phụ kiện'],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = $this->upsert($parentName, null);

            foreach ($children as $childName) {
                $this->upsert($childName, $parent->id);
            }
        }
    }

    private function upsert(string $name, ?int $parentId): Category
    {
        $slug = Str::slug($name);

        return Category::updateOrCreate(
            ['slug' => $slug],
            [
                'name'      => $name,
                'parent_id' => $parentId,
                'image'     => $this->placeholder("categories/{$slug}.svg", $name),
            ]
        );
    }
}