<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    // Hàm helper để lấy khoảng thời gian
    private function getDateRange(Request $request)
    {
        $period = $request->query('period', 'this_month'); // today, yesterday, this_week, this_month
        
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
                // Mặc định là tháng này
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    public function getOverview(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        // Sử dụng Cache để tăng tốc độ, cache trong 60 phút
        $data = Cache::remember('stats_overview_' . $request->query('period', 'this_month'), 60, function () use ($startDate, $endDate) {
            $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
                            ->where('order_status', 'delivered') // Chỉ tính doanh thu đơn đã giao
                            ->sum('total_amount');

            $orderCount = Order::whereBetween('created_at', [$startDate, $endDate])->count();
            
            $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])->count();
            
            $avgOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

            return [
                'revenue' => (float) $revenue,
                'orderCount' => (int) $orderCount,
                'newCustomers' => (int) $newCustomers,
                'averageOrderValue' => (float) $avgOrderValue,
            ];
        });

        return response()->json($data);
    }

    public function getRevenueOverTime(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status', 'delivered')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return response()->json($data);
    }
    
    public function getSalesByCategory(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('categories.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->groupBy('categories.name')
            ->get();
        
        return response()->json($data);
    }

    public function getOrderStatusDistribution(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->get();
            
        return response()->json($data);
    }

    public function getTopSellingProducts(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $limit = $request->input('limit', 5);

        $data = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                'order_items.product_image',
                'products.price',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'order_items.product_image', 'products.price')
            ->orderBy('total_sold', 'desc')
            ->take($limit)
            ->get();

        return response()->json($data);
    }

    public function getRecentActivities()
    {
        $recentOrders = Order::with('user:id,full_name')->latest()->take(5)->get();
        $lowStockProducts = Product::where('quantity', '<', 10)->orderBy('quantity', 'asc')->take(5)->get();

        return response()->json([
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
    
    public function exportReport(Request $request)
    {
        return response()->json(['message' => 'Export function placeholder']);
    }
}
