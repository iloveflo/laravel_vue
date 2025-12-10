<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Cần thêm thư viện này để xử lý file/ảnh
use App\Models\User;

class ProfileController extends Controller
{
    // Lấy thông tin user đang đăng nhập
    public function show(Request $request)
    {
        $user = $request->user();

        // Nếu avatar chưa có full đường dẫn (ví dụ chỉ lưu "uploads/..."), bạn có thể xử lý ở đây hoặc ở Frontend
        // Nhưng tốt nhất trả về raw data, frontend tự xử lý path.

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    // Cập nhật thông tin
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validate dữ liệu
        $request->validate([
            'full_name' => 'required|string|max:100',
            'phone'     => 'required|digits:10', // Bắt buộc 10 số
            'address'   => 'nullable|string|max:255', // Thêm validate địa chỉ
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Thêm validate ảnh (tối đa 2MB)
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'phone.required'     => 'Vui lòng nhập số điện thoại.',
            'phone.digits'       => 'Số điện thoại phải bao gồm đúng 10 chữ số.',
            'avatar.image'       => 'File tải lên phải là hình ảnh.',
            'avatar.max'         => 'Ảnh không được vượt quá 2MB.',
        ]);

        // 2. Xử lý Avatar (Nếu có gửi lên)
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ nếu tồn tại để tránh rác server (kiểm tra disk public)
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Lưu ảnh mới vào thư mục storage/app/public/uploads/avatar
            // Hàm store trả về đường dẫn tương đối: "uploads/avatar/ten_file.jpg"
            $path = $request->file('avatar')->store('uploads/avatar', 'public');
            $user->avatar = $path;
        }

        // 3. Cập nhật thông tin text
        $user->full_name = $request->full_name;
        $user->phone     = $request->phone;
        $user->address   = $request->address; // Cập nhật địa chỉ mới

        // QUAN TRỌNG: Đã xóa dòng cập nhật Email ($user->email = ...) 
        // để đảm bảo Email không bao giờ bị thay đổi từ API này.

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $user
        ]);
    }
}
