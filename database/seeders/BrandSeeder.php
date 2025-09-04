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
                'id' => 2,
            'name' => 'Adidas',
            'slug' => 'Adidas',
            'description' => 'Adidas, Inc. is an American multinational corporation',
            'logo' => 'brands/nike-logo.jpg',
            'is_featured' => true,
            'status' => 'active'
            ]
           
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
       

    }
}
