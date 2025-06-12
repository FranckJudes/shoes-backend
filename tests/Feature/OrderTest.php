<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_create_order(): void
    {
        // Créer un utilisateur client
        $client = User::factory()->create();

        // Créer une catégorie
        $category = Category::factory()->create();

        // Créer un produit
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 99.99,
        ]);

        // Données de la commande à créer
        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
            'shipping_address' => '123 Test Street, Test City',
            'payment_method' => 'mtn',
        ];

        // Faire une requête pour créer une commande
        $response = $this->actingAs($client)
            ->postJson('/api/orders', $orderData);

        // Vérifier que la réponse est correcte
        $response->assertStatus(201)
            ->assertJson([
                'total' => 199.98, // 2 * 99.99
                'status' => 'pending',
                'shipping_address' => '123 Test Street, Test City',
                'payment_method' => 'mtn',
            ]);

        // Vérifier que la commande a été créée dans la base de données
        $this->assertDatabaseHas('orders', [
            'user_id' => $client->id,
            'total' => 199.98,
            'status' => 'pending',
        ]);

        // Vérifier que le stock du produit a été mis à jour
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8, // 10 - 2
        ]);
    }

    public function test_client_cannot_order_out_of_stock_product(): void
    {
        // Créer un utilisateur client
        $client = User::factory()->create();

        // Créer une catégorie
        $category = Category::factory()->create();

        // Créer un produit avec un stock insuffisant
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 1,
            'price' => 99.99,
        ]);

        // Données de la commande à créer
        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2, // Plus que le stock disponible
                ],
            ],
            'shipping_address' => '123 Test Street, Test City',
            'payment_method' => 'mtn',
        ];

        // Faire une requête pour créer une commande
        $response = $this->actingAs($client)
            ->postJson('/api/orders', $orderData);

        // Vérifier que la réponse est un refus
        $response->assertStatus(422);

        // Vérifier que la commande n'a pas été créée dans la base de données
        $this->assertDatabaseMissing('orders', [
            'user_id' => $client->id,
            'status' => 'pending',
        ]);

        // Vérifier que le stock du produit n'a pas été modifié
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }
}