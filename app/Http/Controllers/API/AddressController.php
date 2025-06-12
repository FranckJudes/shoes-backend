<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    /**
     * Get user's address book
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddressBook()
    {
        try {
            $user = Auth::user();
            
            // Dans une application réelle, vous auriez une table d'adresses
            // Pour l'instant, nous retournons l'adresse de l'utilisateur comme une seule adresse
            $address = null;
            
            if ($user->address) {
                $address = [
                    'id' => $user->id,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'postal_code' => $user->postal_code,
                    'is_default' => true
                ];
            }
            
            return response()->json(['data' => $address ? [$address] : []]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve address information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Update user's address book
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
            
            // S'assurer que toutes les valeurs sont correctement traitées comme des chaînes
            $address = $request->address_line1;
            if ($request->address_line2) {
                $address .= ", {$request->address_line2}";
            }
            
            $user->address = $address;
            $user->city = $request->city;
            $user->country = $request->country;
            $user->postal_code = $request->postal_code;
            $user->save();

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
        } catch (ValidationException $e) {
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
     * Add a new address to user's address book
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
            
            // S'assurer que toutes les valeurs sont correctement traitées comme des chaînes
            $address = $request->address_line1;
            if ($request->address_line2) {
                $address .= ", {$request->address_line2}";
            }
            
            $user->address = $address;
            $user->city = $request->city;
            $user->country = $request->country;
            $user->postal_code = $request->postal_code;
            $user->save();

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
        } catch (ValidationException $e) {
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
     * Remove an address from user's address book
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeAddressBook($id)
    {
        try {
            $user = Auth::user();
            
            // Dans une application réelle, vous auriez une table d'adresses
            // Pour l'instant, nous allons simplement effacer l'adresse de l'utilisateur
            if ($user->id == $id) {
                $user->address = null;
                $user->city = null;
                $user->country = null;
                $user->postal_code = null;
                $user->save();
                
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
     * Set an address as default
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
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
}
