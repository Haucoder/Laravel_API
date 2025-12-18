<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model

{
    protected $fillable = ['name', 'price', 'description','image','category_id'];
    protected $casts=['price'=>'integer'];
    public function category(){
        return $this->belongsTo(Category::class);
    }
    protected $appends = ['image_url'];

    // 2. Định nghĩa cái 'image_url' đó lấy ở đâu ra
    public function getImageUrlAttribute()
    {
        // Nếu database có ảnh -> Nối thêm cái domain vào đằng trước
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Nếu database NULL -> Trả về ảnh mặc định (Placeholder)
        // Bạn có thể lấy link ảnh trên mạng dán vào đây
        return asset('public/storage/upload' .'/' .$this->image); 
    }
}
