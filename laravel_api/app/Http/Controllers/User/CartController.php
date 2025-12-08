<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartSession;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addToCart(Request $request)
    {
        // 1. Validate
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'size'       => 'nullable|string',
            'color'      => 'nullable|string', // Lưu ý: Frontend gửi tên màu hay mã màu? Hãy thống nhất.
            'session_id' => 'nullable|string', 
        ]);

        DB::beginTransaction();
        try {
            // 2. Kiểm tra sản phẩm
            $product = Product::findOrFail($validated['product_id']);
            if ($product->status !== 'active') { 
                 return response()->json(['message' => 'Sản phẩm ngừng kinh doanh'], 400);
            }

            // Tìm dòng trong bảng product_variants khớp với Size và Màu khách chọn
            $variant = \App\Models\ProductVariant::where('product_id', $product->id)
                ->where('size', $validated['size']) 
                ->where('color_name', $validated['color']) // Giả sử cột DB là color_name
                ->first();

            // Nếu không tìm thấy biến thể (có thể do khách hack request gửi size linh tinh)
            if (!$variant) {
                return response()->json(['message' => 'Phiên bản sản phẩm không tồn tại'], 404);
            }

            // 3. Logic User/Session (Giữ nguyên code của bạn)
            $userId = null;
            $sessionId = $request->input('session_id');
            $user = auth('sanctum')->user(); 

            if ($user) {
                $userId = $user->id;
            } else {
                if (empty($sessionId)) $sessionId = (string) Str::uuid();
            }

            // 4. Kiểm tra giỏ hàng hiện tại
            $query = CartSession::where('product_id', $product->id)
                ->where('size', $validated['size'])
                ->where('color', $validated['color']); // Lưu ý: cart_sessions lưu cột 'color'

            if ($userId) $query->where('user_id', $userId);
            else $query->where('session_id', $sessionId);

            $cartItem = $query->first();

            // 5. Tính toán số lượng mới
            $newQuantity = $validated['quantity'];
            if ($cartItem) {
                $newQuantity += $cartItem->quantity;
            }

            // Code mới: Check theo $variant->quantity
            if ($variant->quantity < $newQuantity) {
                return response()->json([
                    'message' => 'Sản phẩm chỉ còn ' . $variant->quantity . ' món cho phân loại này.',
                    'current_stock' => $variant->quantity
                ], 400);
            }

            // 6. Lưu vào DB (Giữ nguyên code của bạn)
            if ($cartItem) {
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                $cartItem = CartSession::create([
                    'session_id' => $sessionId,
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
                    'session_id' => $sessionId 
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
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
                $q->select('id', 'name', 'slug', 'price', 'sale_price', 'status', 'featured'); 
            },
            'product.variants', // Load toàn bộ variants để lọc trong memory (hoặc query cụ thể nếu muốn tối ưu hơn nữa)
            'product.images' => function ($q) {
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
            // Tìm variant tương ứng trong list variants đã load sẵn
            // Logic này nhanh hơn là query DB trong vòng lặp
            $variant = $item->product->variants->first(function ($v) use ($item) {
                return $v->size === $item->size && $v->color_name === $item->color;
            });

            // Lấy tồn kho cụ thể của size/màu đó. Nếu lỗi data không tìm thấy thì cho bằng 0
            $specificStock = $variant ? $variant->quantity : 0;

            // Logic giá: Ưu tiên giá sale
            $unitPrice = $item->product->sale_price > 0 ? $item->product->sale_price : $item->product->price;
            $lineTotal = $unitPrice * $item->quantity;
            $totalPrice += $lineTotal;

            $unitPrice = $item->product->sale_price > 0 ? $item->product->sale_price : $item->product->price;
            $lineTotal = $unitPrice * $item->quantity;
            $totalPrice += $lineTotal;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $item->product->main_image_url, 
                'size' => $item->size,
                'color' => $item->color,
                'quantity' => $item->quantity,
                'stock_quantity' => $specificStock, 
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

    /**
     * Cập nhật số lượng sản phẩm trong giỏ (Tăng/Giảm từ giỏ hàng)
     */
    public function update(Request $request)
    {
        // 1. Validate
        $request->validate([
            'id'         => 'required|exists:cart_sessions,id', // ID của dòng trong cart_sessions
            'quantity'   => 'required|integer|min:1',
            'session_id' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user();
        
        // 2. Tìm Cart Item
        $cartItem = CartSession::find($request->id);

        if (!$cartItem) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        // 3. Check quyền sở hữu (Security)
        $isOwner = false;
        if ($user) {
            if ($cartItem->user_id == $user->id) $isOwner = true;
        } else {
            if ($request->session_id && $cartItem->session_id == $request->session_id) $isOwner = true;
        }

        if (!$isOwner) {
            return response()->json(['message' => 'Bạn không có quyền sửa sản phẩm này'], 403);
        }

        // 4. Check tồn kho
        $variant = \App\Models\ProductVariant::where('product_id', $cartItem->product_id)
            ->where('size', $cartItem->size)
            ->where('color_name', $cartItem->color) // Map với cột color trong cart
            ->first();

        if ($variant && $variant->quantity < $request->quantity) {
             return response()->json([
                'message' => 'Kho chỉ còn ' . $variant->quantity . ' sản phẩm.',
            ], 400);
        }

        // 5. Update
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật giỏ hàng thành công'
        ], 200);
    }
}