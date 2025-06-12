<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
            ],
            [
                'name' => 'Clothing',
                'description' => 'Apparel and fashion items',
            ],
            [
                'name' => 'Home & Kitchen',
                'description' => 'Home appliances and kitchen essentials',
            ],
            [
                'name' => 'Books',
                'description' => 'Books, e-books, and audiobooks',
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}