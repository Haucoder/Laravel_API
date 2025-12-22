<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run() {
    \App\Models\Category::insert([
        ['id' => 1789, 'name' => 'Điện thoại - Máy tính bảng'],
        ['id' => 1846, 'name' => 'Laptop - Máy vi tính'],
        ['id' => 1922, 'name' => 'Nội thất công nghệ'],

        
    ]);
}
}
