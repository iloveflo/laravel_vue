<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Đăng ký admin mới
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'full_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ỹĂăÂâĐđÊêÔôƠơƯư\s]+$/u', // Chỉ cho phép chữ cái và khoảng trắng
            ],
            'phone' => 'required|string|max:20',
        ], [
            'full_name.regex' => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng, không được có số hoặc ký tự đặc biệt',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Tạo username từ email (phần trước @)
        $username = explode('@', $request->email)[0];

        $user = User::create([
            'username' => $username,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Sử dụng Hash::make() với bcrypt (cost factor 10)
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'role' => 'user', // Chỉ cho phép đăng ký user, admin được cấp tài khoản riêng
        ]);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role,
            ]
        ], 201);
    }

    /**
     * Đăng nhập
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Tìm user theo email
        $user = User::where('email', $request->email)->first();

        // Sử dụng Hash::check() để xác thực mật khẩu (so sánh plain text với hash)
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function me(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Gửi link reset password về email
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Vẫn trả về success để không tiết lộ email có tồn tại hay không
            return response()->json([
                'success' => true,
                'message' => 'Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu đến email của bạn'
            ]);
        }

        // Tạo token
        $token = Str::random(64);
        
        // Lưu token vào database - Mã hóa token bằng Hash::make() để bảo mật
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token), // Hash token để bảo mật
                'created_at' => now()
            ]
        );

        // Gửi email (sẽ log vào storage/logs/laravel.log nếu dùng log driver)
        $resetUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);
        
        // Log để test (trong production sẽ gửi email thật)
        \Log::info('Password Reset Link for ' . $request->email . ': ' . $resetUrl);

        return response()->json([
            'success' => true,
            'message' => 'Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu đến email của bạn'
        ]);
    }

    /**
     * Đặt lại mật khẩu
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Tìm token trong database
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        // Kiểm tra token - Sử dụng Hash::check() để xác thực token
        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        // Kiểm tra token còn hạn (60 phút)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn. Vui lòng yêu cầu lại'
            ], 400);
        }

        // Tìm user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email không tồn tại'
            ], 404);
        }

        // Cập nhật mật khẩu - Sử dụng Hash::make() để mã hóa mật khẩu mới
        $user->password = Hash::make($request->password);
        $user->save();

        // Xóa token đã sử dụng
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công'
        ]);
    }
}

