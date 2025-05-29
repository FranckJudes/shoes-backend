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
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:255',
                'is_default' => 'nullable|boolean',
            ]);

            $user = Auth::user();
            $user->update([
                'address' => $request->address_line1 . ($request->address_line2 ? ", {$request->address_line2}" : ''),
                'city' => $request->city,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
            ]);

            return response()->json([
                'message' => 'Address updated successfully',
                'data' => [
                    'id' => $user->id,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'postal_code' => $user->postal_code,
                    'is_default' => true
                ]
            ]);
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

    /**
     * @OA\Post(
     *     path="/AdressBook",
     *     summary="Add a new address to user's address book",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="address_line1", type="string", example="123 Main St"),
     *             @OA\Property(property="address_line2", type="string", example="Apt 4B"),
     *             @OA\Property(property="city", type="string", example="City"),
     *             @OA\Property(property="state", type="string", example="State"),
     *             @OA\Property(property="postal_code", type="string", example="12345"),
     *             @OA\Property(property="country", type="string", example="Country"),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Address added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Address added successfully")
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
    public function addAddressBook(Request $request)
    {
        try {
            $request->validate([
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:255',
                'is_default' => 'nullable|boolean',
            ]);

            $user = Auth::user();
            $user->update([
                'address' => $request->address_line1 . ($request->address_line2 ? ", {$request->address_line2}" : ''),
                'city' => $request->city,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
            ]);

            return response()->json([
                'message' => 'Address added successfully',
                'data' => [
                    'id' => $user->id,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'postal_code' => $user->postal_code,
                    'is_default' => true
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/AdressBook/{id}",
     *     summary="Remove an address from user's address book",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Address ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Address removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Address not found"
     *     )
     * )
     */
    public function removeAddressBook($id)
    {
        try {
            $user = Auth::user();
            
            // Dans une application réelle, vous auriez une table d'adresses
            // Pour l'instant, nous allons simplement effacer l'adresse de l'utilisateur
            if ($user->id == $id) {
                $user->update([
                    'address' => null,
                    'city' => null,
                    'country' => null,
                    'postal_code' => null,
                ]);
                
                return response()->json(['message' => 'Address removed successfully']);
            }
            
            return response()->json(['message' => 'Address not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/AdressBook/{id}/default",
     *     summary="Set an address as default",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Address ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address set as default successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Address set as default successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Address not found"
     *     )
     * )
     */
    public function setDefaultAddressBook($id)
    {
        try {
            $user = Auth::user();
            
            // Dans une application réelle, vous auriez une table d'adresses
            // Pour l'instant, nous allons simplement retourner un succès
            if ($user->id == $id) {
                return response()->json(['message' => 'Address set as default successfully']);
            }
            
            return response()->json(['message' => 'Address not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to set address as default',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/PaymentMethods",
     *     summary="Add a new payment method",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="card_name", type="string", example="John Doe"),
     *             @OA\Property(property="card_number", type="string", example="4242424242424242"),
     *             @OA\Property(property="expiry", type="string", example="12/25"),
     *             @OA\Property(property="cvv", type="string", example="123"),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment method added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment method added successfully")
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
    public function addPaymentMethod(Request $request)
    {
        try {
            $request->validate([
                'card_name' => 'required|string|max:255',
                'card_number' => 'required|string|min:16|max:19',
                'expiry' => 'required|string|max:7',
                'cvv' => 'required|string|min:3|max:4',
                'is_default' => 'nullable|boolean',
            ]);

            $user = Auth::user();
            
            // Si c'est la méthode par défaut, mettre toutes les autres à false
            if ($request->is_default) {
                Payment::where('user_id', $user->id)->update(['is_default' => false]);
            }
            
            // Déterminer le type de carte (visa, mastercard, etc.) basé sur le numéro
            $cardType = 'visa'; // Par défaut
            $firstDigit = substr($request->card_number, 0, 1);
            if ($firstDigit == '4') {
                $cardType = 'visa';
            } elseif ($firstDigit == '5') {
                $cardType = 'mastercard';
            }
            
            // Masquer le numéro de carte sauf les 4 derniers chiffres
            $last4 = substr($request->card_number, -4);
            
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_method' => 'card',
                'payment_details' => json_encode([
                    'card_name' => $request->card_name,
                    'card_number' => '************' . $last4,
                    'expiry' => $request->expiry,
                ]),
                'is_default' => $request->is_default ?? false,
            ]);

            return response()->json([
                'message' => 'Payment method added successfully',
                'data' => [
                    'id' => $payment->id,
                    'card_type' => $cardType,
                    'last4' => $last4,
                    'expiry' => $request->expiry,
                    'is_default' => $payment->is_default,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/PaymentMethods/{id}",
     *     summary="Remove a payment method",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment Method ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment method removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment method removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment method not found"
     *     )
     * )
     */
    public function removePaymentMethod($id)
    {
        try {
            $user = Auth::user();
            $payment = Payment::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$payment) {
                return response()->json(['message' => 'Payment method not found'], 404);
            }

            $payment->delete();

            return response()->json(['message' => 'Payment method removed successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/PaymentMethods/{id}/default",
     *     summary="Set a payment method as default",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment Method ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment method set as default successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment method set as default successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment method not found"
     *     )
     * )
     */
    public function setDefaultPaymentMethod($id)
    {
        try {
            $user = Auth::user();
            $payment = Payment::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$payment) {
                return response()->json(['message' => 'Payment method not found'], 404);
            }

            // Mettre toutes les méthodes de paiement à non-défaut
            Payment::where('user_id', $user->id)->update(['is_default' => false]);
            
            // Définir celle-ci comme défaut
            $payment->update(['is_default' => true]);

            return response()->json(['message' => 'Payment method set as default successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to set payment method as default',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
