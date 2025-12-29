<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ProfileuserController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    // 2. Cập nhật thông tin (Tên, SDT, Địa chỉ...)
    public function update(Request $request)
    {
        $user = $request->user();

        // Validate dữ liệu gửi lên
        $request->validate([
            'name' => 'required|string|max:255',
            // 'phone' => 'nullable|string|max:15', // Nếu ông có cột phone
            // 'address' => 'nullable|string|max:255', // Nếu ông có cột address
        ]);

        // Cập nhật
        $user->update([
            'name' => $request->name,
            // 'phone' => $request->phone,
            // 'address' => $request->address,
        ]);

        return response()->json([
            'message' => 'Cập nhật thông tin thành công!',
            'user' => $user
        ]);
    }

    // 3. Đổi mật khẩu (Chức năng này user nào cũng cần)
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // confirmed nghĩa là phải có field new_password_confirmation gửi kèm
        ]);

        $user = $request->user();

        // Check mật khẩu cũ có đúng không
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng'], 400);
        }

        // Đổi mật khẩu
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Đổi mật khẩu thành công!']);
    }

    // 4. Lấy lịch sử đơn hàng của User đó
 

}
