<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryFaqController;
use App\Http\Controllers\CategoryTutorialController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\FlavourController;
use App\Http\Controllers\FooterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\VideoPlayerController;
use App\Http\Controllers\VoucherController;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
Route::resource('video-players', VideoPlayerController::class);
Route::resource('sliders', SliderController::class);
Route::patch('sliders/{slider}/toggle-status', [SliderController::class, 'toggleStatus'])->name('sliders.toggleStatus');
Route::resource('abouts', AboutController::class);
Route::resource('footers', FooterController::class);
Route::resource('categories', CategoryController::class);
Route::resource('flavours', FlavourController::class);
Route::resource('sizes', SizeController::class);
Route::resource('brands', BrandController::class);
Route::resource('products', ProductController::class);
Route::patch('variants/{variant}/toggle-status', [ProductController::class, 'toggleStatus'])->name('variants.toggleStatus');
Route::resource('flash-sales', FlashSaleController::class);
Route::get('/variants/{variant}/sizes', [FlashSaleController::class, 'getSizes']);


Route::resource('benefits', BenefitController::class);
Route::patch('benefits/{benefit}/toggle-status', [BenefitController::class, 'toggleStatus'])->name('benefits.toggleStatus');

Route::get('partners', [PartnerController::class, 'index'])->name('partners.index');

Route::resource('promotions', PromotionController::class);

Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('orders/pickup', [OrderController::class, 'pickup'])->name('orders.pickup');
Route::post('orders/pickup', [OrderController::class, 'pickupStore'])->name('orders.pickup.store');
Route::post('orders/request/{order}', [OrderController::class, 'orderRequest'])->name('orders.request-order');

Route::resource('vouchers', VoucherController::class);

Route::resource('category-articles', CategoryArticleController::class);
Route::resource('articles', ArticleController::class);
Route::patch('articles/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('articles.toggleStatus');

Route::resource('category-tutorials', CategoryTutorialController::class);
Route::resource('tutorials', TutorialController::class);
Route::patch('tutorials/{tutorial}/toggle-status', [TutorialController::class, 'toggleStatus'])->name('tutorials.toggleStatus');

Route::resource('testimonials', TestimonialController::class);
Route::patch('testimonials/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('testimonials.toggleStatus');

Route::resource('category-faqs', CategoryFaqController::class);
Route::resource('faqs', FaqController::class);
Route::patch('faqs/{faq}/toggle-status', [FaqController::class, 'toggleStatus'])->name('faqs.toggleStatus');

require __DIR__ . '/auth.php';
