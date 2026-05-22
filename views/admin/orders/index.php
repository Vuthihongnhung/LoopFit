<?php 
$pageTitle = 'Quản lý đơn hàng'; 
$activeMenu = 'orders';
?>
<div class="orders-page">
    <div class="page-header">
        <h1>Quản lý đơn hàng</h1>
        <div class="search-filters">
            <input type="text" id="searchOrder" placeholder="Tìm kiếm..." class="form-control" style="width: 280px;">
            <button id="resetFilters" class="btn btn-secondary">Reset</button>
        </div>
    </div>

    <!-- Filter Groups -->
    <div class="filter-groups">
        <div class="filter-group">
            <label class="filter-label">
                <svg class="filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Thanh toán
            </label>
            <div class="filter-chips" id="paymentFilter">
                <button class="chip active" data-value="all">Tất cả</button>
                <button class="chip" data-value="pending_verification">Chờ xác nhận</button>
                <button class="chip" data-value="paid">Đã thanh toán</button>
                <button class="chip" data-value="unpaid">Chưa thanh toán</button>
            </div>
        </div>

        <div class="filter-group">
            <label class="filter-label">
                <svg class="filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Đơn hàng
            </label>
            <div class="filter-chips" id="orderFilter">
                <button class="chip active" data-value="all">Tất cả</button>
                <button class="chip" data-value="pending">Chờ xác nhận</button>
                <button class="chip" data-value="confirmed">Đã xác nhận</button>
                <button class="chip" data-value="preparing">Đang chuẩn bị</button>
                <button class="chip" data-value="shipping">Đang giao</button>
                <button class="chip" data-value="renting">Đang thuê</button>
                <button class="chip" data-value="returning">Đang hoàn trả</button>
                <button class="chip" data-value="completed">Hoàn tất</button>
                <button class="chip" data-value="cancelled">Đã hủy</button>
            </div>
        </div>

        <div class="filter-group">
            <label class="filter-label">
                <svg class="filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tiền cọc
            </label>
            <div class="filter-chips" id="depositFilter">
                <button class="chip active" data-value="all">Tất cả</button>
                <button class="chip" data-value="pending">Chưa hoàn</button>
                <button class="chip" data-value="pending_return">Chờ hoàn cọc</button>
                <button class="chip" data-value="refunded_full">Đã hoàn toàn bộ</button>
                <button class="chip" data-value="refunded_partial">Hoàn một phần</button>
                <button class="chip" data-value="forfeited">Không hoàn</button>
            </div>
        </div>
    </div>

    <!-- Batch Actions -->
    <div class="batch-actions">
        <div>
            <svg class="batch-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span id="selectedCount">Đã chọn 0 đơn</span>
        </div>
        <div>
            <button id="batchApproveBtn" class="btn-batch btn-batch-success">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Duyệt đã chọn
            </button>
            <button id="batchCancelBtn" class="btn-batch btn-batch-danger">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Hủy đã chọn
            </button>
            <button id="clearSelectionBtn" class="btn-batch btn-batch-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Bỏ chọn
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h3>Danh sách đơn hàng</h3>
            <span class="badge active" id="orderCount">0 đơn hàng</span>
        </div>
        <div class="card-body" style="padding: 20px; overflow-x: auto;">
            <table class="data-table" id="ordersTable" style="width: 100%; min-width: 1200px;">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" id="selectAllOrders"></th>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Email/SĐT</th>
                        <th>Ngày thuê</th>
                        <th>Ngày trả</th>
                        <th>Tổng tiền</th>
                        <th>Tiền cọc</th>
                        <th>Trạng thái đơn</th>
                        <th>Trạng thái thanh toán</th>
                        <th>Trạng thái cọc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="12" style="text-align: center;">Đang tải...</tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/admin/js/order.js"></script>