<?php
$address = $order['address'] ?? [];
$paymentMethods = [
    'transfer' => 'Chuyển khoản ngân hàng',
    'cash'     => 'Thanh toán khi nhận hàng'
];
$paymentMethod = $order['payment_method'] ?? 'transfer';
$paymentMethodText = $paymentMethods[$paymentMethod] ?? ucfirst(str_replace('_', ' ', $paymentMethod));
?>
<div class="order-detail-container">
    <h1 class="order-detail-title">Chi tiết đơn hàng #<?= htmlspecialchars($order['id']) ?></h1>

    <div class="order-info-row">
        <div class="info-section">
            <h3>Thông tin nhận hàng</h3>
            <p><strong>Người nhận:</strong> <?= htmlspecialchars($address['full_name'] ?? 'Chưa có') ?></p>
            <p><strong>SĐT:</strong> <?= htmlspecialchars($address['phone'] ?? 'Chưa có') ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($address['address_line'] ?? 'Chưa có') ?></p>
        </div>
        <div class="info-section">
            <h3>Thông tin đơn hàng</h3>
            <p><strong>Ngày đặt:</strong> <?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
            <p><strong>Ngày thuê:</strong> <?= date('d/m/Y', strtotime($order['rent_date'])) ?></p>
            <p><strong>Ngày trả dự kiến:</strong> <?= date('d/m/Y', strtotime($order['return_date'])) ?></p>
            <?php if (!empty($order['actual_return_date'])): ?>
                <p><strong>Ngày trả thực tế:</strong> <?= date('d/m/Y', strtotime($order['actual_return_date'])) ?></p>
            <?php endif; ?>
            <p><strong>Số ngày thuê:</strong> <?= (int)($order['days'] ?? 1) ?> ngày</p>
            <p><strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($paymentMethodText) ?></p>
            <?php if (!empty($order['notes'])): ?>
                <p><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($order['notes'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Danh sách sản phẩm đã thuê -->
    <div class="info-section">
        <h3>Sản phẩm đã thuê</h3>
        <?php if (!empty($order['items'])): ?>
            <div class="product-list">
                <?php foreach ($order['items'] as $item): 
                    $productName = htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Sản phẩm');
                    $size = htmlspecialchars($item['size'] ?? 'M');
                    $color = htmlspecialchars($item['color'] ?? 'Trắng');
                    $quantity = (int)($item['quantity']);
                    $pricePerDay = (float)($item['price'] ?? 0);
                    $days = $order['days'] ?? 1;
                    $totalPrice = (float)($item['subtotal'] ?? ($pricePerDay * $quantity * $days));
                    
                    // Xử lý ảnh
                    $imageRaw = $item['image'] ?? '';
                    $firstImage = trim(explode(',', $imageRaw)[0] ?? '');
                    if (empty($firstImage)) $firstImage = 'default-product.png';
                    $imgUrl = BASE_URL . '/uploads/' . $firstImage;
                ?>
                    <div class="product-item">
                        <div class="product-img-wrapper">
                            <img src="<?= $imgUrl ?>" alt="<?= $productName ?>" class="product-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/user/images/default-product.png'">
                        </div>
                        <div class="product-info">
                            <div class="product-name"><?= $productName ?></div>
                            <div class="product-meta">Size: <?= $size ?> | Màu: <?= $color ?></div>
                            <div class="product-price-detail"><?= number_format($pricePerDay) ?>đ/ngày</div>
                        </div>
                        <div class="product-quantity">x <?= $quantity ?></div>
                        <div class="product-total"><?= number_format($totalPrice) ?>đ</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Không có sản phẩm nào trong đơn hàng này.</p>
        <?php endif; ?>
    </div>

    <!-- Tổng kết -->
    <div class="total-summary">
        <div class="total-row">Tổng tiền thuê: <?= number_format($order['total_price'] ?? 0) ?>đ</div>
        <div class="total-row">Tiền cọc: <?= number_format($order['deposit_amount'] ?? 0) ?>đ</div>
        <?php if (isset($order['shipping_fee']) && $order['shipping_fee'] > 0): ?>
            <div class="total-row">Phí vận chuyển: <?= number_format($order['shipping_fee']) ?>đ</div>
        <?php else: ?>
            <div class="total-row" style="color: green;">Phí vận chuyển: Miễn phí</div>
        <?php endif; ?>
        <?php if (!empty($order['discount_price']) && $order['discount_price'] > 0): ?>
            <div class="total-row" style="color: green;">Giảm giá: -<?= number_format($order['discount_price']) ?>đ</div>
        <?php endif; ?>
        <div class="total-row grand-total">Tổng thanh toán: <?= number_format($order['final_total'] ?? 0) ?>đ</div>
    </div>

    <!-- ===== TRẠNG THÁI THANH TOÁN (chuyển khoản) ===== -->
    <?php
    $paymentStatus = $order['payment_status'] ?? 'unpaid';
    $paymentMethod = $order['payment_method'] ?? 'cash';
    $paymentVerifiedAt = $order['payment_verified_at'] ?? null;
    $paymentExpiry = $order['payment_expiry'] ?? null;
    $isTransfer = ($paymentMethod === 'transfer');
    ?>
    <?php if ($isTransfer): ?>
    <div class="payment-status-card">
        <h4>TRẠNG THÁI THANH TOÁN</h4>
        <?php if ($paymentStatus === 'paid'): ?>
            <?php
            $verifiedTimestamp = is_string($paymentVerifiedAt) ? strtotime($paymentVerifiedAt) : 0;
            $showTime = ($paymentVerifiedAt && $verifiedTimestamp > 0);
            ?>
            <p class="status-success">
                <i class="fa-solid fa-circle-check"></i> 
                Đã thanh toán thành công 
                <?php if ($showTime): ?>
                    vào lúc: <?= date('d/m/Y H:i', $verifiedTimestamp) ?>
                <?php else: ?>
                    (đã xác nhận)
                <?php endif; ?>
            </p>
        <?php elseif ($paymentStatus === 'pending_verification'): ?>
            <p class="status-pending">
                <i class="fa-solid fa-clock"></i>
                Đã gửi yêu cầu thanh toán, đang chờ xác nhận.
            </p>
        <?php elseif ($paymentStatus === 'pending'): ?>
            <p class="status-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Chưa thanh toán. Vui lòng chuyển khoản theo thông tin bên dưới.
            </p>
        <?php if ($paymentExpiry && strtotime($paymentExpiry) > 0): ?>
            <p class="status-expiry">
                <i class="fa-regular fa-calendar-clock"></i>
                Hạn thanh toán: <?= date('d/m/Y H:i', strtotime($paymentExpiry)) ?>
            </p>
        <?php endif; ?>

        <?php elseif ($paymentStatus === 'failed'): ?>
            <p class="status-failed">
                <i class="fa-solid fa-circle-xmark"></i>
                Thanh toán thất bại hoặc quá hạn. Vui lòng liên hệ hỗ trợ.
            </p>
        <?php else: ?>
            <p class="status-muted">Chưa có thông tin thanh toán.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Trạng thái hoàn cọc -->
    <?php
    $depositStatus = $order['deposit_status'] ?? 'pending';
    $depositRefundedAmount = (float)($order['deposit_refunded_amount'] ?? 0);
    $depositRefundedAt = $order['deposit_refunded_at'] ?? null;
    $depositAmount = (float)($order['deposit_amount'] ?? 0);
    ?>
    <div class="deposit-refund-section">
        <h4>TÌNH TRẠNG HOÀN CỌC</h4>
        <?php if ($depositStatus === 'refunded' || $depositStatus === 'refunded_full'): ?>
            <?php
            $refundTimestamp = is_string($depositRefundedAt) ? strtotime($depositRefundedAt) : 0;
            $showTime = ($depositRefundedAt && $refundTimestamp > 0);
            ?>
            <p class="text-success">
                <i class="fa-solid fa-circle-check"></i>
                Đã hoàn cọc đầy đủ 
                <?php if ($showTime): ?>
                    vào lúc: <?= date('d/m/Y H:i', $refundTimestamp) ?>
                <?php else: ?>
                    (đã xác nhận)
                <?php endif; ?>
            </p>
            <p>Số tiền đã hoàn: <strong><?= number_format($depositRefundedAmount ?: $depositAmount) ?>đ</strong></p>
        <?php elseif ($depositStatus === 'refunded_partial'): ?>
            <?php
            $refundTimestamp = is_string($depositRefundedAt) ? strtotime($depositRefundedAt) : 0;
            $showTime = ($depositRefundedAt && $refundTimestamp > 0);
            ?>
            <p class="text-warning">
                <i class="fa-solid fa-circle-exclamation"></i>
                Đã hoàn một phần cọc (<?= number_format($depositRefundedAmount) ?>đ / <?= number_format($depositAmount) ?>đ)
                <?php if ($showTime): ?>
                    vào lúc: <?= date('d/m/Y H:i', $refundTimestamp) ?>
                <?php endif; ?>
            </p>
        <?php elseif ($depositStatus === 'forfeited'): ?>
            <p class="text-danger">
                <i class="fa-solid fa-ban"></i>
                Tiền cọc đã bị giữ lại (do vi phạm hợp đồng thuê).
            </p>
        <?php elseif ($depositStatus === 'pending_return'): ?>
            <p class="text-info">
                <i class="fa-solid fa-rotate"></i>
                Đang chờ xử lý hoàn cọc sau khi bạn trả hàng.
            </p>
        <?php elseif ($depositStatus === 'pending'): ?>
            <p class="text-warning">
                <i class="fa-regular fa-hourglass-half"></i>
                Chưa hoàn cọc. Đơn hàng đang trong quá trình xử lý.
            </p>
        <?php else: ?>
            <p class="text-muted">Không áp dụng hoàn cọc.</p>
        <?php endif; ?>
    </div>

    <!-- PHÍ PHÁT SINH (phí trễ) -->
    <?php
    $lateFee = (float)($order['late_fee'] ?? 0);
    $hasLateFee = $lateFee > 0;
    $lateDays = $order['late_days'] ?? 0;
    $lateFeeStatus = $order['late_fee_status'] ?? 'unpaid';
    $actualReturnDate = isset($order['actual_return_date']) && !empty($order['actual_return_date']) ? $order['actual_return_date'] : null;
    $returnDate = $order['return_date'] ?? null;
    ?>
    <?php if ($hasLateFee): ?>
    <div class="extra-fee-section">
        <h4><i class="fas fa-exclamation-triangle"></i> PHÍ PHÁT SINH</h4>
        <div class="fee-row">
            <span>Phí trễ:</span>
            <span class="fee-amount"><?= number_format($lateFee) ?>đ</span>
        </div>
        <?php if ($lateDays > 0 && $returnDate && $actualReturnDate): ?>
        <div class="fee-reason">
            <h3>Lý do: Trả hàng trễ <strong><?= $lateDays ?> ngày</strong> so với ngày dự kiến (<?= date('d/m/Y', strtotime($returnDate)) ?> → <?= date('d/m/Y', strtotime($actualReturnDate)) ?>).</h3>
        </div>
        <?php elseif ($lateDays > 0): ?>
        <div class="fee-reason">
            <small>Lý do: Trả hàng trễ <strong><?= $lateDays ?> ngày</strong>.</small>
        </div>
        <?php endif; ?>
        <div class="fee-row total">
            <span>Tổng phí phát sinh:</span>
            <span class="fee-amount total"><?= number_format($lateFee) ?>đ</span>
        </div>
        <div class="fee-payment-status">
            Trạng thái thanh toán: 
            <?php if ($lateFeeStatus == 'paid'): ?>
                <span class="paid">
                    <i class="fa-solid fa-circle-check"></i>
                    <b>Đã thanh toán</b>
                </span>
            <?php elseif ($lateFeeStatus == 'pending_verification'): ?>
                <span class="pending">
                    <i class="fa-regular fa-hourglass-half"></i>
                    <b>Chờ xác nhận</b>
                </span>
            <?php else: ?>
                <span class="unpaid">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <b>Chưa thanh toán</b>
                </span>
                <button id="payLateFeeBtn" class="btn-pay-latefee" data-order-id="<?= $order['id'] ?>">Thanh toán phí trễ</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/orders" class="back-link">&larr; Quay lại danh sách đơn hàng</a>
</div>

<script>
// Hàm an toàn gọi fetch
async function safeFetchJSON(url, options) {
    try {
        const response = await fetch(url, options);
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            throw new Error('Invalid JSON');
        }
    } catch(err) {
        Toast.error('Lỗi kết nối hoặc dữ liệu không hợp lệ');
        throw err;
    }
}

// Hàm hiển thị modal QR code thanh toán phí trễ (dùng VietQR)
function showQRModal(orderId, amount) {
    let modal = document.getElementById('qrLateFeeModal');
    if (!modal) {
        const modalHtml = `
            <div id="qrLateFeeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.75); z-index:10000; justify-content:center; align-items:center; backdrop-filter:blur(3px);">
                <div style="background:white; padding:24px; border-radius:24px; width:380px; text-align:center; box-shadow:0 20px 35px rgba(0,0,0,0.2); animation:fadeInUp 0.3s ease;">
                    <div style="display:flex; justify-content:flex-end;">
                        <span id="closeQrModal" style="cursor:pointer; font-size:24px; line-height:1;">&times;</span>
                    </div>
                    <h3 style="margin:0 0 8px 0; color:#1e293b; font-size:24px;">Thanh toán phí trễ</h3>
                    <p style="font-size:16px; margin:8px 0;">Số tiền: <strong style="color:#e67e22; font-size:20px;" id="qrAmount">0đ</strong></p>
                    <div id="qrCodeContainer" style="margin:20px auto; display:flex; justify-content:center; background:#fff; padding:10px; border-radius:16px; min-height:220px;"></div>
                    <p style="font-size:13px; color:#64748b; margin:12px 0 0;">Quét mã QR bằng ứng dụng ngân hàng để thanh toán</p>
                    <button id="confirmLateFeePayment" class="btn-pay-confirm" style="background:#10b981; color:white; border:none; padding:10px 20px; border-radius:40px; font-weight:600; margin-top:16px; width:100%; cursor:pointer;">Xác nhận đã thanh toán</button>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('qrLateFeeModal');
        const closeBtn = document.querySelector('#qrLateFeeModal #closeQrModal');
        if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

        const confirmBtn = document.getElementById('confirmLateFeePayment');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', async function() {
                const orderId = this.dataset.orderId;
                const data = await safeFetchJSON(BASE_URL + '/user/orders/payLateFee', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'order_id=' + orderId
                });
                if (data.success) {
                    if (typeof Toast !== 'undefined') Toast.success(data.message);
                    else alert(data.message);
                    modal.style.display = 'none';
                    location.reload();
                } else {
                    if (typeof Toast !== 'undefined') Toast.error(data.message || 'Thanh toán thất bại');
                    else alert(data.message);
                }
            });
        }
    }
    // Cập nhật số tiền
    document.getElementById('qrAmount').innerText = new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    const qrContainer = document.getElementById('qrCodeContainer');
    qrContainer.innerHTML = '';
    
    // Tạo QR Code theo VietQR 
    const bank_id = 'MB';          
    const account_no = '03011092006';  
    const content = `PHITRE ${orderId}`;
    const encoded_content = encodeURIComponent(content);
    const qrUrl = `https://img.vietqr.io/image/${bank_id}-${account_no}-compact.png?amount=${amount}&addInfo=${encoded_content}`;
    
    const img = document.createElement('img');
    img.src = qrUrl;
    img.alt = 'QR Code thanh toán';
    img.style.width = '220px';
    img.style.height = '220px';
    img.style.borderRadius = '12px';
    img.style.objectFit = 'contain';
    img.onerror = () => {
        if (typeof QRCode !== 'undefined') {
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: `LOOPFIT|${orderId}|${amount}`,
                width: 200,
                height: 200,
                correctLevel: QRCode.CorrectLevel.L
            });
        } else {
            qrContainer.innerHTML = '<p style="color:red;">Không thể tạo mã QR, vui lòng liên hệ CSKH</p>';
        }
    };
    qrContainer.appendChild(img);
    
    document.getElementById('confirmLateFeePayment').dataset.orderId = orderId;
    modal.style.display = 'flex';
}

// Xử lý nút thanh toán phí trễ
const payLateFeeBtn = document.getElementById('payLateFeeBtn');
if (payLateFeeBtn) {
    payLateFeeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const orderId = this.dataset.orderId;
        const amount = <?= $lateFee ?>;
        if (amount <= 0) {
            Toast.warning('Không có phí trễ cần thanh toán');
            return;
        }
        if (!confirm(`Bạn có chắc muốn thanh toán phí trễ ${new Intl.NumberFormat('vi-VN').format(amount)}đ?`)) return;
        showQRModal(orderId, amount);
    });
}

// Thông báo Toast nếu có phí trễ chưa thanh toán
<?php if ($hasLateFee && $lateFeeStatus == 'unpaid'): ?>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Toast !== 'undefined') {
        Toast.warning('📢 Đơn hàng #<?= $order['id'] ?> phát sinh phí trễ <?= number_format($lateFee) ?>đ. Vui lòng thanh toán để hoàn tất.');
    }
});
<?php endif; ?>
</script>