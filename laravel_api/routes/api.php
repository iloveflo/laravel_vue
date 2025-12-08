<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Product\ProductDetailsController;
use App\Http\Controllers\User\CartController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

// các route liên quan đến admin
Route::middleware('auth:sanctum','admin')->prefix('admin')->group(function() {
    // quản lý đơn hàng
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order_code}', [OrderController::class, 'show']);
    Route::put('/{order_code}/status', [OrderController::class, 'updateStatus']);

<<<<<<< HEAD
    //quản lý người dùng
    Route::get('/users/deleted', [UserController::class,'deletedUsers']);       // danh sách đã xóa
    Route::patch('/users/{id}/restore', [UserController::class,'restore']);    // khôi phục
    Route::get('/users', [UserController::class,'index']);         // danh sách
    Route::post('/users', [UserController::class,'store']);        // tạo user/admin
    Route::get('/users/{id}', [UserController::class,'show']);      // xem chi tiết
    Route::put('/users/{id}', [UserController::class,'update']);    // update
    Route::delete('/users/{id}', [UserController::class,'destroy']); // xóa user
    Route::patch('/users/{id}/status', [UserController::class,'changeStatus']); // change status
    Route::get('/users/{id}/orders', [UserController::class,'orders']); // lịch sử đơn hàng
=======
    // Dành cho biểu đồ sản phẩm bán theo danh mục
    Route::get('/sales-by-category', [StatisticsController::class, 'getSalesByCategory']);

    // Dành cho biểu đồ tròn trạng thái đơn hàng
    Route::get('/order-status-distribution', [StatisticsController::class, 'getOrderStatusDistribution']);

    // Dành cho top selling
    Route::get('/top-selling-products', [StatisticsController::class, 'getTopSellingProducts']);

    // Lấy danh sách đơn hàng mới, sản phẩm sắp hết hàng
    Route::get('/recent-activities', [StatisticsController::class, 'getRecentActivities']);

    // Export dữ liệu
    Route::post('/export', [StatisticsController::class, 'exportReport']);
>>>>>>> 8758146 (Push project to huy branch)
});

//hiển thị sản phẩm
Route::get('/products/category/{slug}', [ProductController::class, 'getByCategory']);
Route::get('/products', [ProductController::class, 'getAll']);

//chi tiết sản phẩm
Route::get('/products/{slug}', [ProductDetailsController::class, 'show']);

// route giỏ hàng
Route::prefix('cart')->group(function() {

    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'addToCart']);
    
    // Xóa item (DELETE /api/cart/remove/{id})
    // Lưu ý: Cần gửi kèm session_id trong body hoặc query param nếu là khách vãng lai
    Route::delete('/remove/{id}', [CartController::class, 'remove']);
});