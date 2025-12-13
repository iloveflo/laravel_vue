<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ==============================
// Authentication (Social Login)
// ==============================
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

// ==============================
// API routes (optional, for internal use)
// ==============================
// Bạn có thể giữ các route API riêng biệt nếu cần
// Ví dụ:
// Route::prefix('api')->group(function () {
//     Route::get('/users', [UserController::class, 'index']);
// });

// ==============================
// SPA Fallback Route – CUỐI CÙNG
// ==============================
// Bắt tất cả request không phải API hoặc auth, trả về Vue index.html
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
