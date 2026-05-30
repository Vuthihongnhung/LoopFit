<?php 
$pageTitle = 'Quản lý thanh toán'; 
$activeMenu = 'payments';
?>
<script>const BASE_URL = '<?= BASE_URL ?>';</script>

<div class="payments-page">
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Quản lý thanh toán</h1>
        <div class="search-filters">
            <select id="methodFilter" class="form-control" style="width: 180px;">
                <option value="all">Tất cả phương thức</option>
                <option value="cash">Tiền mặt (COD)</option>
                <option value="transfer">Chuyển khoản</option>
            </select>
            <select id="statusFilter" class="form-control" style="width: 180px;">
                <option value="all">Tất cả trạng thái</option>
                <option value="unpaid">Chưa thanh toán</option>
                <option value="pending_verification">Chờ xác nhận</option>
                <option value="paid">Đã thanh toán</option>
            </select>
            <button id="refreshBtn" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i> Làm mới
            </button>
        </div>
    </div>

    <!-- Thống kê nhanh -->
    <div class="stats-grid" id="paymentStats">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3>Chờ xác nhận</h3>
                <p class="stat-number" id="statPendingVerification">0</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3>Đã thanh toán</h3>
                <p class="stat-number" id="statPaid">0</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-hourglass"></i></div>
            <div class="stat-info">
                <h3>Chưa thanh toán</h3>
                <p class="stat-number" id="statUnpaid">0</p>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách đơn hàng</h3>
            <span class="badge active" id="orderCount">0 đơn</span>
        </div>
        <div class="card-body" style="padding: 20px; overflow-x: auto;">
            <table class="data-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Tổng tiền</th>
                        <th>Phương thức</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <tr><td colspan="8" style="text-align: center;">Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/admin/js/payment.js"></script>