<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'customer.full_name' => 'required',
            'customer.phone' => 'required',
            'customer.address' => 'required',
            'cart_items' => 'required|array', // Mảng sp từ Vue gửi lên
            'cart_items.*.id' => 'required|exists:products,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // 2. Tính toán tổng tiền
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($request->cart_items as $item) {
                $product = Product::find($item['id']);

                // Kiểm tra tồn kho (nếu cần)
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng.");
                }

                $price = $product->sale_price ?? $product->price; // Ưu tiên giá sale
                $lineTotal = $price * $item['quantity'];
                $subtotal += $lineTotal;

                // Chuẩn bị data cho bảng order_items
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $item['image'] ?? null, // Ảnh lấy từ FE hoặc DB
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineTotal,
                ];
            }

            // 3. Tạo Order (Bảng orders)
            $order = Order::create([
                'order_code' => 'ORD-' . strtoupper(Str::random(10)),
                'full_name' => $request->customer['full_name'],
                'email' => $request->customer['email'],
                'phone' => $request->customer['phone'],
                'address' => $request->customer['address'],
                'subtotal' => $subtotal,
                'total_amount' => $subtotal, // Cộng thêm ship nếu có
                'payment_method' => $request->payment_method ?? 'cod',
                'order_status' => 'pending'
            ]);

            // 4. Tạo Order Items (Bảng order_items)
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            // 5. Nếu user đã login, xóa cart_sessions tương ứng (nếu bạn dùng bảng này)
            // CartSession::where('user_id', auth()->id())->delete();

            DB::commit();

            return response()->json(['message' => 'Đặt hàng thành công!', 'order_code' => $order->order_code], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
