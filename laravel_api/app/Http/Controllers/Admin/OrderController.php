<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CartSession;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class OrderController extends Controller
{
    // 1. Lấy danh sách đơn hàng (kèm bộ lọc)
    public function index(Request $request)
    {
        // Eager load dùng quan hệ đúng: orderItems
        $query = Order::query()->with(['orderItems', 'user']);

        // Tìm kiếm chung (Mã, Tên, Phone, Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        // Lọc theo phương thức thanh toán
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Lọc theo ngày tạo
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        // Sắp xếp mặc định mới nhất
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($orders);
    }

    // 2. Xem chi tiết đơn hàng
    public function show($order_code)
    {
        $order = Order::with([
            'orderItems',  // Không cần bắt buộc product nếu dữ liệu đã lưu trong order_items
            'user',
            'couponUsages.coupon'
        ])
        ->where('order_code', $order_code)
        ->firstOrFail();

        return response()->json($order);
    }



    // 3. Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $order_code)
    {
        $request->validate([
            'order_status' => 'required|in:pending,confirmed,shipping,completed,cancelled'
        ]);

        // Sửa: orderItems
        $order = Order::with('orderItems')
            ->where('order_code', $order_code)
            ->firstOrFail();

        $oldStatus = $order->order_status;
        $newStatus = $request->order_status;

        if ($oldStatus === $newStatus) {
            return response()->json(['message' => 'Trạng thái không thay đổi']);
        }

        $order->order_status = $newStatus;

        // CASE A: Hoàn thành -> Đã thanh toán
        if ($newStatus == 'completed') {
            $order->payment_status = 'paid';
        }

        // CASE B: Hủy đơn → Hoàn kho
        if ($newStatus == 'cancelled' && $oldStatus != 'cancelled') {
            foreach ($order->orderItems as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->increment('quantity', $item->quantity);
                }
            }
        }

        // CASE C: Khôi phục đơn hủy → Trừ kho
        if ($oldStatus == 'cancelled' && $newStatus != 'cancelled') {
            foreach ($order->orderItems as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->decrement('quantity', $item->quantity);
                }
            }
        }

        $order->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'order_status' => $order->order_status
        ]);
    }

    // 4. Lấy danh sách giỏ hàng bị bỏ quên
    public function abandonedCarts()
    {
        $carts = CartSession::with(['product', 'user'])
            ->select(
                'session_id',
                'user_id',
                DB::raw('SUM(quantity * (SELECT price FROM products WHERE products.id = cart_sessions.product_id)) as total_value'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('MAX(updated_at) as last_activity')
            )
            ->groupBy('session_id', 'user_id')
            ->orderBy('last_activity', 'desc')
            ->paginate(10);

        return response()->json($carts);
    }

    // 5. Lấy chi tiết một giỏ hàng bị bỏ quên
    public function abandonedCartDetail($sessionId)
    {
        $items = CartSession::with('product')->where('session_id', $sessionId)->get();
        return response()->json($items);
    }
}
