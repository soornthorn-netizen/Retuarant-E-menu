<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryLanguageController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\UnitTypeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductLanguageController;
use App\Http\Controllers\ProductVariationController;
use App\Http\Controllers\ProductVariationLanguageController;
use App\Http\Controllers\ProductOptionController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\BusinessSocialLinkController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\TableSessionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ColorStructureController;
use App\Http\Controllers\RoadMapController;
use App\Http\Controllers\LuckyWheelController;
use App\Http\Controllers\LuckyWheelPrizeController;
use App\Http\Controllers\LuckyWheelSpinController;

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
    return $request->user()->load(['role.permissions', 'linkedSocialAccounts']);
});

// RBAC & User Management
Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions']);
Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);
Route::apiResource('role-permissions', RolePermissionController::class);
Route::apiResource('users', UserController::class);

// Color Structure / Theme API Endpoint
Route::get('color-structure', [ColorStructureController::class, 'current']);
Route::apiResource('color-structures', ColorStructureController::class);

// 07. Food Lucky Wheel (Spin to win Coupon or Free Drink)
Route::prefix('lucky-wheel')->group(function () {
    Route::get('prizes', [LuckyWheelController::class, 'prizes']);
    Route::post('spin', [LuckyWheelController::class, 'spin']);
    Route::get('history', [LuckyWheelController::class, 'history']);
    Route::match(['get', 'post'], 'redeem', [LuckyWheelController::class, 'redeem']);
    Route::get('verify/{code}', [LuckyWheelController::class, 'verifyCode']);
});
Route::apiResource('lucky-wheel-prizes', LuckyWheelPrizeController::class);
Route::apiResource('lucky-wheel-spins', LuckyWheelSpinController::class);

// Multilingual & Socials
Route::apiResource('languages', LanguageController::class);
Route::apiResource('socials', SocialController::class);
Route::apiResource('business-social-links', BusinessSocialLinkController::class);

// Categories & Category Languages
Route::apiResource('categories', CategoryController::class);
Route::apiResource('category-languages', CategoryLanguageController::class);

// Unit Types
Route::apiResource('unit-types', UnitTypeController::class);

// Products, Options, Images, Variations & Languages
Route::apiResource('products', ProductController::class);
Route::apiResource('product-images', ProductImageController::class);
Route::apiResource('product-languages', ProductLanguageController::class);
Route::apiResource('product-variations', ProductVariationController::class);
Route::apiResource('product-variation-languages', ProductVariationLanguageController::class);
Route::apiResource('product-options', ProductOptionController::class);

// Restaurant Management System CRUD API Routes
Route::apiResource('restaurants', RestaurantController::class);
Route::apiResource('branches', BranchController::class);
Route::apiResource('menus', MenuController::class);
Route::apiResource('menu-items', MenuItemController::class);
Route::apiResource('reservations', ReservationController::class);

// Dining Tables & Table Sessions (QR scan & Dine-in sessions)
Route::get('dining-tables/{dining_table}/active-session', [DiningTableController::class, 'activeSession']);
Route::post('dining-tables/{dining_table}/open-session', [DiningTableController::class, 'openSession']);
Route::apiResource('dining-tables', DiningTableController::class);

Route::post('table-sessions/{table_session}/close', [TableSessionController::class, 'closeSession']);
Route::get('table-sessions/{table_session}/orders', [TableSessionController::class, 'orders']);
Route::apiResource('table-sessions', TableSessionController::class);

Route::apiResource('orders', OrderController::class);
Route::apiResource('order-items', OrderItemController::class);
Route::apiResource('payments', PaymentController::class);
Route::apiResource('receipts', ReceiptController::class);
Route::apiResource('promotions', PromotionController::class);
Route::apiResource('reviews', ReviewController::class);
Route::apiResource('road-maps', RoadMapController::class);
Route::apiResource('profiles', ProfileController::class);
