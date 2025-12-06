<?php

namespace App\Http\Controllers\Admin; // Lưu ý namespace nên là Api nếu để trong folder Api

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

// Đảm bảo bạn đã tạo Model, nếu chưa thì dùng DB::table
use App\Models\Order; 
use App\Models\User;
use App\Models\Product;

class StatisticsController extends Controller
{
    // Helper lấy ngày
    private function getDateRange(Request $request)
    {
        $period = $request->query('period', 'this_month');
        
        switch ($period) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'yesterday':
                return [Carbon::yesterday(), Carbon::yesterday()->endOfDay()];
            case 'this_week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    // 1. Tổng quan
    public function getOverview(Request $request) // Đổi tên cho khớp route cũ
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        // Cache 60 phút
        $data = Cache::remember('stats_overview_' . $request->query('period', 'this_month'), 60, function () use ($startDate, $endDate) {
            
            // SỬA: status -> order_status, delivered -> completed, total_price -> total_amount
            $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
                            ->where('order_status', 'completed') 
                            ->sum('total_amount');

            $orderCount = Order::whereBetween('created_at', [$startDate, $endDate])->count();
            
            // User có thể không có model User nếu chỉ test DB, nên dùng DB::table cho chắc
            $newCustomers = DB::table('users')->whereBetween('created_at', [$startDate, $endDate])->count();
            
            $avgOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

            return [
                'revenue' => (float) $revenue,
                'orders' => (int) $orderCount, // Vue đang gọi là 'orders' chứ không phải 'orderCount'
                'customers' => (int) $newCustomers, // Vue gọi là 'customers'
                'averageOrderValue' => (float) $avgOrderValue,
                // Vue cần products_sold
                'products_sold' => DB::table('order_items')
                                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                    ->where('orders.order_status', 'completed')
                                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                                    ->sum('order_items.quantity')
            ];
        });

        return response()->json($data);
    }

    // 2. Doanh thu theo thời gian
    public function getRevenueOverTime(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status', 'completed') // SỬA: order_status
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total') // SỬA: total_amount
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return response()->json($data);
    }
    
    // 3. Doanh số theo danh mục
    public function getSalesByCategory(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        // Lưu ý: Code này giả định bạn CÓ bảng categories. 
        // Nếu không có bảng categories (như dữ liệu mẫu), code sẽ lỗi.
        // Tôi giữ nguyên join logic của bạn nhưng sửa tên cột quantity
        
        $data = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'completed') // Nên thêm điều kiện này
            ->select('categories.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->groupBy('categories.name')
            ->get();
        
        return response()->json($data);
    }

    // 4. Các hoạt động gần đây (Cho Dashboard)
    public function getRecentActivities()
    {
        // Cần đảm bảo Model Order có function user() { return $this->belongsTo(User::class); }
        $recentOrders = Order::with('user:id,full_name') // DB mẫu là full_name, ko phải name
                             ->latest()
                             ->take(5)
                             ->get();

        // SỬA: stock_quantity -> quantity
        $lowStockProducts = Product::where('quantity', '<', 10) 
                                   ->orderBy('quantity', 'asc')
                                   ->take(5)
                                   ->get();

        return response()->json([
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}