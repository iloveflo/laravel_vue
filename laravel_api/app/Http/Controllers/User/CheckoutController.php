<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt; // Thêm thư viện Crypt
use Illuminate\Contracts\Encryption\DecryptException; // Để bắt lỗi giải mã
use App\Models\Order;

class CheckoutController extends Controller
{
    /**
     * Lấy thông tin hiển thị lên form
     * Cần GIẢI MÃ (Decrypt) dữ liệu từ DB để Frontend hiển thị được
     */
    public function getCheckoutInfo(Request $request)
    {
        $user = $request->user('sanctum');

        if ($user) {
            // Xử lý giải mã an toàn (tránh lỗi nếu dữ liệu cũ chưa mã hóa hoặc bị lỗi)
            $phone = '';
            $address = '';

            try {
                // Kiểm tra nếu có dữ liệu thì mới giải mã
                $phone = $user->phone ? Crypt::decryptString($user->phone) : '';
            } catch (DecryptException $e) {
                // Nếu giải mã lỗi (do data cũ là plain text), ta lấy nguyên gốc
                $phone = $user->phone;
            }

            try {
                $address = $user->address ? Crypt::decryptString($user->address) : '';
            } catch (DecryptException $e) {
                $address = $user->address;
            }

            return response()->json([
                'is_logged_in' => true,
                'customer_info' => [
                    'id'        => $user->id,
                    'full_name' => $user->full_name,
                    'email'     => $user->email,
                    'phone'     => $phone,   // Đã giải mã
                    'address'   => $address, // Đã giải mã
                ]
            ]);
        }

        // Khách vãng lai
        return response()->json([
            'is_logged_in' => false,
            'customer_info' => [
                'full_name' => '',
                'email'     => '',
                'phone'     => '',
                'address'   => '',
            ]
        ]);
    }

    /**
     * Xử lý đặt hàng
     * Cần MÃ HÓA (Encrypt) dữ liệu khi cập nhật lại vào bảng User
     */
    public function processCheckout(Request $request)
    {
        // 1. Validate (Dữ liệu từ Frontend gửi lên là Plain Text)
        $validatedData = $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:500',
            'payment_method' => 'required|string',
            'coupon_code'    => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = $request->user('sanctum');

            // 2. Cập nhật thông tin User (MÃ HÓA TRƯỚC KHI LƯU)
            if ($user) {
                $user->full_name = $validatedData['full_name'];
                
                // Logic mã hóa theo yêu cầu của bạn
                $user->phone = $validatedData['phone'] ? Crypt::encryptString($validatedData['phone']) : null;
                $user->address = $validatedData['address'] ? Crypt::encryptString($validatedData['address']) : null;
                
                $user->save();
            }

            // 3. Tạo đơn hàng (Order)
            // LƯU Ý: Thường đơn hàng lưu plain text để Admin/Shipper đọc được dễ dàng.
            // Nếu bảng orders của bạn CŨNG yêu cầu mã hóa, hãy dùng Crypt::encryptString ở đây luôn.
            // Ở đây tôi giả định bảng orders lưu plain text (thông thường).
            
            $order = new Order();
            $order->user_id        = $user ? $user->id : null;
            $order->full_name      = $validatedData['full_name'];
            $order->email          = $validatedData['email'];
            
            // Lưu vào Order (Nếu order cần mã hóa thì bọc Crypt giống ở trên, nếu không thì để nguyên)
            $order->phone          = $validatedData['phone']; 
            $order->address        = $validatedData['address'];
            
            $order->payment_method = $validatedData['payment_method'];
            $order->coupon_code    = $validatedData['coupon_code'];
            $order->status         = 'pending';
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}