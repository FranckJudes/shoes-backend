<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Events\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get user orders",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user orders",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Si l'utilisateur est admin, retourner toutes les commandes
            if ($user->isAdmin()) {
                $orders = Order::with('items.product')->get();
            } else {
                // Sinon, retourner uniquement les commandes de l'utilisateur
                $orders = Order::with('items.product')->where('user_id', $user->id)->get();
            }

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'shipping_address' => 'required|string',
                'payment_method' => 'required|string|in:mtn,orange,paypal,stripe',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            $items = $request->items;
            $total = 0;

            // Vérifier le stock et calculer le total
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Not enough stock for product: {$product->name}",
                        'available' => $product->stock
                    ], 422);
                }

                $total += $product->price * $item['quantity'];
            }

            // Créer la commande
            $order = new Order();
            $order->user_id = $user->id;
            $order->total = $total;
            $order->status = 'pending';
            $order->shipping_address = $request->shipping_address;
            $order->payment_method = $request->payment_method;
            $order->save();

            // Ajouter les articles de la commande et mettre à jour le stock
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);

                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $product->price;
                $orderItem->save();

                // Mettre à jour le stock
                $product->stock -= $item['quantity'];
                $product->save();
            }

            // Déclencher l'événement de commande placée
            event(new OrderPlaced($order));

            return response()->json($order->load('items.product'), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get order details",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $order = Order::with('items.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Vérifier si l'utilisateur est autorisé à voir cette commande
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Cancel an order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot cancel this order"
     *     )
     * )
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $order = Order::with('items')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Vérifier si l'utilisateur est autorisé à annuler cette commande
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Vérifier si la commande peut être annulée (uniquement si elle est en attente)
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Cannot cancel this order. Status: ' . $order->status], 422);
        }

        // Restaurer le stock
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            $product->stock += $item->quantity;
            $product->save();
        }

        // Mettre à jour le statut de la commande
        $order->status = 'cancelled';
        $order->save();

        return response()->json(['message' => 'Order cancelled successfully']);
    }
}
