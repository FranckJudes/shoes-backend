<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MassDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(100)->create();

        Category::factory(100)->create();

        Product::factory(100)->create();

        $paymentMethods = ['mtn', 'orange', 'paypal', 'stripe'];
        $orderStatuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

        $users = User::all();
        $products = Product::all();

        for ($i = 0; $i < 100; $i++) {
            $user = $users->random();
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
                'status' => $orderStatuses[array_rand($orderStatuses)],
                'shipping_address' => fake()->address(),
                'payment_method' => $paymentMethod,
            ]);

            // Ajouter 1 à 5 produits à la commande
            $orderTotal = 0;
            $numItems = rand(1, 5);

            for ($j = 0; $j < $numItems; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $subtotal = $product->price * $quantity;
                $orderTotal += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }

            $order->update(['total' => $orderTotal]);

            if ($order->status === 'paid' || $order->status === 'shipped' || $order->status === 'delivered') {
                $transactionId = $paymentMethod . '_txn_' . Str::random(16);

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $orderTotal,
                    'transaction_id' => $transactionId,
                    'status' => 'completed',
                ]);
            }
        }
    }
}
