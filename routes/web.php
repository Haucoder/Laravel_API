<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
Route::get('/', function () {
   return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



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