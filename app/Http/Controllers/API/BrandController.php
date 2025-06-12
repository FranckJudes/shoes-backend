<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Brands",
 *     description="API Endpoints pour la gestion des marques"
 * )
 */

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     *
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/api/brands",
     *     summary="Récupérer toutes les marques",
     *     tags={"Brands"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des marques récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Brand"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $brands = Brand::all();
        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * Display featured brands.
     *
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/api/brands/featured",
     *     summary="Récupérer les marques mises en avant",
     *     tags={"Brands"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des marques mises en avant récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Brand"))
     *         )
     *     )
     * )
     */
    public function featured()
    {
        $brands = Brand::where('is_featured', true)
                       ->where('status', 'active')
                       ->get();
        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * Store a newly created brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Post(
     *     path="/api/brands",
     *     summary="Créer une nouvelle marque",
     *     tags={"Brands"},
     *     security={"sanctum": {}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Nike"),
     *             @OA\Property(property="description", type="string", example="Nike, Inc. est une entreprise américaine"),
     *             @OA\Property(property="logo", type="string", format="binary"),
     *             @OA\Property(property="is_featured", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="active", enum={"active", "inactive"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Marque créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Brand")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $brandData = $request->all();
        
        // Generate slug from name
        $brandData['slug'] = Str::slug($request->name);
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
            $brandData['logo'] = $path;
        }
        
        // Set default values
        $brandData['is_featured'] = $request->is_featured ?? false;
        $brandData['status'] = $request->status ?? 'active';

        $brand = Brand::create($brandData);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand
        ], 201);
    }

    /**
     * Display the specified brand.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/api/brands/{brand}",
     *     summary="Récupérer les détails d'une marque",
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="ID de la marque",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la marque récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Brand")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Marque non trouvée"
     *     )
     * )
     */
    public function show(Brand $brand)
    {
        return response()->json([
            'success' => true,
            'data' => $brand
        ]);
    }

    /**
     * Update the specified brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     * 
     * @OA\Put(
     *     path="/api/brands/{brand}",
     *     summary="Mettre à jour une marque",
     *     tags={"Brands"},
     *     security={"sanctum": {}},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="ID de la marque",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Nike"),
     *             @OA\Property(property="description", type="string", example="Nike, Inc. est une entreprise américaine"),
     *             @OA\Property(property="logo", type="string", format="binary"),
     *             @OA\Property(property="is_featured", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="active", enum={"active", "inactive"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Marque mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Brand")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Marque non trouvée"
     *     )
     * )
     */
    public function update(Request $request, Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $brandData = $request->all();
        
        // Update slug if name is changed
        if ($request->has('name')) {
            $brandData['slug'] = Str::slug($request->name);
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            
            $path = $request->file('logo')->store('brands', 'public');
            $brandData['logo'] = $path;
        }

        $brand->update($brandData);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand
        ]);
    }

    /**
     * Remove the specified brand from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     * 
     * @OA\Delete(
     *     path="/api/brands/{brand}",
     *     summary="Supprimer une marque",
     *     tags={"Brands"},
     *     security={"sanctum": {}},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="ID de la marque",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Marque supprimée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Impossible de supprimer la marque",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete brand with associated products. Please remove or reassign the products first.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Marque non trouvée"
     *     )
     * )
     */
    public function destroy(Brand $brand)
    {
        // Check if brand has associated products
        $productCount = Product::where('brand_id', $brand->id)->count();
        
        if ($productCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete brand with associated products. Please remove or reassign the products first.'
            ], 422);
        }
        
        // Delete logo if exists
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        
        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully'
        ]);
    }

    /**
     * Get products for a specific brand.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/api/brands/{brand}/products",
     *     summary="Récupérer les produits d'une marque",
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="ID de la marque",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produits de la marque récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/brands/1/products?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/brands/1/products?page=1"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="path", type="string", example="http://localhost:8000/api/brands/1/products"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Marque non trouvée"
     *     )
     * )
     */
    public function products(Brand $brand)
    {
        $products = $brand->products()->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
