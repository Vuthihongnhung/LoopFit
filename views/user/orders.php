<?php
// views/user/orders.php
?>
<div class="orders-container">
    <h1>📋 Đơn hàng của tôi</h1>
    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <i class="bi bi-box-seam"></i>
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="/LoopFit/public/products" class="btn-shop-now">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): 
                // Xác định trạng thái hiển thị
                $statusText = '';
                $statusClass = '';
                $statusIcon = '';
                $currentDate = date('Y-m-d');
                
                switch ($order['status']) {
                    case 'pending':
                        $statusText = 'Chờ xác nhận';
                        $statusClass = 'status-pending';
                        $statusIcon = 'bi-clock-history';
                        break;
                    case 'processing':
                        // Kiểm tra nếu quá hạn và chưa trả
                        if ($order['return_date'] < $currentDate && !$order['actual_return_date']) {
                            $statusText = 'Quá hạn trả';
                            $statusClass = 'status-overdue';
                            $statusIcon = 'bi-exclamation-triangle';
                        } else {
                            $statusText = 'Đang thuê';
                            $statusClass = 'status-renting';
                            $statusIcon = 'bi-truck';
                        }
                        break;
                    case 'shipping': // nếu có trạng thái đang giao
                        $statusText = 'Đang giao hàng';
                        $statusClass = 'status-shipping';
                        $statusIcon = 'bi-box-seam';
                        break;
                    case 'completed':
                        $statusText = 'Đã hoàn thành';
                        $statusClass = 'status-completed';
                        $statusIcon = 'bi-check-circle';
                        break;
                    case 'cancelled':
                        $statusText = 'Đã hủy';
                        $statusClass = 'status-cancelled';
                        $statusIcon = 'bi-x-circle';
                        break;
                    default:
                        $statusText = ucfirst($order['status']);
                        $statusClass = '';
                        $statusIcon = 'bi-question-circle';
                }
                
                // Tính tiền cọc (30% tổng tiền)
                $deposit = $order['total_price'] * 0.3;
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <span class="order-id">Mã đơn: #<?= $order['id'] ?></span>
                            <span class="order-date"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="order-status-badge <?= $statusClass ?>">
                            <i class="bi <?= $statusIcon ?>"></i>
                            <?= $statusText ?>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-dates">
                            <div class="date-item">
                                <span class="label">📅 Ngày thuê:</span>
                                <span class="value"><?= date('d/m/Y', strtotime($order['rent_date'])) ?></span>
                            </div>
                            <div class="date-item">
                                <span class="label">⏰ Ngày trả dự kiến:</span>
                                <span class="value"><?= date('d/m/Y', strtotime($order['return_date'])) ?></span>
                            </div>
                            <?php if ($order['actual_return_date']): ?>
                                <div class="date-item">
                                    <span class="label">✅ Ngày trả thực tế:</span>
                                    <span class="value"><?= date('d/m/Y', strtotime($order['actual_return_date'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($order['items'])): ?>
                            <div class="order-items">
                                <div class="items-title">Sản phẩm đã thuê:</div>
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item-product">
                                        <img src="/LoopFit/public/uploads/products/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <div class="product-detail">
                                            <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="product-meta">Số lượng: <?= $item['quantity'] ?></div>
                                        </div>
                                        <div class="product-price"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Tổng tiền:</span>
                                <strong><?= number_format($order['total_price'], 0, ',', '.') ?>đ</strong>
                            </div>
                            <div class="summary-row">
                                <span>Tiền cọc (30%):</span>
                                <span><?= number_format($deposit, 0, ',', '.') ?>đ</span>
                            </div>
                            <?php if ($order['late_fee'] > 0): ?>
                                <div class="summary-row late-fee">
                                    <span>Phí trễ hạn:</span>
                                    <span class="text-danger">+ <?= number_format($order['late_fee'], 0, ',', '.') ?>đ</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-footer">
                        <a href="/LoopFit/public/order/<?= $order['id'] ?>" class="btn-detail">Xem chi tiết</a>
                        <?php if ($order['status'] == 'pending'): ?>
                            <button class="btn-cancel-order" data-id="<?= $order['id'] ?>">Hủy đơn</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>