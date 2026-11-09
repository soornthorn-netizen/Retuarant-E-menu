<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ImageItemController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ColorStructureController;
use App\Http\Controllers\RoadMapController;

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
