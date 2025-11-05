<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteInformationController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\CustomerRegistrationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dashboard API Routes (Protected - Web Session Auth)
Route::middleware(['web', 'auth'])->prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'index'])
        ->name('api.dashboard.stats');
    
    Route::get('/overview', [DashboardController::class, 'overview'])
        ->name('api.dashboard.overview');
    
    Route::get('/recent-activity', [DashboardController::class, 'recentActivity'])
        ->name('api.dashboard.recent-activity');
    
    Route::get('/latest-backup', [DashboardController::class, 'latestBackup'])
        ->name('api.dashboard.latest-backup');
    
    Route::post('/clear-cache', [DashboardController::class, 'clearCache'])
        ->name('api.dashboard.clear-cache');
});

// Public API Routes with rate limiting (v1)
Route::prefix('v1')->group(function () {
    // Blog API Endpoints
    Route::get('/blogs', [BlogController::class, 'apiIndex'])
        ->name('api.blogs.index');
    
    Route::get('/blogs/latest', [BlogController::class, 'apiLatest'])
        ->name('api.blogs.latest');
    
    Route::get('/blogs/{id}', [BlogController::class, 'showApi'])
        ->where('id', '[0-9]+')
        ->name('api.blogs.show');
    
    Route::get('/blogs/{id}/related', [BlogController::class, 'relatedBlogs'])
        ->where('id', '[0-9]+')
        ->name('api.blogs.related');
    
    // Product API Endpoints
    Route::get('/products', [ProductController::class, 'apiIndex'])
        ->name('api.products.index');
    
    Route::get('/products/latest', [ProductController::class, 'apiLatest'])
        ->name('api.products.latest');
    
    // Site Information Contact API Endpoints (Public)
    Route::prefix('contacts')->group(function () {
        Route::get('/email', [SiteInformationController::class, 'fetchEmail'])
            ->name('api.contacts.email');
        
        Route::get('/tel', [SiteInformationController::class, 'fetchTelNo'])
            ->name('api.contacts.tel');
        
        Route::get('/phone', [SiteInformationController::class, 'fetchPhoneNo'])
            ->name('api.contacts.phone');

        Route::get('/address', [SiteInformationController::class, 'fetchAddress'])
            ->name('api.contacts.address');
        
        Route::get('/telegram', [SiteInformationController::class, 'fetchTelegram'])
            ->name('api.contacts.telegram');
        
        Route::get('/facebook', [SiteInformationController::class, 'fetchFacebook'])
            ->name('api.contacts.facebook');
        
        Route::get('/viber', [SiteInformationController::class, 'fetchViber'])
            ->name('api.contacts.viber');
        
        Route::get('/whatsapp', [SiteInformationController::class, 'fetchWhatsapp'])
            ->name('api.contacts.whatsapp');
        
        Route::get('/all', [SiteInformationController::class, 'fetchAllContacts'])
            ->name('api.contacts.all');
    });

    // Customer Registration API Endpoints
    Route::get('/customer-registrations', [CustomerRegistrationController::class, 'apiIndex'])
        ->name('api.customer-registrations.index');

    Route::post('/customer-registrations', [CustomerRegistrationController::class, 'storeApi'])
        ->name('api.customer-registrations.store');

    Route::get('/customer-registrations/{id}', [CustomerRegistrationController::class, 'showApi'])
        ->where('id', '[0-9]+')
        ->name('api.customer-registrations.show');
});

// Site Settings API (Public) - For logo and name display
Route::get('/site-settings', [SiteSettingsController::class, 'fetchSiteSettings'])
    ->name('api.site-settings');

