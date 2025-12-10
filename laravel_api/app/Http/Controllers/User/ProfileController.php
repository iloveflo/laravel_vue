<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class ProfileController extends Controller
{
    // Lấy thông tin user đang đăng nhập
    public function show(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }

    // Cập nhật thông tin
    public function update(Request $request)
    {
        $user = $request->user();

        // Validate dữ liệu
        $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'required|digits:10', // Backend nhận chuỗi thường, Model sẽ tự mã hóa
        ], [
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.digits'   => 'Số điện thoại phải bao gồm đúng 10 chữ số.',
        ]);

        // Cập nhật dữ liệu
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $user
        ]);
    }
}