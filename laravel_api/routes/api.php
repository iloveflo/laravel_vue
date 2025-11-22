<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController; // Giả sử bạn đã có controller này để list sp

Route::get('/products', [ProductController::class, 'index']);
Route::post('/checkout', [OrderController::class, 'checkout']);

// Ghi chú: Middleware 'is_admin' cần được tạo và đăng ký sau này
// để đảm bảo chỉ admin mới có quyền truy cập vào các route này.
Route::middleware(['auth:sanctum'/*, 'is_admin'*/])->prefix('admin/statistics')->group(function () {
    // Dành cho KPI cards và các số liệu tổng quan
    Route::get('/overview', [StatisticsController::class, 'getOverview']);

    // Dành cho biểu đồ doanh thu
    Route::get('/revenue-over-time', [StatisticsController::class, 'getRevenueOverTime']);

    // Dành cho biểu đồ sản phẩm bán theo danh mục
    Route::get('/sales-by-category', [StatisticsController::class, 'getSalesByCategory']);

    // Dành cho biểu đồ tròn trạng thái đơn hàng
    Route::get('/order-status-distribution', [StatisticsController::class, 'getOrderStatusDistribution']);

    // Lấy danh sách đơn hàng mới, sản phẩm sắp hết hàng
    Route::get('/recent-activities', [StatisticsController::class, 'getRecentActivities']);

    // Export dữ liệu
    Route::post('/export', [StatisticsController::class, 'exportReport']);
});

// Routes for reports
Route::middleware(['auth:sanctum'])->prefix('admin/reports')->group(function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/top-products', [ReportController::class, 'topProducts']);
    Route::get('/by-category', [ReportController::class, 'byCategory']);
    Route::get('/sales-trend', [ReportController::class, 'salesTrend']);
    Route::get('/export', [ReportController::class, 'export']);
});
