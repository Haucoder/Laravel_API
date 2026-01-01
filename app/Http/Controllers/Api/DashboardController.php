<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Tổng doanh thu (Chỉ tính đơn đã hoàn thành hoặc đã thanh toán)
        // Giả sử status 'completed' hoặc 'paid' là tính tiền
        $revenue = Order::whereIn('status', ['completed', 'paid'])->sum('total_price');

        // 2. Số đơn hàng mới (Pending)
        $pendingOrders = Order::where('status', 'pending')->count();

        // 3. Tổng số sản phẩm
        $totalProducts = Product::count();

        // 4. Tổng số khách hàng (trừ Admin ra)
        $totalUsers = User::where('role', '!=', 'admin')->count();

        return response()->json([
            'status' => true,
            'data' => [
                'revenue' => $revenue,
                'pending_orders' => $pendingOrders,
                'total_products' => $totalProducts,
                'total_users' => $totalUsers
            ]
        ]);
    }
}
