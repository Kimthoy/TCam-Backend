<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerCategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\PublicHomeController;

// ==========================
// PUBLIC ROUTES
// ==========================
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('/contact', [ContactMessageController::class, 'store']);

Route::get('/banners/public', [BannerController::class, 'publicBanners']);
Route::get('/posts/public', [PostController::class, 'publicIndex']);
Route::get('/services/public', [ServiceController::class, 'publicIndex']);
Route::get('/customers/public', [CustomerController::class, 'publicIndex']);
Route::get('/partners/public', [PartnerController::class, 'publicIndex']);

Route::get('/products/public', [ProductController::class, 'publicIndex']);
Route::get('/products/public/{id}', [ProductController::class, 'publicShow']);
 Route::post('contact-messages', [ContactMessageController::class, 'store']);

// ==========================
// AUTHENTICATED USER ROUTES
// ==========================
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('user', [AuthController::class, 'updateProfile']);
    Route::post('user/password', [AuthController::class, 'changePassword']);
});

// ==========================
// ADMIN ROUTES
// ==========================
Route::middleware(['auth:api'])->prefix('admin')->group(function () {

    // Users
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/photo', [UserController::class, 'uploadPhoto']);
    Route::post('users/{id}/restore', [UserController::class, 'restore']);
    Route::delete('users/{id}/force', [UserController::class, 'forceDelete']);

    // Banners
    Route::apiResource('banners', BannerController::class);
    Route::post('banners/{id}/restore', [BannerController::class, 'restore']);
    Route::delete('banners/{id}/force', [BannerController::class, 'forceDelete']);

    // Product Categories & Products
    Route::apiResource('categories', ProductCategoryController::class)->except('show');
    Route::apiResource('products', ProductController::class);
    Route::post('products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('products/{id}/force', [ProductController::class, 'forceDelete']);

    // Service Categories & Services
    Route::apiResource('service-categories', ServiceCategoryController::class)->except('show');
    Route::apiResource('services', ServiceController::class);
    Route::post('services/{id}/restore', [ServiceController::class, 'restore']);
    Route::delete('services/{id}/force', [ServiceController::class, 'forceDelete']);

    // Customer Categories & Customers
    Route::apiResource('customer-categories', CustomerCategoryController::class)->except('show');
    Route::apiResource('customers', CustomerController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Partners
    Route::apiResource('partners', PartnerController::class);
    Route::post('partners/{id}/restore', [PartnerController::class, 'restore']);
    Route::delete('partners/{id}/force', [PartnerController::class, 'forceDelete']);

    // Contact Messages
    Route::apiResource('contact-messages', ContactMessageController::class)
        ->only(['index', 'show', 'destroy']);
    Route::post('contact-messages/{contactMessage}/handled', [ContactMessageController::class, 'markHandled']);
    Route::post('contact-messages/bulk-handled', [ContactMessageController::class, 'bulkMarkHandled']);
   

    // Blog / Posts
    Route::apiResource('post-categories', PostCategoryController::class);
    Route::post('post-categories/{id}/restore', [PostCategoryController::class, 'restore']);
    Route::delete('post-categories/{id}/force', [PostCategoryController::class, 'forceDelete']);

    Route::apiResource('posts', PostController::class);
    Route::post('posts/{id}/restore', [PostController::class, 'restore']);
    Route::delete('posts/{id}/force', [PostController::class, 'forceDelete']);

    // Jobs
    Route::apiResource('jobs', JobController::class);
    Route::post('jobs/{id}/restore', [JobController::class, 'restore']);
    Route::delete('jobs/{id}/force', [JobController::class, 'forceDelete']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'activity']);

    Route::get('settings', [SettingsController::class, 'index']);
    Route::put('settings', [SettingsController::class, 'update']);
    Route::post('settings/refresh-cache', [SettingsController::class, 'refreshCache']);
});
