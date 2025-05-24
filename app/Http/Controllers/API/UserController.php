<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SavedItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/OrderHistory",
     *     summary="Get user's order history",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's order history",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function orderHistory()
    {
        try {
            $user = Auth::user();
            $orders = $user->orders()->with(['items.product', 'payment'])->orderBy('created_at', 'desc')->get();

            return response()->json(['data' => $orders]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve order history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/SavedItems",
     *     summary="Get user's saved items",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's saved items",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function savedItems()
    {
        try {
            $user = Auth::user();
            $savedItems = $user->savedItems()->with('product')->get();
            $products = $savedItems->map(function ($item) {
                return $item->product;
            });

            return response()->json(['data' => $products]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve saved items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/SavedItems",
     *     summary="Save an item to user's favorites",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item saved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function saveItem(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $user = Auth::user();
            $savedItem = SavedItem::firstOrCreate([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
            ]);

            return response()->json(['message' => 'Item saved successfully'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/SavedItems/{product_id}",
     *     summary="Remove an item from user's favorites",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item removed from favorites")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found in favorites"
     *     )
     * )
     */
    public function removeSavedItem($productId)
    {
        try {
            $user = Auth::user();
            $savedItem = SavedItem::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if (!$savedItem) {
                return response()->json(['message' => 'Item not found in favorites'], 404);
            }

            $savedItem->delete();

            return response()->json(['message' => 'Item removed from favorites']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/PaymentMethods",
     *     summary="Get user's payment methods",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's payment methods",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="payment_method", type="string", example="mtn"),
     *                 @OA\Property(property="payment_details", type="string", example="+237 6XX XXX XXX"),
     *                 @OA\Property(property="is_default", type="boolean", example=true)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function paymentMethods()
    {
        try {
            $user = Auth::user();
            $payments = Payment::where('user_id', $user->id)
                ->select('id', 'payment_method', 'payment_details', 'is_default')
                ->orderBy('is_default', 'desc')
                ->get();

            return response()->json(['data' => $payments]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment methods',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/AdressBook",
     *     summary="Get user's address book",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's address information",
     *         @OA\JsonContent(
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="City"),
     *             @OA\Property(property="country", type="string", example="Country"),
     *             @OA\Property(property="postal_code", type="string", example="12345")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function addressBook()
    {
        try {
            $user = Auth::user();
            $address = [
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
                'postal_code' => $user->postal_code,
            ];

            return response()->json($address);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve address information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/AdressBook",
     *     summary="Update user's address book",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="City"),
     *             @OA\Property(property="country", type="string", example="Country"),
     *             @OA\Property(property="postal_code", type="string", example="12345")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Address updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateAddressBook(Request $request)
    {
        try {
            $request->validate([
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'postal_code' => 'required|string|max:20',
            ]);

            $user = Auth::user();
            $user->update([
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
            ]);

            return response()->json(['message' => 'Address updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
