<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;

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