<!DOCTYPE html>
<html>
<head>
    <title>Xác nhận đơn hàng</title>
</head>
<body>
    <h1>Cảm ơn bạn đã đặt hàng! 🎉</h1>
    <p>Xin chào, đơn hàng <b>#{{ $order->id }}</b> của bạn đã được ghi nhận.</p>
    
    <h3>Thông tin đơn hàng:</h3>
    <p>Tổng tiền: <b>{{ number_format($order->total_price) }} VNĐ</b></p>
    <p>Trạng thái: {{ $order->status }}</p>
    
    <p>Chúng tôi sẽ giao hàng sớm nhất có thể!</p>
    <br>
    <p>Trân trọng,<br>Shop Của Hậu</p>
</body>
</html>