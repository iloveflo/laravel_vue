<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-w-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Cảm ơn {{ $order->full_name }} đã mua hàng!</h2>
        <p>Đơn hàng <strong>#{{ $order->order_code }}</strong> của bạn đã hoàn thành.</p>
        <p>Bạn có hài lòng với sản phẩm không? Hãy cho chúng tôi biết nhé.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/reviews/' . $order->order_code) }}" 
               style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;">
               Đánh giá sản phẩm ngay
            </a>
        </div>
        <p>Trân trọng,<br>Floentic</p>
    </div>
</body>
</html>