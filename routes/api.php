<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;

// 1. Route công khai (Ai cũng vào được)

Route::get('/nap-du-lieu-bi-mat', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    return response()->json(['message' => 'Đã nạp dữ liệu thành công! Hàng về rồi sếp ơi!']);
});

Route::get('/tang-toc-website', function () {
    // 1. Cache các file cấu hình (Config)
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    
    // 2. Cache các đường dẫn (Routes) - Giúp tìm route nhanh hơn
    \Illuminate\Support\Facades\Artisan::call('route:cache');
    
    // 3. Cache file giao diện (View)
    \Illuminate\Support\Facades\Artisan::call('view:cache');

    return response()->json(['message' => 'Đã bật chế độ Turbo! Web chạy nhanh hơn rồi nhé! 🚀']);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']); // Xem danh sách thì cho xem thoải mái
Route::get('/products/{id}', [ProductController::class, 'show']);



//Route::get('/categories',[CategoryController::class,'index']);
 Route::get('/vnpay/return', [PaymentController::class, 'vnpayReturn']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
// 2. Route BẢO MẬT (Phải có Token mới vào được)
// Ta gom nhóm lại và dùng middleware 'auth:sanctum'
Route::middleware('auth:sanctum')->group(function () {
    
    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout']);
    //mua hang
    Route::post('/orders',[OrderController::class,'store']);
    //lich su don hang
    Route::get('/orders', [OrderController::class, 'index']);
    // Dùng cancel don hang
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    //
    Route::get("/cart",[CartController::class,'index']);
    Route::post("/cart",[CartController::class,'store']);
    Route::put("/cart/{id}",[CartController::class,'updateCart']);
    Route::delete("/cart/{id}",[CartController::class,'deleteCart']);

    //Wishlist
    Route::post('/wishlist/toggle',[ProductController::class,'toggleWishlist']);
    Route::get('/wishlist',[ProductController::class,'getWishlist']);
    //comment
    Route::post('/comments', [ProductController::class, 'StoreComment']);

   //view 
   Route::get('products/trending', [ProductController::class, 'getTrendingProducts']);


    Route::post('/checkout',[CartController::class,"checkOut"]);
    // Route tạo link thanh toán
    Route::post('/payment/vnpay', [PaymentController::class, 'createPayment']);
    // Route nhận kết quả trả về từ VNPAY
   
    // Lấy thông tin bản thân
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    
});
 
Route::middleware(['auth:sanctum','admin'])->group( function(){
    // Chỉ người đăng nhập mới được Thêm/Sửa/Xóa
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    //category
    Route::apiResource('categories',CategoryController::class);
    //DashboardController
    

    Route::get('/admin/orders', [OrderController::class, 'indexAdmin']);
    //
   Route::post('/admin/orders/cleanup', [OrderController::class, 'cleanupExpiredOrders']);
//thay doi trang thai
  Route::put('/admin/orders/{id}/status',[OrderController::class,'UpdateStatus']);
});
   Route::get('/dashboard', [DashboardController::class, 'index']);
