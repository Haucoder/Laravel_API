<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $filePath = base_path("database/data/tiki_products.csv");
        
        if (!file_exists($filePath)) {
            $this->command->error("Không tìm thấy file CSV tại: $filePath");
            return;
        }

        $file = fopen($filePath, "r");
        $header = fgetcsv($file); // Đọc dòng đầu tiên (tiêu đề)

        $count = 0;
        while (($row = fgetcsv($file, 2000, ",")) !== FALSE) {
            // Mapping dữ liệu từ CSV vào mảng (Thứ tự: 0:name, 1:price, 2:description, 3:image, 4:category_id)
            Product::create([
                'name'        => $row[0],
                'price'       => (int)$row[1], // Ép kiểu sang số nguyên
                'description' => $row[2] ?? 'Đang cập nhật mô tả cho ' . $row[0],
                'image'       => $row[3],
                'category_id' => (int)$row[4],
            ]);
            $count++;
        }

        fclose($file);
        $this->command->info("Đã nạp thành công $count sản phẩm vào database!");
    }
}