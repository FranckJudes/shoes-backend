<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;'); // Désactive les contraintes
        Product::query()->delete();                   // Supprime toutes les données
        DB::statement('PRAGMA foreign_keys = ON;');  // Réactive les contraintes

        $products = [
            // Sneakers Homme
            [
                'name' => 'Nike Air Force 1 Low White',
                'description' => 'Classique indémodable, cuir blanc premium avec semelle confortable.',
                'price' => 120.00,
                'stock' => 40,
                'category_id' => 1,
                'featured' => true,
                'coming_soon' => false,
                'image' => '/storage/products/nike-air-force-1-low-white.jpg',
            ],
            [
                'name' => 'Adidas Yeezy Boost 350 V2 Zebra',
                'description' => 'Sneaker iconique avec design zébré noir et blanc.',
                'price' => 280.00,
                'stock' => 15,
                'category_id' => 1,
                'featured' => true,
                'coming_soon' => false,
                'image' => '/storage/products/adidas-yeezy-boost-350-v2-zebra.webp',
            ],

            // Sneakers Femme
            [
                'name' => 'Nike Dunk Low Pink Velvet',
                'description' => 'Sneaker féminine en daim rose avec détails velours.',
                'price' => 150.00,
                'stock' => 25,
                'category_id' => 2,
                'featured' => true,
                'coming_soon' => false,
                'image' => '/storage/products/nike-dunk-low-pink-velvet.webp',
            ],
            [
                'name' => 'Puma Cali Star White Gold',
                'description' => 'Sneaker lifestyle élégante avec détails dorés.',
                'price' => 110.00,
                'stock' => 30,
                'category_id' => 2,
                'featured' => false,
                'coming_soon' => true,
                'image' => '/storage/products/puma-cali-star-white-gold.webp',
            ],

            // Éditions Limitées
            [
                'name' => 'Jordan 1 Retro High University Blue',
                'description' => 'Édition limitée inspirée des couleurs UNC.',
                'price' => 350.00,
                'stock' => 10,
                'category_id' => 3,
                'featured' => true,
                'coming_soon' => false,
                'image' => '/storage/products/jordan-1-retro-high-university-blue.webp',
            ],
            [
                'name' => 'Nike SB Dunk Low Travis Scott',
                'description' => 'Collaboration spéciale avec Travis Scott.',
                'price' => 500.00,
                'stock' => 5,
                'category_id' => 3,
                'featured' => true,
                'coming_soon' => true,
                'image' => '/storage/products/nike-sb-dunk-low-travis-scott.webp',
            ],

            // Sneakers Enfant
            [
                'name' => 'Adidas Superstar Kids',
                'description' => 'Version mini du modèle culte Adidas Superstar.',
                'price' => 70.00,
                'stock' => 50,
                'category_id' => 4,
                'featured' => false,
                'coming_soon' => false,
                'image' => '/storage/products/adidas-superstar-kids.webp',
            ],
            [
                'name' => 'Nike Air Max 90 Junior',
                'description' => 'Confort maximal pour les plus jeunes.',
                'price' => 85.00,
                'stock' => 40,
                'category_id' => 4,
                'featured' => true,
                'coming_soon' => false,
                'image' => '/storage/products/nike-air-max-90-junior.webp',
            ],

            // Accessoires
            [
                'name' => 'Pack de chaussettes Nike Sportswear (3 paires)',
                'description' => 'Chaussettes confortables idéales pour sneakers.',
                'price' => 20.00,
                'stock' => 100,
                'category_id' => 5,
                'featured' => false,
                'coming_soon' => false,
                'image' => '/storage/products/chaussettes-nike-sportswear-3p.webp',
            ],
            [
                'name' => 'Lacets premium noirs (120cm)',
                'description' => 'Lacets résistants et adaptés aux sneakers.',
                'price' => 8.00,
                'stock' => 150,
                'category_id' => 5,
                'featured' => false,
                'coming_soon' => false,
                'image' => '/storage/products/lacets-premium-noirs-120cm.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
