<?php if (isset($_SESSION['error']) && $_SESSION['error']): ?>
    <div class="alert-error" style="background: #fee2e2; color: #b91c1c; padding: 12px 20px; border-radius: 12px; margin: 20px auto; max-width: 1200px;">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>
<div class="orders-page">
    <div class="orders-header">
        <h1><i class="fas fa-truck"></i> Đơn hàng của tôi</h1>
    </div>

    <!-- Tab lọc -->
    <div class="orders-tabs">
        <button class="tab-btn active" data-status="all">Tất cả</button>
        <button class="tab-btn" data-status="pending">Chờ xác nhận</button>
        <button class="tab-btn" data-status="confirmed">Đã xác nhận</button>
        <button class="tab-btn" data-status="shipping">Đang giao</button>
        <button class="tab-btn" data-status="renting">Đang thuê</button>
        <button class="tab-btn" data-status="returning">Đang hoàn trả</button>
        <button class="tab-btn" data-status="completed">Hoàn tất</button>
        <button class="tab-btn" data-status="cancelled">Đã hủy</button>
    </div>

    <!-- Container hiển thị danh sách đơn hàng -->
    <div id="orders-list-container">
        <div class="loading-spinner">Đang tải đơn hàng...</div>
    </div>
</div>