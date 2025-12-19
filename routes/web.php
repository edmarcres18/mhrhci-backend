<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteInformationController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureUserHasAdminPrivileges;
use App\Http\Middleware\EnsureUserIsSystemAdmin;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('blogs', BlogController::class);
    Route::resource('users', UserController::class);
    Route::resource('principals', PrincipalController::class);

    // User invitation routes
    Route::get('users-invite', [UserController::class, 'inviteForm'])->name('users.invite.form');
    Route::post('users-invite', [UserController::class, 'sendInvitation'])->name('users.invite.send');
    Route::get('invitations', [UserController::class, 'invitations'])->name('invitations.index');
    Route::post('invitations/{invitation}/resend', [UserController::class, 'resendInvitation'])->name('invitations.resend');
    Route::delete('invitations/{invitation}', [UserController::class, 'cancelInvitation'])->name('invitations.cancel');

    // Site Information - Single record management (Admin only)
    Route::middleware([EnsureUserHasAdminPrivileges::class])->group(function () {
        Route::get('site-information', [SiteInformationController::class, 'index'])->name('site-information.index');
        Route::post('site-information', [SiteInformationController::class, 'store'])->name('site-information.store');
        Route::delete('site-information', [SiteInformationController::class, 'destroy'])->name('site-information.destroy');

        Route::get('hero-backgrounds', function () {
            return Inertia::render('HeroBackgrounds/Index');
        })->name('hero-backgrounds.index');

        // Database Backup Management (Admin only)
        Route::get('database-backup', [DatabaseBackupController::class, 'index'])->name('database-backup.index');
        Route::post('database-backup/create', [DatabaseBackupController::class, 'backup'])->name('database-backup.create');
        Route::get('database-backup/download/{filename}', [DatabaseBackupController::class, 'download'])->name('database-backup.download');
        Route::delete('database-backup/{filename}', [DatabaseBackupController::class, 'destroy'])->name('database-backup.delete');
        Route::post('database-backup/restore', [DatabaseBackupController::class, 'restore'])->name('database-backup.restore');
        Route::post('database-backup/upload-restore', [DatabaseBackupController::class, 'uploadAndRestore'])->name('database-backup.upload-restore');
    });

    // Site Settings - System Admin only
    Route::middleware([EnsureUserIsSystemAdmin::class])->group(function () {
        Route::get('site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
        Route::post('site-settings', [SiteSettingsController::class, 'update'])->name('site-settings.update');
        Route::post('site-settings/remove-logo', [SiteSettingsController::class, 'removeLogo'])->name('site-settings.remove-logo');
        Route::post('site-settings/reset', [SiteSettingsController::class, 'reset'])->name('site-settings.reset');
    });

    // Customer Registrations pages (create/show/delete only managed via API)
    Route::get('customer-registrations', function () {
        return Inertia::render('CustomerRegistrations/Index');
    })->name('customer-registrations.index');

    Route::get('customer-registrations/create', function () {
        return Inertia::render('CustomerRegistrations/Create');
    })->name('customer-registrations.create');

    Route::get('customer-registrations/{id}', function ($id) {
        return Inertia::render('CustomerRegistrations/Show', ['id' => (int) $id]);
    })->where('id', '[0-9]+')->name('customer-registrations.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
