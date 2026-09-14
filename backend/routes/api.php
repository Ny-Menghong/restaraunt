<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BakongController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QRMenuController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =====================
// Public auth routes
// =====================
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');
// =====================
// Public customer QR self-ordering
// =====================
Route::get('/qr/{table:qr_token}', [QRMenuController::class, 'show'])->name('show');
Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/items', [OrderController::class, 'addItems'])->name('orders.addItems');
Route::middleware(['auth:sanctum','role:admin'])->group(function (){
    Route::get('/ilovu',function(){
        return 'love u';
    });

});
// =====================
// Authenticated routes
// =====================
Route::middleware(['auth:sanctum', 'user.active'])->group(function () {
    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    // Users
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::post('/users', [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    // Tables
    Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
    Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
    Route::get('/tables/{table}', [TableController::class, 'show'])->name('tables.show');
    Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('tables.destroy');
    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    



    // Foods (admin create/update/delete, public index above)
    Route::post('/foods', [FoodController::class, 'store'])->name('foods.store');
    Route::get('/foods/{food}', [FoodController::class, 'show'])->name('foods.show');
    Route::put('/foods/{food}', [FoodController::class, 'update'])->name('foods.update');
    Route::delete('/foods/{food}', [FoodController::class, 'destroy'])->name('foods.destroy');
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    // Payments
        Route::post('/payment', [PaymentController::class, 'pay'])->name('payment.pay');
        Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
        // Bakong KHQR
        Route::post('/bakong/checkout', [BakongController::class, 'checkout'])->name('bakong.checkout');
        Route::post('/bakong/verify', [BakongController::class, 'verify'])->name('bakong.verify');
    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    // User Requests
    Route::get('/user_requests', [UserRequestController::class, 'index'])->name('user_requests.index');
    Route::post('/user_requests', [UserRequestController::class, 'store'])->name('user_requests.store');
    Route::get('/user_requests/{userRequest}', [UserRequestController::class, 'show'])->name('user_requests.show');
    Route::put('/user_requests/{userRequest}', [UserRequestController::class, 'update'])->name('user_requests.update');
    Route::delete('/user_requests/{userRequest}', [UserRequestController::class, 'destroy'])->name('user_requests.destroy');
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
