<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\MasterAPIController;
use App\Http\Controllers\API\RajaongkirController;
use App\Http\Controllers\API\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    // with google
    Route::get('google', [AuthController::class, 'redirectToGoogle']);
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);
});

Route::middleware('auth:sanctum')->prefix('/cart')->group(function () {
    Route::post('add', [CartController::class, 'addToCart']);
    Route::get('list', [CartController::class, 'getCart']);
    Route::post('increment', [CartController::class, 'increment']);
    Route::post('decrement', [CartController::class, 'decrement']);
    Route::delete('remove/{id}', [CartController::class, 'remove']);
});

// voucher apply
Route::post('/apply-voucher', [MasterAPIController::class, 'applyVoucher'])->middleware('auth:sanctum');

// Category
Route::get('/categories', [MasterAPIController::class, 'categories']);
// Products
Route::get('/products', [MasterAPIController::class, 'products']);
Route::get('/product/{slug}', [MasterAPIController::class, 'detailProduct']);
// Variants
Route::get('/variants', [MasterAPIController::class, 'variants']);

// Articles
Route::get('/articles', [MasterAPIController::class, 'articles']);
Route::get('/article/{slug}', [MasterAPIController::class, 'detailArticle']);

// Arsip Tutorials
Route::get('/tutorials', [MasterAPIController::class, 'tutorials']);

Route::get('/benefit/resellers', [MasterAPIController::class, 'benefitResellers']);
Route::get('/benefit/affiliates', [MasterAPIController::class, 'benefitAffiliates']);

// Promotions
Route::get('/promotions', [MasterAPIController::class, 'promotions']);

// Reseller Register
Route::post('/reseller/register', [MasterAPIController::class, 'createReseller']);
// Affiliate Register
Route::post('/affiliate/register', [MasterAPIController::class, 'createAffiliate']);


// Rajaongkir
Route::get('provinces', [RajaongkirController::class, 'provinces']);
Route::get('cities/{id}', [RajaongkirController::class, 'cities']);
Route::get('districts/{id}', [RajaongkirController::class, 'districts']);
Route::get('subdistricts/{id}', [RajaongkirController::class, 'subdistricts']);
Route::get('cost/{destination}/{weight}/{courier}', [RajaongkirController::class, 'calculateCost']);

// Transaction
Route::post('/transaction', [TransactionController::class, 'createTransaction']);
