<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteInformationController;
use App\Http\Controllers\SiteSettingsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dashboard API Routes (Protected - Web Session Auth)
Route::middleware(['web', 'auth'])->prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'index'])
        ->middleware('throttle:120,1') // 120 requests per minute for real-time updates
        ->name('api.dashboard.stats');
    
    Route::get('/overview', [DashboardController::class, 'overview'])
        ->middleware('throttle:120,1') // 120 requests per minute
        ->name('api.dashboard.overview');
    
    Route::get('/recent-activity', [DashboardController::class, 'recentActivity'])
        ->middleware('throttle:120,1') // 120 requests per minute
        ->name('api.dashboard.recent-activity');
    
    Route::get('/latest-backup', [DashboardController::class, 'latestBackup'])
        ->middleware('throttle:120,1') // 120 requests per minute
        ->name('api.dashboard.latest-backup');
    
    Route::post('/clear-cache', [DashboardController::class, 'clearCache'])
        ->middleware('throttle:10,1') // 10 requests per minute (less frequent)
        ->name('api.dashboard.clear-cache');
});

// Public API Routes with rate limiting (v1)
Route::prefix('v1')->group(function () {
    // Blog API Endpoints
    Route::get('/blogs', [BlogController::class, 'apiIndex'])
        ->middleware('throttle:60,1') // 60 requests per minute
        ->name('api.blogs.index');
    
    Route::get('/blogs/latest', [BlogController::class, 'apiLatest'])
        ->middleware('throttle:100,1') // 100 requests per minute (more generous for latest)
        ->name('api.blogs.latest');
    
    Route::get('/blogs/{id}', [BlogController::class, 'showApi'])
        ->middleware('throttle:100,1') // 100 requests per minute
        ->where('id', '[0-9]+')
        ->name('api.blogs.show');
    
    Route::get('/blogs/{id}/related', [BlogController::class, 'relatedBlogs'])
        ->middleware('throttle:100,1') // 100 requests per minute
        ->where('id', '[0-9]+')
        ->name('api.blogs.related');
    
    // Product API Endpoints
    Route::get('/products', [ProductController::class, 'apiIndex'])
        ->middleware('throttle:60,1') // 60 requests per minute
        ->name('api.products.index');
    
    Route::get('/products/latest', [ProductController::class, 'apiLatest'])
        ->middleware('throttle:100,1') // 100 requests per minute (more generous for latest)
        ->name('api.products.latest');
    
    // Site Information Contact API Endpoints (Public)
    Route::prefix('contacts')->group(function () {
        Route::get('/email', [SiteInformationController::class, 'fetchEmail'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.email');
        
        Route::get('/tel', [SiteInformationController::class, 'fetchTelNo'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.tel');
        
        Route::get('/phone', [SiteInformationController::class, 'fetchPhoneNo'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.phone');

        Route::get('/address', [SiteInformationController::class, 'fetchAddress'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.address');
        
        Route::get('/telegram', [SiteInformationController::class, 'fetchTelegram'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.telegram');
        
        Route::get('/facebook', [SiteInformationController::class, 'fetchFacebook'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.facebook');
        
        Route::get('/viber', [SiteInformationController::class, 'fetchViber'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.viber');
        
        Route::get('/whatsapp', [SiteInformationController::class, 'fetchWhatsapp'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.whatsapp');
        
        Route::get('/all', [SiteInformationController::class, 'fetchAllContacts'])
            ->middleware('throttle:120,1') // 120 requests per minute
            ->name('api.contacts.all');
    });
});

// Site Settings API (Public) - For logo and name display
Route::get('/site-settings', [SiteSettingsController::class, 'fetchSiteSettings'])
    ->middleware('throttle:200,1') // 200 requests per minute
    ->name('api.site-settings');

