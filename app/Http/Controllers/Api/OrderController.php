<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB; // Dùng để quản lý giao dịch (Transaction)
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlaced;
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            //'user_id' => 'required|exists:users,id', // Tạm thời nhập tay user_id để test
            'shipping_address' => 'required|string',
            'phone' => 'required|string',
            'items' => 'required|array', // Bắt buộc phải là một danh sách món hàng
            'items.*.product_id' => 'required|exists:products,id', // Từng món phải có ID sản phẩm chuẩn
            'items.*.quantity' => 'required|integer|min:1', // Số lượng phải >= 1
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Bắt đầu Giao dịch (Transaction)
        // Ý nghĩa: Nếu có lỗi xảy ra ở bất kỳ bước nào bên dưới -> Hủy hết, coi như chưa từng đặt hàng.
        // Tránh trường hợp: Tạo được đơn hàng nhưng không tạo được món hàng chi tiết.
        DB::beginTransaction();

        try {
            // Bước 2.1: Tính tổng tiền (Server tự tính, không tin giá từ Frontend gửi lên)
            $totalPrice = 0;
            // Lấy danh sách sản phẩm khách chọn để tính toán
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $totalPrice += $product->price * $item['quantity'];
            }

            // Bước 2.2: Tạo Đơn hàng (Order)
            $order = Order::create([
                'user_id' => $request->user()->id,
                'shipping_address' => $request->shipping_address,
                'phone' => $request->phone,
                'total_price' => $totalPrice,
                'status' => 'pending' // Mặc định là đang chờ xử lý
            ]);

            // Bước 2.3: Tạo Chi tiết đơn hàng (Order Items)
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if($product){
                   if($product->stock){
                     $product->decrement('stock',$item['quantity']);
                   }    else{
                        return response()->json(['message' => "Sản phẩm {$product->name} không đủ hàng!"], 400);
                   }
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price // Lưu lại giá tại thời điểm mua
                ]);
            }

            // Bước 2.4: Lưu mọi thứ vào Database
            DB::commit();

           // Mail::to($request->user()->email)->send(new OrderPlaced($order));
            try {
                Mail::to($request->user()->email)->send(new OrderPlaced($order));
            } catch (\Exception $e) {
                // Nếu lỗi mail thì chỉ log lỗi lại thôi, KHÔNG làm chết app
                \Log::error("Lỗi gửi mail đơn hàng " . $order->id . ": " . $e->getMessage());
            }
            $request->user()->cartItems()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Đặt hàng thành công!',
                'data' => $order->load('orderItems') // Trả về đơn hàng kèm chi tiết vừa tạo
            ], 201);

        } catch (\Exception $e) {
            // Nếu có lỗi -> Quay ngược thời gian, không lưu gì cả
            DB::rollBack();
            return response()->json(['message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
    // API Xem lịch sử đơn hàng
public function index(Request $request)
{
    // 1. Lấy User đang đăng nhập từ Token
    $user = $request->user();

    // 2. Chỉ lấy những đơn hàng CỦA USER ĐÓ (where user_id = ...)
    // with('orderItems.product'): Load luôn chi tiết món hàng + Tên sản phẩm
    $orders = Order::with('orderItems.product') 
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc') // Đơn mới nhất lên đầu
                ->paginate(5);

    return response()->json([
        'status' => true,
        'message' => 'Danh sách đơn hàng của bạn',
        'data' => $orders
    ]);
}
public function indexAdmin(Request $request)
{
    // Khởi tạo query
    // with('user'): Load thêm thông tin người mua (tên, email...)
    // with('orderItems.product'): Load chi tiết sản phẩm trong đơn
    $query = Order::with(['user', 'orderItems.product']); 

    // --- TÍNH NĂNG MỞ RỘNG: LỌC TRẠNG THÁI ---
    // Giúp Admin lọc nhanh: /api/admin/orders?status=pending
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Sắp xếp đơn mới nhất lên đầu
    $orders = $query->orderBy('created_at', 'desc')
                    ->paginate(10); // Admin màn hình to, load 10-20 đơn là đẹp

    return response()->json([
        'status' => true,
        'message' => 'Danh sách toàn bộ đơn hàng',
        'data' => $orders
    ]);
}
public function cancel(Request $request, $id)
{
    // 1. Tìm đơn hàng (Phải đúng ID và đúng là CỦA MÌNH mới tìm ra)
    $user = $request->user();
    $order = Order::where('user_id', $user->id)
                  ->where('id', $id)
                  ->first();

    // 2. Nếu không tìm thấy (hoặc đơn của người khác)
    if (!$order) {
        return response()->json([
            'status' => false,
            'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập'
        ], 404);
    }

    // 3. Kiểm tra điều kiện: Chỉ được hủy khi đang 'pending'
    if ($order->status !== 'pending') {
        
        return response()->json([
            'status' => false,
            'message' => 'Đơn hàng này đang được giao hoặc đã hoàn thành, không thể hủy!'
        ], 400);
    }
    foreach($order->orderItems as $item){
        Product::where('id',$item->product_id)->increment('stock',$item->quantity);
       
    }

    // 4. Nếu ok hết thì đổi trạng thái
    $order->status = 'canceled'; // Đổi sang trạng thái 'Đã hủy'
    $order->save();

    return response()->json([
        'status' => true,
        'message' => 'Đã hủy đơn hàng thành công và trả lại kho!',
        'data' => $order
    ]);
}

    public function UpdateStatus(Request $request,$id){
        $validator=Validator::make($request->all(),[
            'status'=>'required|in:pending,shipping,completed,canceled'
        ]);

        if($validator->fails()){
            return response()->json([
                'Errors'=> $validator->errors()
            ],422);
        }
        $order=Order::find($id);
        if(!$order){
            return response()->json([
                'status'=>false,
                'message'=>'khong tim thay don hang'
            ],404);
        }
        if($order->status=='canceled'|| $order->status=='completed'){
            return response()->json([
                'status'=>false,
                'message'=>"khong the thay doi trang thai"
            ],400);
        }
        $order->status=$request->status;
        $order->save();
        return response()->json([
            'status'=>true,
            "message"=>"thay doi thanh cong",
            "date"=>$order
        ]);
    }
    

    public function cleanupExpiredOrders() {
        $expiredOrders = Order::where('status', 'pending')
                            ->where('created_at', '<=', now()->subMinutes(30))
                            ->with('OrderItems')
                            ->get();

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                foreach ($order->OrderItems as $item) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
                $order->update(['status' => 'canceled']);
            });
        }

        return response()->json(['message' => 'Đã dọn dẹp ' . $expiredOrders->count() . ' đơn hàng quá hạn.']);
    }
    public function show($id){
        $order=Order::with('orderItems.product')->findOrFail($id);
        if(!$order){
            return response()->json([
                'status'=>false,
                'message'=>'khong tim thay don hang'
            ],404);
        }
        return response()->json([
            'status'=>true,
            'message'=>'chi tiet don hang',
            'data'=>$order
        ]);
    }
}
