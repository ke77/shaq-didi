<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);


// protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/logout',  [AuthController::class, 'logout']);

    Route::get('/restaurants', [RestaurantController::class, 'index']);
    Route::get('/restaurants/{restaurant}/products', [RestaurantController::class, 'products']);
    Route::get('/products', [ProductController::class, 'index']);


    // order management routes
    Route::post('/place-order', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/order/today', [OrderController::class, 'today']);
    Route::patch('/order/{order}/complete', [OrderController::class, 'complete']);

});