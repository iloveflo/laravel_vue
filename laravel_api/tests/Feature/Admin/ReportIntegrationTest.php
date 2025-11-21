<?php
// tests/Feature/Admin/ReportIntegrationTest.php

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Tạo admin user
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    // Tạo category và product
    $this->category = Category::factory()->create(['status' => 'active']);
    $this->product  = Product::factory()->create(['category_id' => $this->category->id]);

    // Tạo đơn hàng hoàn thành trong 10 ngày gần đây
    Order::factory()->count(5)->completed()->create([
        'created_at' => Carbon::now()->subDays(5),
        'total_amount' => 1500000,
    ]);

    // Tạo đơn hàng hôm nay
    $todayOrder = Order::factory()->completed()->create([
        'created_at' => now(),
        'total_amount' => 3000000,
    ]);

    // Thêm order items
    OrderItem::factory()->create([
        'order_id' => $todayOrder->id,
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'product_image' => 'products/test.jpg',
        'quantity' => 10,
        'subtotal' => 3000000,
    ]);
});

it('admin có thể xem dashboard báo cáo với filter ngày hợp lệ', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/reports/dashboard?start_date=2025-11-01&end_date=2025-11-20');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'revenue',
            'orders',
            'items_sold',
            'avg_order_value',
            'revenue_growth',
            'growth_positive',
            'period',
        ])
        ->assertJson([
            'period' => '01/11/2025 → 20/11/2025',
        ]);

    expect($response->json('revenue'))->toBeGreaterThan(0);
});

it('trả về 422 nếu ngày kết thúc nhỏ hơn ngày bắt đầu', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/reports/dashboard?start_date=2025-11-20&end_date=2025-11-01');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

it('trả về 401 nếu user không phải admin truy cập', function () {
    $normalUser = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($normalUser, 'sanctum')
        ->getJson('/api/admin/reports/dashboard');

    $response->assertStatus(403); // hoặc 401 tùy middleware
});

it('top products trả về đúng sản phẩm bán chạy nhất', function () {
    // Tạo thêm 1 sản phẩm bán ít hơn
    $product2 = Product::factory()->create();
    $order2 = Order::factory()->completed()->create();
    OrderItem::factory()->create([
        'order_id' => $order2->id,
        'product_id' => $product2->id,
        'product_name' => $product2->name,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/reports/top-products?limit=5');

    $response->assertStatus(200);

    $json = $response->json();
    expect($json[0]['units_sold'])->toBe(10); // Sản phẩm đầu tiên bán 10 cái
    expect($json[0]['name'])->toBe($this->product->name);
});

it('sales by category trả về đúng danh mục có doanh thu', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/reports/by-category');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => $this->category->name,
        ]);

    $totalPercentage = collect($response->json())->sum('percentage');
    expect($totalPercentage)->toBe(100.0); // Tổng phần trăm phải = 100%
});

it('export excel thành công với filter ngày', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/admin/reports/export?start_date=2025-11-01&end_date=2025-11-20');

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertHeader('content-disposition', fn ($value) => str_contains($value, 'Bao-cao-doanh-so-Florentic'));
});

it('trả về 422 khi export với ngày không hợp lệ', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/admin/reports/export?end_date=2025-11-01');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

it('sales trend trả về dữ liệu đúng với filter', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/reports/sales-trend?period=day&days=30');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'labels',
            'data',
        ]);

    expect(count($response->json('labels')))->toBeGreaterThan(0);
    expect(count($response->json('data')))->toBeGreaterThan(0);
});
it('trả về 422 khi sales trend với kỳ không hợp lệ', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/reports/sales-trend?period=year&days=30');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['period']);
});
it('trả về 422 khi sales trend với số ngày vượt quá giới hạn', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/reports/sales-trend?period=day&days=400');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['days']);
});