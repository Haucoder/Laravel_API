<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order; // Nhớ use Model Order

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public $order; // Biến này sẽ chứa thông tin đơn hàng để hiển thị trong mail

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        // Trỏ tới file giao diện (view) mà tí nữa mình sẽ tạo
        return $this->subject('Xác nhận đơn hàng #' . $this->order->id)
                    ->view('emails.order_placed');
    }
}