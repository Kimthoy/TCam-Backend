<?php

use App\Http\Controllers\Admin\AboutUsController;
use App\Http\Controllers\Admin\AdminLocationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerCategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\ManageCVController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PartnerWithUsController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\CLient\ApplyCVController;
use App\Http\Controllers\Admin\WidgetController;
use App\Http\Controllers\Admin\SupportSystemController;
use App\Http\Controllers\Admin\WhyJoinUsController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('/contact', [ContactMessageController::class, 'store']);
Route::get('public/events', [EventController::class, 'index']);
Route::get('/banners/public', [BannerController::class, 'publicBanners']);
Route::get('/posts/public', [PostController::class, 'publicIndex']);
Route::get('/services/public', [ServiceController::class, 'publicIndex']);
Route::get('/customers/public', [CustomerController::class, 'publicIndex']);
Route::get('/partners/public', [PartnerController::class, 'publicIndex']);
Route::get('/jobs/public/{id}', [JobController::class, 'show']);
Route::get('/public/support', [SupportSystemController::class, 'index']);
Route::get('/jobs/public', [JobController::class, 'index']);
Route::get('/about_us/public', [AboutUsController::class, 'index']);
Route::get('/public/location-system', [AdminLocationController::class, 'index']);
Route::get('/products/public', [ProductController::class, 'publicIndex']);
Route::get('/products/public/{id}', [ProductController::class, 'publicShow']);
Route::post('/contact-messages', [ContactMessageController::class, 'store']);
Route::get('/public/widgets', [WidgetController::class, 'index']);
Route::post('/jobs/{job}/apply', [ApplyCVController::class, 'store']);
Route::get('/public/industries', [IndustryController::class, 'index']);
Route::get('/public/whyjoinus', [WhyJoinUsController::class, 'index']);
Route::get('/public/partner-with-us', [PartnerWithUsController::class, 'index']);
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('user', [AuthController::class, 'updateProfile']);
    Route::post('user/password', [AuthController::class, 'changePassword']);
});



Route::middleware(['auth:api'])->prefix('admin')->group(function () {

    Route::get('jobs', [JobController::class, 'index']);
    Route::get('jobs/{id}', [JobController::class, 'show']);
    Route::post('jobs', [JobController::class, 'store']);
    Route::put('jobs/{id}', [JobController::class, 'update']);
    Route::delete('jobs/{id}', [JobController::class, 'destroy']);
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

    //Careers
    Route::apiResource('careers', CareerController::class);
  
    Route::post('careers/{id}/restore', [CareerController::class, 'restore']);
    Route::delete('careers/{id}/force', [CareerController::class, 'forceDelete']);


    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'activity']);

    Route::get('settings', [SettingsController::class, 'index']);
    Route::put('settings', [SettingsController::class, 'update']);
    Route::post('settings/refresh-cache', [SettingsController::class, 'refreshCache']);


    //Manage apply cv 
    Route::get('/job-applications', [ManageCVController::class, 'index']);
    Route::get('/job-applications/{id}', [ManageCVController::class, 'show']);
    Route::delete('/job-applications/{id}', [ManageCVController::class, 'destroy']);
    Route::put('/job-applications/{id}/status',[ManageCVController::class, 'updateStatus']);


    Route::get('about_us', [AboutUsController::class, 'index']);
    Route::get('about_us/{id}', [AboutUsController::class, 'show']);
    Route::post('about_us', [AboutUsController::class, 'store']);
    Route::put('about_us/{id}', [AboutUsController::class, 'update']); 
    Route::delete('about_us/{id}', [AboutUsController::class, 'destroy']);


    Route::get('widgets', [WidgetController::class, 'index']);
    Route::post('widgets', [WidgetController::class, 'store']);
    Route::get('widgets/{id}', [WidgetController::class, 'show']);
    Route::put('widgets/{id}', [WidgetController::class, 'update']);
    Route::delete('widgets/{id}', [WidgetController::class, 'destroy']);


    // Full CRUD for support system
Route::post('/support-system', [SupportSystemController::class, 'store']);
Route::get('/support-system', [SupportSystemController::class, 'index']);
Route::get('/support-system/{id}', [SupportSystemController::class, 'show']);
Route::put('/support-system/{id}', [SupportSystemController::class, 'update']);
Route::delete('/support-system/{id}', [SupportSystemController::class, 'destroy']); // delete all

// Individual deletes
Route::delete('/support-plan/{id}', [SupportSystemController::class, 'destroyPlan']);
Route::delete('/support-option/{id}', [SupportSystemController::class, 'destroyOption']);
Route::delete('/support-feature/{id}', [SupportSystemController::class, 'destroyFeature']);



    Route::post('/location-system', [AdminLocationController::class, 'store']);
    Route::get('/location-system', [AdminLocationController::class, 'index']);
    Route::put('/location-system/{id}', [AdminLocationController::class, 'update']);
    Route::delete('/location-system/{id}', [AdminLocationController::class, 'destroy']);


    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events', [EventController::class, 'index']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);


    Route::post('/industries', [IndustryController::class, 'store']);    
    Route::get('/industries', [IndustryController::class, 'index']);
    Route::put('/industries/{id}', [IndustryController::class, 'update']);
    Route::delete('/industries/{id}', [IndustryController::class, 'destroy']);
    Route::get('/industries/{id}', [IndustryController::class, 'show']);


    Route::post('/whyjoinus', [WhyJoinUsController::class, 'store']);    
    Route::get('/whyjoinus', [WhyJoinUsController::class, 'index']);
    Route::put('/whyjoinus/{id}', [WhyJoinUsController::class, 'update']);
    Route::delete('/whyjoinus/{id}', [WhyJoinUsController::class, 'destroy']);
    Route::get('/whyjoinus/{id}', [WhyJoinUsController::class, 'show']);




    Route::get('/partner-with-us', [PartnerWithUsController::class, 'index']);
    Route::post('/partner-with-us', [PartnerWithUsController::class, 'store']);       
    Route::get('/partner-with-us/{section}', [PartnerWithUsController::class, 'show']); 
    Route::put('/partner-with-us/{section}', [PartnerWithUsController::class, 'update']); 
    Route::delete('/partner-with-us/{section}', [PartnerWithUsController::class, 'destroy']);
    Route::post('/partner-with-us/{section}/cards', [PartnerWithUsController::class, 'storeCard']); 
    Route::get('/partner-with-us/cards/{card}', [PartnerWithUsController::class, 'showCard']);      
    Route::put('/partner-with-us/cards/{card}', [PartnerWithUsController::class, 'updateCard']);  
    Route::delete('/partner-with-us/cards/{card}', [PartnerWithUsController::class, 'destroyCard']);
});
