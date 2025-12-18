<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Type\Integer;

class Order extends Model
{
    // ...
    protected $fillable = [
        'user_id', // <--- BẠN ĐANG THIẾU CÁI NÀY
        'total_price', 
        'status', 
        'shipping_address', 
        'phone'
    ];
    protected $casts = ['total_price'=>'integer'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}