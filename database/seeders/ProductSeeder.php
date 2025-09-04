<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Electronics
            [
                'name' => 'Smartphone XYZ',
                'description' => 'A high-end smartphone with amazing features',
                'price' => 999.99,
                'stock' => 50,
                'category_id' => 1,
            ],
            [
                'name' => 'Laptop Pro',
                'description' => 'Powerful laptop for professionals',
                'price' => 1499.99,
                'stock' => 30,
                'category_id' => 1,
            ],
            [
                'name' => 'Wireless Earbuds',
                'description' => 'Premium wireless earbuds with noise cancellation',
                'price' => 199.99,
                'stock' => 100,
                'category_id' => 1,
            ],
            
            // Clothing
            [
                'name' => 'Men\'s T-Shirt',
                'description' => 'Comfortable cotton t-shirt for men',
                'price' => 29.99,
                'stock' => 200,
                'category_id' => 2,
            ],
            [
                'name' => 'Women\'s Jeans',
                'description' => 'Stylish and durable jeans for women',
                'price' => 59.99,
                'stock' => 150,
                'category_id' => 2,
            ],
            
            // Home & Kitchen
            [
                'name' => 'Coffee Maker',
                'description' => 'Automatic coffee maker with timer',
                'price' => 89.99,
                'stock' => 40,
                'category_id' => 3,
            ],
            [
                'name' => 'Blender',
                'description' => 'High-speed blender for smoothies and more',
                'price' => 69.99,
                'stock' => 35,
                'category_id' => 3,
                'coming_soon' => true,
                'featured'=>true,
            ],
            
            // Books
            [
                'name' => 'The Great Novel',
                'description' => 'Bestselling novel by a renowned author',
                'price' => 19.99,
                'stock' => 100,
                'category_id' => 4,
                'coming_soon' => true,
                'featured'=>true,
            ],
            [
                'name' => 'Cookbook Collection',
                'description' => 'Collection of recipes from around the world',
                'price' => 34.99,
                'stock' => 60,
                'category_id' => 4,
                'coming_soon' => true,
                'featured'=>true,
            ],
            [
                'name' => 'dasdas Collection',
                'description' => 'Collection of recipes from around the world',
                'price' => 34.99,
                'stock' => 60,
                'category_id' => 5,
                'coming_soon' => true,
                'featured'=>true,
            ],
            
            // Sports & Outdoors
            [
                'name' => 'Yoga Mat',
                'description' => 'Non-slip yoga mat for home workouts',
                'price' => 24.99,
                'stock' => 80,
                'category_id' => 5,
                'featured'=>true,
                'coming_soon' => true,
            ],
            [
                'name' => 'Camping Tent',
                'description' => 'Waterproof tent for 4 people',
                'price' => 129.99,
                'stock' => 25,
                'category_id' => 5,
                'featured' => false,
                'coming_soon' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}