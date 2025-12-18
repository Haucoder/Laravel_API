<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;

// 1. Route công khai (Ai cũng vào được)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']); // Xem danh sách thì cho xem thoải mái
Route::get('/products/{id}', [ProductController::class, 'show']);

    

Route::get('/categories',[CategoryController::class,'index']);


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
//thay doi trang thai
  Route::put('orders/{id}/status',[OrderController::class,'UpdateStatus']);
});