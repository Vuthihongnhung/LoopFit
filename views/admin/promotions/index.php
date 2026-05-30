<?php 
$pageTitle = 'Quản lý khuyến mãi'; 
$activeMenu = 'promotions';
?>

<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<div class="promotions-page">
    <div class="page-header">
        <h1><i class="fas fa-tags"></i> Khuyến mãi</h1>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs-navigation" style="margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; display: flex; gap: 0;">
        <button class="tab-btn active" data-tab="tab-discount-codes" style="padding: 12px 20px; border: none; background: none; cursor: pointer; font-size: 16px; border-bottom: 3px solid #3b82f6; color: #3b82f6; font-weight: 500;">
            <i class="fas fa-ticket-alt"></i> Mã giảm giá
        </button>
        <button class="tab-btn" data-tab="tab-bulk-promotions" style="padding: 12px 20px; border: none; background: none; cursor: pointer; font-size: 16px; border-bottom: 3px solid transparent; color: #6b7280; font-weight: 500;">
            <i class="fas fa-chart-line"></i> Chương trình khuyến mãi
        </button>
    </div>

    <!-- Tab 1: Mã giảm giá -->
    <div id="tab-discount-codes" class="tab-content active">
        <div class="page-header" style="margin-bottom: 20px;">
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
    </div><!-- End of tab-discount-codes -->

    <!-- Tab 2: Chương trình khuyến mãi -->
    <div id="tab-bulk-promotions" class="tab-content" style="display: none;">
        <?php include __DIR__ . '/bulk_promotions.php'; ?>
    </div><!-- End of tab-bulk-promotions -->
</div><!-- End of promotions-page -->

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

<!-- Tab Navigation Handler -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class từ tất cả button
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.borderBottom = '3px solid transparent';
                btn.style.color = '#6b7280';
            });
            
            // Remove active class từ tất cả tab content
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
                tab.classList.remove('active');
            });
            
            // Add active class cho button hiện tại
            this.classList.add('active');
            this.style.borderBottom = '3px solid #3b82f6';
            this.style.color = '#3b82f6';
            
            // Show tab content
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.style.display = 'block';
                tabContent.classList.add('active');
            }
        });
    });
});
</script>