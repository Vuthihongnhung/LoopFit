<?php 
$pageTitle = 'Quản lý khuyến mãi'; 
$activeMenu = 'promotions';
?>

<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<div class="promotions-page">
    <div class="page-header">
        <h1><i class="fas fa-tags"></i> Khuyến mãi</h1>
        <button class="btn btn-primary" id="openModalBtn">
            <i class="fas fa-plus"></i> Thêm mã giảm giá
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách mã giảm giá</h3>
            <div class="search-box">
                <input type="text" id="searchPromo" placeholder="🔍 Tìm kiếm mã..." class="form-control">
            </div>
        </div>
        <div class="card-body" style="padding: 20px; overflow-x: auto;">
            <table class="data-table" id="promotionsTable" style="width: 100%; min-width: 1000px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã code</th>
                        <th>Giảm giá</th>
                        <th>Điều kiện (VNĐ)</th>
                        <th>Giới hạn sử dụng</th>
                        <th>Đã dùng</th>
                        <th>Còn lại</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="11" style="text-align: center;">📦 Đang tải...</tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa khuyến mãi -->
<div id="promoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle"></i> Thêm mã giảm giá</h2>
            <span class="close">&times;</span>
        </div>
        <form id="promoForm">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="update_flag" id="updateFlag">
            <div class="form-group">
                <label>Mã code</label>
                <input type="text" name="code" id="promoCode" required>
            </div>
            <div class="form-group">
                <label>Loại giảm giá</label>
                <div class="discount-type-group">
                    <label><input type="radio" name="discount_type" value="percent" checked> Giảm theo %</label>
                    <label><input type="radio" name="discount_type" value="amount"> Giảm theo số tiền</label>
                </div>
            </div>
            <div class="form-group" id="percent-group">
                <label>Giảm giá (%)</label>
                <input type="number" name="discount_percent" id="promoDiscountPercent" min="0" max="100" value="0">
            </div>
            <div class="form-group" id="amount-group" style="display:none;">
                <label>Giảm giá (VNĐ)</label>
                <input type="number" name="discount_amount" id="promoDiscountAmount" min="0" step="1000" value="0">
            </div>
            <div class="form-group">
                <label>Điều kiện đơn hàng tối thiểu (VNĐ)</label>
                <input type="number" name="min_order_value" id="promoMinOrder" value="0">
            </div>
            <div class="form-group">
                <label>Giới hạn sử dụng</label>
                <input type="number" name="usage_limit" id="promoUsageLimit" value="0">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_date" id="promoStart">
                </div>
                <div class="form-group">
                    <label>Ngày kết thúc</label>
                    <input type="datetime-local" name="end_date" id="promoEnd">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <button type="button" class="btn btn-secondary close-modal">Hủy</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/admin/js/promotion.js"></script>