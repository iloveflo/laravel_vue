<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'rememberMe' => 'sometimes|boolean', // Thêm trường rememberMe
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Sai thông tin đăng nhập'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Sai thông tin đăng nhập'], 401);
        }

        // --- Tạo token ---
        $tokenName = 'auth-token';
        $token = $user->createToken($tokenName)->plainTextToken;

        // --- Nếu rememberMe = true và user không phải admin, lưu remember_token ---
        if ($request->rememberMe && $user->role !== 'admin') {
            $user->remember_token = bin2hex(random_bytes(60));
            $user->save();
        }

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // Current authenticated user info
    public function me(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(null, 401);
        }

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'avatar' => $this->formatAvatar($user->avatar),
            'role' => $user->role,
        ]);
    }

    // helper to normalize avatar paths returned by API
    private function formatAvatar($avatar)
    {
        if (! $avatar) return null;
        $a = (string) $avatar;
        if (Str::startsWith($a, 'http')) return $a;
        if (Str::startsWith($a, '/')) return $a;
        if (Str::startsWith($a, 'public/uploads/')) {
            return '/' . preg_replace('/^public\//', '', $a);
        }
        if (Str::startsWith($a, 'uploads/')) {
            return '/' . $a;
        }
        if (Str::startsWith($a, 'storage/')) {
            return '/' . $a;
        }
        // fallback assume storage disk path
        return '/storage/' . ltrim($a, '/');
    }

    /**
     * Logout (nếu dùng token)
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // 1. Xóa token hiện tại
        $user->currentAccessToken()->delete();

        // 2. Xóa luôn remember_token trong DB
        $user->remember_token = null;
        $user->save();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    public function register(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => [
                'required',
                'string',
                'max:100',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password'  => ['required', 'confirmed', Password::defaults()],
            'full_name' => 'nullable|string|max:100',
            'phone'     => 'nullable|digits:10', // chỉ số, 10 chữ số
            'address'   => 'nullable|string|max:255',
            'avatar'    => 'nullable|image|max:2048', // tối đa 2MB
        ]);


        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');
            $filename = $avatarFile->getClientOriginalName();
            $destination = public_path('uploads/avatar');

            // Tạo folder nếu chưa tồn tại
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            // Kiểm tra xem đã có file cùng tên chưa
            if (!File::exists($destination . '/' . $filename)) {
                // Chưa có -> move file lên
                $avatarFile->move($destination, $filename);
            }
            // Dù file đã tồn tại hay mới upload, lưu đường dẫn relative vào DB
            $avatarPath = 'uploads/avatar/' . $filename;
        }

        // Tạo user mới
        $user = User::create([
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'full_name' => $request->full_name,
            'phone'     => $request->phone ? Crypt::encryptString($request->phone) : null,
            'address'   => $request->address ? Crypt::encryptString($request->address) : null,
            'avatar'    => $avatarPath,
            'role'      => 'user', // mặc định role là user
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Đăng ký thành công!',
            'user'    => $user,
        ], 201);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại trong hệ thống'], 404);
        }

        // Tạo token reset mật khẩu
        $rawToken = Str::random(64);

        // Lưu token thật vào DB
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $rawToken,
                'created_at' => Carbon::now()
            ]
        );

        // Mã hóa email + token
        $data = json_encode([
            'email' => $request->email,
            'token' => $rawToken
        ]);
        $encrypted = Crypt::encryptString($data);

        // Lấy base URL hiện tại (scheme + host + port nếu có)
        $baseUrl = $request->getSchemeAndHttpHost(); // ví dụ: http://localhost:8000 hoặc https://example.com

        // Tạo link reset đầy đủ
        $resetLink = $baseUrl . '/reset-password?code=' . urlencode($encrypted);

        // Gửi email
        Mail::send('reset_password', ['link' => $resetLink], function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Khôi phục mật khẩu - FLORENTIC');
        });

        return response()->json(['message' => 'Email khôi phục đã được gửi'], 200);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'password' => 'required|min:8'
        ]);

        try {
            // Giải mã dữ liệu
            $decoded = Crypt::decryptString($request->code);
            $data = json_decode($decoded, true);

            $email = $data['email'];
            $token = $data['token'];

        } catch (\Exception $e) {
            return response()->json(['message' => 'Mã đặt lại mật khẩu không hợp lệ'], 400);
        }

        // Kiểm tra token trong DB
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Token không tồn tại hoặc đã hết hạn'], 400);
        }

        // Kiểm tra thời gian: 5 phút = 300 giây
        $created = Carbon::parse($record->created_at);
        if (Carbon::now()->diffInSeconds($created) > 300) {
            // Xóa token cũ
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json(['message' => 'Link đặt lại mật khẩu đã hết hạn (5 phút)'], 400);
        }

        // Cập nhật mật khẩu
        User::where('email', $email)->update([
            'password' => bcrypt($request->password),
            'remember_token' => null,
        ]);

        // Xóa token sau khi đổi mật khẩu
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json(['message' => 'Đặt mật khẩu thành công'], 200);
    }

}
