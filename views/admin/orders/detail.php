<?php 
$pageTitle = 'Chi tiết đơn hàng'; 
$activeMenu = 'orders';
// Controller truyền biến $order và $items
?>
<div class="order-detail-page">
    <div class="page-header">
        <h1>Chi tiết đơn hàng #<?php echo $order['id']; ?></h1>
        <a href="<?php echo BASE_URL; ?>/admin/orders" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
    <div class="card">
        <div class="card-header">
            <h3>Thông tin đơn hàng</h3>
        </div>
        <div class="card-body">
            <div class="info-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['user_name']); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></div>
                <div><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></div>
                <div><strong>Ngày thuê:</strong> <?php echo $order['rent_date']; ?></div>
                <div><strong>Ngày trả dự kiến:</strong> <?php echo $order['return_date']; ?></div>
                <div><strong>Ngày trả thực tế:</strong> <?php echo $order['actual_return_date'] ?? 'Chưa trả'; ?></div>
                <div><strong>Tổng tiền:</strong> <?php echo number_format($order['total_price'], 0, ',', '.'); ?>đ</div>
                <div><strong>Phí trễ:</strong> <?php echo number_format($order['late_fee'], 0, ',', '.'); ?>đ</div>
                <div><strong>Trạng thái:</strong> <span class="badge <?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3>Sản phẩm đã thuê</h3>
        </div>
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                        <td><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?>đ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>