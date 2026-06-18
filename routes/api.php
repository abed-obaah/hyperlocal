<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RestaurantDashboardController;
use App\Http\Controllers\Api\RestaurantMenuController;
use App\Http\Controllers\Api\RiderController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public auth
|--------------------------------------------------------------------------
*/
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

/*
|--------------------------------------------------------------------------
| Public catalog (browsing doesn't require auth)
|--------------------------------------------------------------------------
*/
Route::get('categories', [CatalogController::class, 'categories']);
Route::get('restaurants', [CatalogController::class, 'restaurants']);
Route::get('restaurants/{restaurant}', [CatalogController::class, 'restaurant']);
Route::get('menu-items/{menuItem}', [CatalogController::class, 'menuItem']);
Route::get('restaurants/{restaurant}/reviews', [ReviewController::class, 'index']);
Route::get('coupons', [CouponController::class, 'index']);
Route::post('coupons/validate', [CouponController::class, 'validateCode']);

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Image uploads (review photos, etc.)
    Route::post('uploads', [UploadController::class, 'store']);

    // ---- Customer ----
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/{order}/advance', [OrderController::class, 'advance']); // demo tracking
    Route::post('restaurants/{restaurant}/reviews', [ReviewController::class, 'store']);
    Route::get('addresses', [AddressController::class, 'index']);
    Route::post('addresses', [AddressController::class, 'store']);
    Route::put('addresses/{address}', [AddressController::class, 'update']);
    Route::delete('addresses/{address}', [AddressController::class, 'destroy']);

    // Favourites
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('restaurants/{restaurant}/favorite', [FavoriteController::class, 'store']);
    Route::delete('restaurants/{restaurant}/favorite', [FavoriteController::class, 'destroy']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/device-token', [NotificationController::class, 'registerDeviceToken']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::get('complaints', [ComplaintController::class, 'index']);
    Route::post('complaints', [ComplaintController::class, 'store']);

    // ---- Restaurant dashboard ----
    Route::prefix('restaurant')->middleware('role:restaurant')->group(function () {
        Route::get('orders', [RestaurantDashboardController::class, 'orders']);
        Route::get('orders/history', [RestaurantDashboardController::class, 'history']);
        Route::get('sales', [RestaurantDashboardController::class, 'sales']);
        Route::get('reviews', [RestaurantDashboardController::class, 'reviews']);
        Route::get('hours', [RestaurantDashboardController::class, 'hours']);
        Route::put('hours', [RestaurantDashboardController::class, 'updateHours']);
        Route::post('orders/{order}/accept', [RestaurantDashboardController::class, 'accept']);
        Route::post('orders/{order}/reject', [RestaurantDashboardController::class, 'reject']);
        Route::post('orders/{order}/preparing', [RestaurantDashboardController::class, 'preparing']);
        Route::post('orders/{order}/ready', [RestaurantDashboardController::class, 'ready']);

        Route::get('menu', [RestaurantMenuController::class, 'index']);
        Route::post('menu', [RestaurantMenuController::class, 'store']);
        Route::put('menu/{menuItem}', [RestaurantMenuController::class, 'update']);
        Route::delete('menu/{menuItem}', [RestaurantMenuController::class, 'destroy']);
        Route::post('menu/{menuItem}/availability', [RestaurantMenuController::class, 'toggleAvailability']);
    });

    // ---- Rider app ----
    Route::prefix('rider')->middleware('role:rider')->group(function () {
        Route::post('availability', [RiderController::class, 'toggleAvailability']);
        Route::patch('availability', [RiderController::class, 'toggleAvailability']);
        Route::get('assignments', [RiderController::class, 'assignments']);
        Route::get('deliveries', [RiderController::class, 'deliveries']);
        Route::get('deliveries/completed', [RiderController::class, 'completed']);
        Route::get('earnings', [RiderController::class, 'earnings']);
        Route::post('deliveries/{delivery}/accept', [RiderController::class, 'accept']);
        Route::patch('deliveries/{delivery}/accept', [RiderController::class, 'accept']);
        Route::post('deliveries/{delivery}/decline', [RiderController::class, 'decline']);
        Route::post('deliveries/{delivery}/arrive', [RiderController::class, 'arriveAtRestaurant']);
        Route::post('deliveries/{delivery}/pickup', [RiderController::class, 'pickup']);
        Route::post('deliveries/{delivery}/on-the-way', [RiderController::class, 'onTheWay']);
        Route::post('deliveries/{delivery}/deliver', [RiderController::class, 'deliver']);
        Route::patch('deliveries/{delivery}/status', [RiderController::class, 'updateStatus']);
    });

    // ---- Admin dashboard ----
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::post('restaurants', [AdminController::class, 'onboardRestaurant']);
        Route::post('riders', [AdminController::class, 'onboardRider']);
        Route::get('orders', [AdminController::class, 'orders']);
        Route::get('riders/available', [AdminController::class, 'availableRiders']);
        Route::get('riders', [AdminController::class, 'riders']);
        Route::post('orders/{order}/assign', [AdminController::class, 'assignRider']);
        Route::post('orders/{order}/complete', [AdminController::class, 'complete']);
        Route::post('orders/{order}/cancel', [AdminController::class, 'cancelOrder']);
        Route::get('payments', [AdminController::class, 'payments']);
        Route::post('orders/{order}/confirm-payment', [AdminController::class, 'confirmPayment']);
        Route::post('orders/{order}/refund', [AdminController::class, 'refundOrder']);
        Route::get('complaints', [AdminController::class, 'complaints']);
        Route::post('complaints/{complaint}/resolve', [AdminController::class, 'resolveComplaint']);
        Route::get('revenue', [AdminController::class, 'revenue']);
    });
});
