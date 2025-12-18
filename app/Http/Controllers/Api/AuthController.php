<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // 1. Đăng ký người dùng mới
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Mã hóa pass
        ]);

        // Tạo token ngay khi đăng ký xong
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    // 2. Đăng nhập (Lấy Token)
    public function login(Request $request) {
        // Kiểm tra email user gửi lên có tồn tại không
        $user = User::where('email', $request->email)->first();

        // Kiểm tra pass có khớp không
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Tài khoản hoặc mật khẩu sai'
            ], 401);
        }

        // Nếu đúng hết -> Cấp Token mới
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Chào mừng quay lại, ' . $user->name,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // 3. Đăng xuất (Xóa Token)
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}