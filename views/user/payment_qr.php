<div class="container py-4">
    <h2>Thanh toán chuyển khoản</h2>
    <p><strong>Mã đơn hàng:</strong> #<?= (int)$order_id ?></p>
    <p><strong>Khách hàng:</strong> <?= htmlspecialchars($customer_name) ?></p>
    <p><strong>Số tiền cần thanh toán:</strong> <?= number_format((float)$amount) ?>đ</p>

    <div style="margin: 20px 0; padding: 20px; border: 1px dashed #ccc; display: inline-block; text-align: center;">
        <p>QR thanh toán cho đơn #<?= (int)$order_id ?></p>
        <img alt="QR thanh toán" style="max-width: 260px; width: 100%;" src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=ORDER-<?= (int)$order_id ?>-AMOUNT-<?= urlencode((string)$amount) ?>">
    </div>

    <form method="post" action="<?= BASE_URL ?>/api/payment/confirm">
        <input type="hidden" name="order_id" value="<?= (int)$order_id ?>">
        <button type="submit">Tôi đã chuyển khoản</button>
    </form>
</div>
