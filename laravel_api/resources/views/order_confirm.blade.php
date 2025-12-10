<!DOCTYPE html>
<html>
<body>
    <h2>Cảm ơn {{ $order->full_name }} đã đặt hàng!</h2>
    <p>Mã đơn hàng: <b>{{ $order->order_code }}</b></p>
    <p>Tổng tiền: {{ number_format($order->total_amount) }} VNĐ</p>

    <p>Bạn có thể theo dõi trạng thái đơn hàng tại đường dẫn sau:</p>
    <a href="{{ $trackingLink }}" style="background: #ee4d2d; color: white; padding: 10px 20px; text-decoration: none;">
        Xem đơn hàng của tôi
    </a>

    <p>Hoặc truy cập link: {{ $trackingLink }}</p>
</body>
</html>