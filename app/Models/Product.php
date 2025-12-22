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
    if (!$this->image) {
        // Trả về ảnh mặc định nếu không có dữ liệu
        return "https://via.placeholder.com/300x300.png?text=No+Image";
    }

    // Nếu link bắt đầu bằng http hoặc https (link từ Tiki cào về)
    if (str_starts_with($this->image, 'http')) {
        return $this->image;
    }

    // Nếu là ảnh bạn tự upload lưu trong storage/app/public
    return asset('storage/' . $this->image);
}
}
