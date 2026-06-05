<?php

use App\Http\Controllers\Api\FrontendPublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public frontend JSON API (/api/public/*)
|--------------------------------------------------------------------------
|
| Consumed by the Next.js site (bgs-front-end). No authentication required.
|
*/
Route::prefix('api/public')->middleware('throttle:120,1')->group(function () {
    Route::get('home', [FrontendPublicController::class, 'home']);
    Route::get('site-footer', [FrontendPublicController::class, 'siteFooter']);
    Route::get('about', [FrontendPublicController::class, 'about']);
    Route::get('services', [FrontendPublicController::class, 'services']);
    Route::get('services/{slug}', [FrontendPublicController::class, 'serviceShow']);
    Route::get('doctors', [FrontendPublicController::class, 'doctors']);
    Route::get('faqs', [FrontendPublicController::class, 'faqs']);
    Route::get('testimonials', [FrontendPublicController::class, 'testimonials']);
    Route::get('blogs', [FrontendPublicController::class, 'blogs']);
    Route::get('blogs/{slug}', [FrontendPublicController::class, 'blogShow']);
    Route::get('products', [FrontendPublicController::class, 'products']);
    Route::get('packages', [FrontendPublicController::class, 'packages']);
    Route::get('membership-plans', [FrontendPublicController::class, 'membershipPlans']);
    Route::get('promotions', [FrontendPublicController::class, 'promotions']);
    Route::get('page-headers/{key}', [FrontendPublicController::class, 'pageHeader']);
    Route::post('contact-inquiry', [FrontendPublicController::class, 'contactInquiry']);
});
