<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
//Route::redirect('/', '/home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/{any}', function () {

    return view('home');
})->where('any', '.*');

Route::get('/test-email', function () {
    try {
        Mail::raw('Đây là email test hệ thống!', function ($message) {
            $message->to('test@example.com')
                    ->subject('Test Mailtrap');
        });
        return 'Gửi mail thành công! Cấu hình ngon lành.';
    } catch (\Exception $e) {
        return 'Lỗi rồi: ' . $e->getMessage();
    }
});
// routes/web.php

// ... các route cũ giữ nguyên

// Thêm đoạn này:
Route::get('/nap-du-lieu-bi-mat', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]); // Xóa sạch làm lại bảng (cho chắc)
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]); // Nạp dữ liệu mẫu
    return 'Đã nạp dữ liệu thành công! Vào trang chủ xem hàng đi sếp!';
});


Route::get('/cuu-ho-render', function () {
    try {
        // 1. Xóa sạch mọi cache cũ (QUAN TRỌNG NHẤT để nhận biến ENV mới)
        Artisan::call('optimize:clear');
        
        // 2. Chạy migrate (Tạo bảng thiếu, KHÔNG XÓA dữ liệu cũ)
        Artisan::call('migrate', ['--force' => true]);
        
        // 3. (Tùy chọn) Chạy Seed nếu bảng rỗng
        // Artisan::call('db:seed', ['--force' => true]); 

        // 4. Cache lại để web chạy nhanh
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        return response()->json([
            'status' => true,
            'message' => '✅ Đã cứu hộ thành công!',
            'details' => [
                'migrate' => 'Đã cập nhật bảng database',
                'cache' => 'Đã xóa cache cũ và nạp cache mới',
                'env' => 'Đã nhận cấu hình VNPAY & Mail mới'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => '❌ Lỗi rồi sếp ơi: ' . $e->getMessage()
        ]);
    }
});
