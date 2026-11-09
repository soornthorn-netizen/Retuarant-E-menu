<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\ImageItemController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ColorStructureController;
use App\Http\Controllers\Api\RoadMapController;

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

// Authentication & Social Login
Route::post('auth/google/login', [GoogleLoginController::class, 'login']);
Route::post('auth/social/login', [GoogleLoginController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('linkedSocialAccounts');
});

// Color Structure / Theme API Endpoint
Route::get('color-structure', [ColorStructureController::class, 'current']);
Route::apiResource('color-structures', ColorStructureController::class);

// Restaurant Management System CRUD API Routes
Route::apiResource('restaurants', RestaurantController::class);
Route::apiResource('branches', BranchController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('menus', MenuController::class);
Route::apiResource('menu-items', MenuItemController::class);
Route::apiResource('image-items', ImageItemController::class);
Route::apiResource('reservations', ReservationController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('order-items', OrderItemController::class);
Route::apiResource('payments', PaymentController::class);
Route::apiResource('receipts', ReceiptController::class);
Route::apiResource('promotions', PromotionController::class);
Route::apiResource('reviews', ReviewController::class);
Route::apiResource('road-maps', RoadMapController::class);
Route::apiResource('profiles', ProfileController::class);
