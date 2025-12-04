<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartSession;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addToCart(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'size'       => 'nullable|string',
            'color'      => 'nullable|string',
            // session_id là bắt buộc nếu user chưa login (client tự sinh hoặc server cấp)
            'session_id' => 'nullable|string', 
        ]);

        DB::beginTransaction();
        try {
            // 2. Kiểm tra sản phẩm và tồn kho
            $product = Product::findOrFail($validated['product_id']);

            // Kiểm tra xem sản phẩm có đang bán không 
            if (isset($product->status) && $product->status !== 'active') { 
                 return response()->json(['message' => 'Sản phẩm ngừng kinh doanh'], 400);
            }

            // 3. Xác định định danh người dùng (User ID hoặc Session ID)
            $userId = null;
            $sessionId = $request->input('session_id');

            // --- SỬA ĐOẠN NÀY ---
            // ta kiểm tra user qua guard 'sanctum'
            $user = auth('sanctum')->user(); 

            if ($user) {
                // Nếu Token hợp lệ -> Lấy ID từ user tìm thấy
                $userId = $user->id;
            } else {
                // Nếu không có Token hoặc Token sai -> Là khách vãng lai
                if (empty($sessionId)) {
                    // Nếu Client không gửi session_id, Server tạo mới
                    $sessionId = (string) Str::uuid();
                }
            }

            // 4. Kiểm tra xem sản phẩm này với size/color này đã có trong giỏ chưa
            $query = CartSession::where('product_id', $product->id)
                ->where('size', $validated['size'])
                ->where('color', $validated['color']);

            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }

            $cartItem = $query->first();

            // 5. Tính toán số lượng mới
            $newQuantity = $validated['quantity'];
            if ($cartItem) {
                $newQuantity += $cartItem->quantity;
            }

            // 6. Kiểm tra tồn kho (Product Quantity)
            // Lưu ý: Nếu bạn quản lý tồn kho theo Size/Màu thì phải check bảng ProductVariant (nếu có)
            // Ở đây check theo tổng quantity của Product như model bạn cung cấp
            if ($product->quantity < $newQuantity) {
                return response()->json([
                    'message' => 'Sản phẩm không đủ số lượng tồn kho.',
                    'current_stock' => $product->quantity
                ], 400);
            }

            // 7. Lưu vào DB (Tạo mới hoặc Update)
            if ($cartItem) {
                // Update
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                // Tạo mới
                $cartItem = CartSession::create([
                    'session_id' => $sessionId, // Có thể lưu cả session_id cho user đã login để tracking thiết bị
                    'user_id'    => $userId,
                    'product_id' => $validated['product_id'],
                    'quantity'   => $validated['quantity'],
                    'size'       => $validated['size'],
                    'color'      => $validated['color'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm vào giỏ hàng thành công',
                'data' => [
                    'cart_item' => $cartItem,
                    'session_id' => $sessionId // Trả lại session_id để Frontend lưu nếu là lần đầu tạo
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị danh sách giỏ hàng
     */
    public function index(Request $request)
    {
        // 1. Validate
        $request->validate([
            'session_id' => 'nullable|string',
        ]);

        // 2. Xác định người dùng
        $user = auth('sanctum')->user();
        
        // Nếu không có cả user lẫn session_id thì trả về rỗng luôn
        if (!$user && !$request->session_id) {
            return response()->json([
                'status' => 'success', 
                'data' => [], 
                'total' => 0
            ]);
        }

        // 3. Query DB
        $query = CartSession::with([
            'product' => function ($q) {
                // Chỉ lấy các trường cần thiết của product để nhẹ response
                $q->select('id', 'name', 'slug', 'price', 'sale_price', 'status', 'quantity as stock_quantity', 'featured');
            },
            'product.images' => function ($q) {
                // Lấy ảnh đại diện (giả sử bạn có logic lấy ảnh chính, hoặc lấy cái đầu tiên)
                $q->limit(1); 
            }
        ]);

        // Lọc theo User hoặc Session
        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('session_id', $request->session_id);
        }

        // Chỉ lấy những item mà sản phẩm vẫn còn tồn tại (chưa bị xóa cứng/mềm)
        $query->whereHas('product'); 

        $cartItems = $query->orderBy('created_at', 'desc')->get();

        // 4. Tính toán tổng tiền (Optional - tiện cho frontend)
        $totalPrice = 0;
        $formattedItems = $cartItems->map(function ($item) use (&$totalPrice) {
            // Logic giá: Ưu tiên giá sale
            $unitPrice = $item->product->sale_price > 0 ? $item->product->sale_price : $item->product->price;
            $lineTotal = $unitPrice * $item->quantity;
            $totalPrice += $lineTotal;

            return [
                'id' => $item->id, // ID của cart session (để dùng xóa)
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $item->product->images->first()->image_path ?? null, // Sửa 'image_path' theo column thực tế của bạn
                'size' => $item->size,
                'color' => $item->color,
                'quantity' => $item->quantity,
                'stock_quantity' => $item->product->stock_quantity,
                'unit_price' => $unitPrice,
                'original_price' => $item->product->price,
                'line_total' => $lineTotal,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedItems,
            'summary' => [
                'total_items' => $cartItems->sum('quantity'),
                'total_price' => $totalPrice
            ]
        ], 200);
    }

    /**
     * Xóa 1 item khỏi giỏ hàng
     */
    public function remove(Request $request, $id)
    {
        $request->validate([
            'session_id' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user();

        // Tìm item trong giỏ
        $cartItem = CartSession::find($id);

        if (!$cartItem) {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ'], 404);
        }

        // Bảo mật: Kiểm tra xem item này có đúng là của người đang request không
        $isOwner = false;
        
        if ($user) {
            // Nếu là user login: Check user_id
            if ($cartItem->user_id == $user->id) $isOwner = true;
        } else {
            // Nếu là khách: Check session_id
            if ($request->session_id && $cartItem->session_id == $request->session_id) $isOwner = true;
        }

        if (!$isOwner) {
            return response()->json(['message' => 'Bạn không có quyền xóa sản phẩm này'], 403);
        }

        // Xóa
        $cartItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
        ], 200);
    }
}