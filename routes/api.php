<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AddressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/featured', [ProductController::class, 'featured']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('categories/{category}/products', [CategoryController::class, 'products']);

// Routes pour les marques (brands)
Route::get('brands', [BrandController::class, 'index']);
Route::get('brands/featured', [BrandController::class, 'featured']);
Route::get('brands/{brand}', [BrandController::class, 'show']);
Route::get('brands/{brand}/products', [BrandController::class, 'products']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('user', [AuthController::class, 'user']);
    Route::put('user', [AuthController::class, 'update']);
    Route::put('profile', [AuthController::class, 'updateProfile']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    
    // User profile related routes
    Route::get('OrderHistory', [UserController::class, 'orderHistory']);
    Route::get('SavedItems', [UserController::class, 'savedItems']);
    Route::post('SavedItems', [UserController::class, 'saveItem']);
    Route::delete('SavedItems/{product_id}', [UserController::class, 'removeSavedItem']);
    
    // Routes pour les méthodes de paiement
    Route::get('PaymentMethods', [UserController::class, 'paymentMethods']);
    Route::post('PaymentMethods', [UserController::class, 'addPaymentMethod']);
    Route::delete('PaymentMethods/{id}', [UserController::class, 'removePaymentMethod']);
    Route::put('PaymentMethods/{id}/default', [UserController::class, 'setDefaultPaymentMethod']);
    
    // Routes pour le carnet d'adresses
    Route::get('AdressBook', [AddressController::class, 'getAddressBook']);
    Route::post('AdressBook', [AddressController::class, 'addAddressBook']);
    Route::put('AdressBook', [AddressController::class, 'updateAddressBook']);
    Route::delete('AdressBook/{id}', [AddressController::class, 'removeAddressBook']);
    Route::put('AdressBook/{id}/default', [AddressController::class, 'setDefaultAddressBook']);

    Route::post('payments', [PaymentController::class, 'store']);
    Route::get('payments/{payment}', [PaymentController::class, 'show']);
    Route::get('payments/user/{user_id}', [PaymentController::class, 'getUserPaymentHistory']);

    Route::middleware('admin')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);

        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        // Routes admin pour les marques (brands)
        Route::post('brands', [BrandController::class, 'store']);
        Route::put('brands/{brand}', [BrandController::class, 'update']);
        Route::delete('brands/{brand}', [BrandController::class, 'destroy']);

        Route::get('admin/orders', [OrderController::class, 'adminIndex']);
        Route::put('admin/orders/{order}', [OrderController::class, 'adminUpdate']);

        Route::get('admin/payments', [PaymentController::class, 'adminIndex']);
        Route::put('admin/payments/{payment}', [PaymentController::class, 'adminUpdate']);
    });
});
