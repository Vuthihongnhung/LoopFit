<?php
// Cấu hình ngân hàng 
$bank_id = 'MB';
$account_no = '03011092006';
$content = "LOOPFIT " . $order_id;
$encoded_content = urlencode($content);
$qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact.png?amount={$total_amount}&addInfo={$encoded_content}";

if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl = $protocol . '://' . $host . rtrim($scriptName, '/');
    define('BASE_URL', $baseUrl);
}
?>

    <div class="payment-qr-container">
        <h2 class="payment-title">Thanh toán đơn hàng</h2>
        
        <div class="qr-code">
            <img src="<?= htmlspecialchars($qr_url) ?>" alt="QR Code thanh toán VietQR">
        </div>

        <div class="info-box">
            <div class="info-item">
                <span class="info-label">Mã đơn hàng:</span>
                <strong><?= htmlspecialchars($order_id) ?></strong>
            </div>
            <div class="info-item">
                <span class="info-label">Nội dung chuyển khoản:</span>
                <strong><?= htmlspecialchars($content) ?></strong>
            </div>
            <div class="info-item">
                <span class="info-label">Tổng tiền cần chuyển:</span>
                <span class="amount"><?= number_format($total_amount, 0, ',', '.') ?> ₫</span>
            </div>
            <div class="info-item">
                <span class="info-label">Ngân hàng:</span>
                <strong><?= $bank_id ?> - <?= $account_no ?></strong>
            </div>
            <div class="info-item countdown-item">
                <span class="info-label">Thời gian còn lại:</span>
                <span id="countdown-timer" class="countdown">15:00</span>
            </div>
        </div>

        <button id="confirmPaymentBtn" class="btn-confirm">Tôi đã chuyển khoản</button>
        <div id="paymentMessage" class="message"></div>
    </div>

    <script>
        window.paymentData = {
            orderId: <?= json_encode($order_id) ?>,
            totalAmount: <?= json_encode($total_amount) ?>,
            expirySeconds: 15 * 60,
            baseUrl: <?= json_encode(BASE_URL) ?>
        };
    </script>
    <script src="<?= BASE_URL ?>/assets/user/js/payment.js"></script>
