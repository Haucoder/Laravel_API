<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    // public function run(): void
    // {
    //     User::create([
    //         'name' => 'Nguyen Van A',
    //         'email' => 'test@gmail.com',
    //         'password' => bcrypt('123456') // Mật khẩu là 123456
    //     ]);
     
    //     $catPhone = Category::create(['name' => 'Điện thoại']);      // ID = 1
    //     $catFurniture = Category::create(['name' => 'Nội thất']);    // ID = 2
    //     $catFashion = Category::create(['name' => 'Thời trang']);    // ID = 3

    //     // 2. Tạo 6 Sản phẩm đa dạng
        
    //     // --- NHÓM ĐIỆN THOẠI (ID: 1) ---
    //     Product::create([
    //         'name' => 'iPhone 15 Pro Max',
    //         'price' => 30000000, // 30 triệu (Đắt)
    //         'description' => 'Hàng cao cấp apple',
    //         'category_id' => $catPhone->id
    //     ]);

    //     Product::create([
    //         'name' => 'Samsung Galaxy S24',
    //         'price' => 25000000, // 25 triệu (Đắt)
    //         'description' => 'Doi thu cua iPhone',
    //         'category_id' => $catPhone->id
    //     ]);

    //     Product::create([
    //         'name' => 'Nokia 1280',
    //         'price' => 500000, // 500k (Rẻ)
    //         'description' => 'Dien thoai cuc gach',
    //         'category_id' => $catPhone->id
    //     ]);

    //     // --- NHÓM NỘI THẤT (ID: 2) ---
    //     Product::create([
    //         'name' => 'Ghế Gaming Zerus',
    //         'price' => 2000000, // 2 triệu (Vừa)
    //         'description' => 'Ngoi choi game dau lung',
    //         'category_id' => $catFurniture->id
    //     ]);

    //     Product::create([
    //         'name' => 'Bàn làm việc gỗ sồi',
    //         'price' => 1500000, // 1.5 triệu (Vừa)
    //         'description' => 'Ban dep',
    //         'category_id' => $catFurniture->id
    //     ]);

    //     // --- NHÓM THỜI TRANG (ID: 3) ---
    //     Product::create([
    //         'name' => 'Áo thun Gucci Fake',
    //         'price' => 150000, // 150k (Rất rẻ)
    //         'description' => 'Mac cho mat',
    //         'category_id' => $catFashion->id
    //     ]);
    //     // Trong DatabaseSeeder.php
    //             $user = User::first(); // Lấy ông user đầu tiên

    //             // Tạo 5 đơn hàng giả
    //             for ($i = 1; $i <= 5; $i++) {
    //                 $order = Order::create([
    //                     'user_id' => $user->id,
    //                     'total_price' => 0, // Tí tính sau
    //                     'status' => 'pending',
    //                     'shipping_address' => 'Địa chỉ test số ' . $i,
    //                     'phone' => '090909090' . $i,
    //                     'created_at' => now()->subDays($i) // Lùi ngày lại cho danh sách có thứ tự
    //                 ]);
                    
    //                 // Tạo item cho đơn này...
    //             }
      
        
    // }
     public function run() {
    // Tạo danh mục trước
    \App\Models\Category::updateOrCreate(['id' => 1789], ['name' => 'Điện thoại - Máy tính bảng']);
    \App\Models\Category::updateOrCreate(['id' => 1846], ['name' => 'Laptop - Máy vi tính']);
    \App\Models\Category::updateOrCreate(['id' => 1922], ['name' => 'Nội thất công nghệ']);

    // Sau đó mới gọi ProductSeeder
    $this->call([
        ProductSeeder::class,
    ]);
    }
    
}
