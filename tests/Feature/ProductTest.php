<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_products(): void
    {
        // Créer quelques produits
        Product::factory(3)->create();

        // Faire une requête pour obtenir tous les produits
        $response = $this->getJson('/api/products');

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_get_single_product(): void
    {
        // Créer un produit
        $product = Product::factory()->create();

        // Faire une requête pour obtenir le produit
        $response = $this->getJson('/api/products/' . $product->id);

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
            ]);
    }

    public function test_admin_can_create_product(): void
    {
        // Créer un utilisateur admin
        $admin = User::factory()->admin()->create();

        // Données du produit à créer
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => 1,
        ];

        // Faire une requête pour créer un produit
        $response = $this->actingAs($admin)
            ->postJson('/api/products', $productData);

        // Vérifier que la réponse est correcte
        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Test Product',
                'description' => 'This is a test product',
                'price' => 99.99,
                'stock' => 10,
                'category_id' => 1,
            ]);

        // Vérifier que le produit a été créé dans la base de données
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
        ]);
    }

    public function test_client_cannot_create_product(): void
    {
        // Créer un utilisateur client
        $client = User::factory()->create();

        // Données du produit à créer
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => 1,
        ];

        // Faire une requête pour créer un produit
        $response = $this->actingAs($client)
            ->postJson('/api/products', $productData);

        // Vérifier que la réponse est un refus
        $response->assertStatus(403);

        // Vérifier que le produit n'a pas été créé dans la base de données
        $this->assertDatabaseMissing('products', [
            'name' => 'Test Product',
        ]);
    }
}