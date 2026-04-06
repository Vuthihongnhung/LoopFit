<?php $pageTitle = 'Dashboard'; $activeMenu = 'dashboard'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin/css/admin.css">
<div class="dashboard">
    <div class="page-header">
        <h1>Tổng quan</h1>
        <p>Chào mừng trở lại, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>!</p>
    </div>
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <h3>Sản phẩm</h3>
                <p class="stat-number" id="statProducts">0</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <h3>Đơn hàng</h3>
                <p class="stat-number" id="statOrders">0</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3>Người dùng</h3>
                <p class="stat-number" id="statUsers">0</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3>Doanh thu</h3>
                <p class="stat-number" id="statRevenue">0đ</p>
            </div>
        </div>
    </div>
    <div class="recent-section">
        <div class="card">
            <div class="card-header">
                <h3>Đơn hàng gần đây</h3>
                <a href="<?php echo BASE_URL; ?>/admin/orders" class="btn-link">Xem tất cả</a>
            </div>
            <div class="card-body" id="recentOrders">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th></tr>
                    </thead>
                    <tbody><tr><td colspan="4">Đang tải...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    fetch('<?php echo BASE_URL; ?>/admin/api/stats')
        .then(res => res.json())
        .then(data => {
            document.getElementById('statProducts').innerText = data.total_products;
            document.getElementById('statOrders').innerText = data.total_orders;
            document.getElementById('statUsers').innerText = data.total_users;
            document.getElementById('statRevenue').innerText = new Intl.NumberFormat('vi-VN').format(data.revenue) + 'đ';
        });
    fetch('<?php echo BASE_URL; ?>/admin/api/orders?limit=5')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if(data.data && data.data.length) {
                data.data.forEach(order => {
                    html += `<tr>
                        <td>#${order.id}</td>
                        <td>${order.user_name || 'N/A'}</td>
                        <td>${new Intl.NumberFormat('vi-VN').format(order.total_price)}đ</td>
                        <td><span class="badge ${order.status}">${order.status}</span></td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="4">Chưa có đơn hàng</td></tr>';
            }
            document.querySelector('#recentOrders table tbody').innerHTML = html;
        });
</script>