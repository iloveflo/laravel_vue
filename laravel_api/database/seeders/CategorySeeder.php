<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Thời trang nam',
                'slug' => 'thoi-trang-nam',
                'description' => 'Các sản phẩm quần áo, giày dép và phụ kiện dành cho nam.',
                'parent_id' => null,
            ],
            [
                'name' => 'Áo nam',
                'slug' => 'ao-nam',
                'description' => 'Các loại áo dành cho nam.',
                'parent_id' => 1,
            ],
            [
                'name' => 'Quần nam',
                'slug' => 'quan-nam',
                'description' => 'Các loại quần dành cho nam.',
                'parent_id' => 1,
            ],
            [
                'name' => 'Giày nam',
                'slug' => 'giay-nam',
                'description' => 'Giày dép dành cho nam.',
                'parent_id' => 1,
            ],
            [
                'name' => 'Thời trang nữ',
                'slug' => 'thoi-trang-nu',
                'description' => 'Các sản phẩm quần áo, giày dép và phụ kiện dành cho nữ.',
                'parent_id' => null,
            ],
            [
                'name' => 'Áo nữ',
                'slug' => 'ao-nu',
                'description' => 'Các loại áo dành cho nữ.',
                'parent_id' => 5,
            ],
            [
                'name' => 'Váy nữ',
                'slug' => 'vay-nu',
                'description' => 'Các loại váy dành cho nữ.',
                'parent_id' => 5,
            ],
            [
                'name' => 'Giày dép',
                'slug' => 'giay-dep',
                'description' => 'Tất cả loại giày dép cho nam và nữ.',
                'parent_id' => null,
            ],
            [
                'name' => 'Phụ kiện',
                'slug' => 'phu-kien',
                'description' => 'Mũ, ví, dây nịt, kính, đồng hồ,…',
                'parent_id' => null,
            ],
            [
                'name' => 'Khác',
                'slug' => 'khac',
                'description' => 'Các sản phẩm khác không thuộc danh mục chính.',
                'parent_id' => null,
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'description' => $cat['description'],
                'parent_id' => $cat['parent_id'],
                'image' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
