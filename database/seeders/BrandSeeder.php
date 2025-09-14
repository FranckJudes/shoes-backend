<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Nike est une marque américaine leader dans les sneakers et vêtements de sport.',
                'logo' => '/storage/brands/nike.webp',
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'Adidas, marque allemande iconique, connue pour ses modèles comme Yeezy et Superstar.',
                'logo' => '/storage/brands/adidas.png',
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Jordan',
                'slug' => 'jordan',
                'description' => 'Jordan Brand, filiale de Nike, spécialisée dans les sneakers Air Jordan.',
                'logo' => '/storage/brands/jordan.jpg',
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Puma',
                'slug' => 'puma',
                'description' => 'Puma est une marque allemande réputée pour ses sneakers lifestyle et sport.',
                'logo' => '/storage/brands/puma.jpg',
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'New Balance',
                'slug' => 'new-balance',
                'description' => 'New Balance est une marque américaine connue pour ses sneakers confortables et rétro.',
                'logo' => '/storage/brands/new-balance.png',
                'is_featured' => false,
                'status' => 'active',
            ],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
