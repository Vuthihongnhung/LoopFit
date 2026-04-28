<?php 
$pageTitle = 'Quản lý khuyến mãi'; 
$activeMenu = 'promotions';
?>
<script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
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
        <div class="card-body" style="display: flex; justify-content: center; padding: 20px;">
            <div style="width: 100%; max-width: 1400px; overflow-x: auto;">
                <table class="data-table" id="promotionsTable" style="width: 100%; margin: 0 auto;">
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
                        <tr><td colspan="11" style="text-align: center;">📦 Đang tải...<\/tr>
                    </tbody>
                </table>
            </div>
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
            <input type="hidden" name="id" id="editId" value="">
            <input type="hidden" name="update_flag" id="updateFlag" value="">
            <div class="form-group">
                <label><i class="fas fa-barcode"></i> Mã code</label>
                <input type="text" name="code" id="promoCode" placeholder="Nhập mã (VD: SALE20)" required style="text-transform: uppercase;">
                <small>Mã sẽ tự động viết hoa</small>
            </div>
            <div class="form-group">
                <label><i class="fas fa-percent"></i> Giảm giá (%)</label>
                <input type="number" name="discount_percent" id="promoDiscount" min="1" max="100" placeholder="1-100" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-money-bill-wave"></i> Điều kiện đơn hàng tối thiểu (VNĐ)</label>
                <input type="number" name="min_order_value" id="promoMinOrder" value="0" min="0" step="1000" placeholder="0 = không giới hạn">
            </div>
            <div class="form-group">
                <label><i class="fas fa-infinity"></i> Giới hạn số lần sử dụng</label>
                <input type="number" name="usage_limit" id="promoUsageLimit" value="0" min="0" placeholder="0 = không giới hạn">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_date" id="promoStart">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-check"></i> Ngày kết thúc</label>
                    <input type="datetime-local" name="end_date" id="promoEnd">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <button type="button" class="btn btn-secondary close-modal"><i class="fas fa-times"></i> Hủy</button>
            </div>
        </form>
    </div>
</div>