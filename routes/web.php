<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\CategoryArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryFaqController;
use App\Http\Controllers\CategoryTutorialController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\FlavourController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
Route::resource('categories', CategoryController::class);
Route::resource('flavours', FlavourController::class);
Route::resource('sizes', SizeController::class);
Route::resource('products', ProductController::class);
Route::patch('variants/{variant}/toggle-status', [ProductController::class, 'toggleStatus'])->name('variants.toggleStatus');
Route::resource('flash-sales', FlashSaleController::class);

Route::resource('benefits', BenefitController::class);
Route::patch('benefits/{benefit}/toggle-status', [BenefitController::class, 'toggleStatus'])->name('benefits.toggleStatus');

Route::get('partners', [PartnerController::class, 'index'])->name('partners.index');

Route::resource('promotions', PromotionController::class);

Route::resource('vouchers', VoucherController::class);

Route::resource('category-articles', CategoryArticleController::class);
Route::resource('articles', ArticleController::class);
Route::patch('articles/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('articles.toggleStatus');

Route::resource('category-tutorials', CategoryTutorialController::class);
Route::resource('tutorials', TutorialController::class);
Route::patch('tutorials/{tutorial}/toggle-status', [TutorialController::class, 'toggleStatus'])->name('tutorials.toggleStatus');

Route::resource('category-faqs', CategoryFaqController::class);
Route::resource('faqs', FaqController::class);
Route::patch('faqs/{faq}/toggle-status', [FaqController::class, 'toggleStatus'])->name('faqs.toggleStatus');

require __DIR__ . '/auth.php';
