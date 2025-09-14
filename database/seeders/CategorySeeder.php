<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;'); // Désactive les contraintes
        Category::query()->delete();                   // Supprime toutes les données
        DB::statement('PRAGMA foreign_keys = ON;');  // Réactive les contraintes

        $categories = [
            [
                'name' => 'Sneakers Homme',
                'description' => 'Collection de sneakers pour homme, allant des classiques aux nouveautés.',
            ],
            [
                'name' => 'Sneakers Femme',
                'description' => 'Sneakers tendance et élégantes spécialement conçues pour femme.',
            ],
            [
                'name' => 'Éditions Limitées',
                'description' => 'Modèles rares, collaborations exclusives et sneakers collectors.',
            ],
            [
                'name' => 'Sneakers Enfant',
                'description' => 'Sneakers confortables et stylées pour les plus jeunes.',
            ],
            [
                'name' => 'Accessoires',
                'description' => 'Chaussettes, lacets, produits d’entretien et autres accessoires.',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
