<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tính đúng doanh thu dashboard với dữ liệu thật', function () {
    // Arrange: Tạo dữ liệu giả
    Order::factory()->completed()->create(['total_amount' => 1000000, 'created_at' => now()->subDays(10)]);
    Order::factory()->completed()->create(['total_amount' => 2000000, 'created_at' => now()->subDays(5)]);
    Order::factory()->count(3)->create(['order_status' => 'pending']); // Không tính

    // Act
    $service = new ReportService();
    $result  = $service->getDashboardStats(
        Carbon::now()->subDays(15),
        Carbon::now()
    );

    // Assert
    expect($result['revenue'])->toBe(3000000.0);
    expect($result['orders'])->toBe(2);
    expect($result['revenue_growth'])->toBeString();
});

it('báo lỗi khi ngày bắt đầu lớn hơn ngày kết thúc', function () {
    $service = new ReportService();
    
    $service->getDashboardStats(
        Carbon::parse('2025-12-01'),
        Carbon::parse('2025-11-01')
    );
})->throws(InvalidArgumentException::class, 'Ngày bắt đầu không được lớn hơn ngày kết thúc');

it('hỗ trợ kỳ week và month trong sales trend', function () {
    Order::factory()->completed()->create(['total_amount' => 5000000, 'created_at' => now()->subDays(7)]);

    $service = new ReportService();
    $result  = $service->getSalesTrend('week', 30);

    expect($result['labels'])->toBeArray();
    expect($result['data'])->toContain(5000000.0);
});