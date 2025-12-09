<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Lấy user hiện tại đang đăng nhập
        $user = $request->user();

        // Khởi tạo query lấy đơn hàng của user đó
        // Eager load 'orderItems' để lấy luôn chi tiết sản phẩm, tránh lỗi N+1 query
        $query = Order::with('orderItems')->where('user_id', $user->id);

        // Xử lý lọc theo Tab trạng thái (gửi từ Vue lên)
        // Các status trong DB: pending, confirmed, shipping, completed, cancelled
        if ($request->has('status') && $request->status != 'all') {
            $status = $request->status;

            // Map status của frontend (nếu cần) sang database enum
            // Ví dụ: Tab 'Chờ xác nhận' ứng với 'pending'
            $query->where('order_status', $status);
        }

        // Sắp xếp đơn mới nhất lên đầu
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
}
