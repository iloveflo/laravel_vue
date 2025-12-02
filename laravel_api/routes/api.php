<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Admin\OrderController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

// Admin users endpoints (protected by sanctum)
Route::middleware('auth:sanctum')->prefix('admin/users')->group(function() {
    Route::get('/deleted', [UserController::class,'deletedUsers']);       // danh sách đã xóa
    Route::patch('/{id}/restore', [UserController::class,'restore']);    // khôi phục
    Route::get('/', [UserController::class,'index']);         // danh sách
    Route::post('/', [UserController::class,'store']);        // tạo user/admin
    Route::get('/{id}', [UserController::class,'show']);      // xem chi tiết
    Route::put('/{id}', [UserController::class,'update']);    // update
    Route::delete('/{id}', [UserController::class,'destroy']); // xóa user
    Route::patch('/{id}/status', [UserController::class,'changeStatus']); // change status
    Route::get('/{id}/orders', [UserController::class,'orders']); // lịch sử đơn hàng
});

Route::middleware('auth:sanctum')->group(function () {
    // Route này khớp với Vue: axios.get('/api/orders')
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order_code}', [OrderController::class, 'show']);
    // Route này khớp với Vue: axios.patch('/api/orders/{id}/status')
    Route::put('/{order_code}/status', [OrderController::class, 'updateStatus']);
});

Route::get('/products/category/{slug}', [ProductController::class, 'getByCategory']);
Route::get('/products', [ProductController::class, 'getAll']);