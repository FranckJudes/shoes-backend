<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SavedItem;
use Illuminate\Http\Request;

class SavedItemController extends Controller
{
    /**
     * Liste des articles sauvegardés de l'utilisateur connecté
     */
    public function index()
    {
        $savedItems = SavedItem::with('product.brand')
            ->where('user_id', auth()->id())
            ->get();
    
        return response()->json([
            'data' => $savedItems
        ]);
    }
    
    /**
     * Ajouter un article aux favoris
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $savedItem = SavedItem::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'message' => 'Article ajouté aux favoris',
            'data' => $savedItem->load('product.brand'),
        ], 201);
    }

    /**
     * Retirer un article des favoris
     */
    public function destroy($productId)
    {
        $deleted = SavedItem::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Article retiré des favoris']);
        }

        return response()->json(['message' => 'Article non trouvé'], 404);
    }
}
