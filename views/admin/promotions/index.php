<?php 
$pageTitle = 'Quản lý khuyến mãi'; 
$activeMenu = 'promotions';
?>

<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<style>
.promotions-page .modal-content {
    max-width: 620px;
    width: 100%;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(59, 130, 246, 0.2);
    box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12);
}
.promotions-page .modal-header {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.16), rgba(37, 99, 235, 0.08));
    padding: 22px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}
.promotions-page .modal-header h2 {
    margin: 0;
    font-size: 1.35rem;
    color: #0f172a;
    font-weight: 700;
}
.promotions-page .modal-header .close {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background: #ffffff;
    border: 1px solid rgba(59, 130, 246, 0.22);
    color: #2563eb;
    transition: background 0.2s ease;
}
.promotions-page .modal-header .close:hover {
    background: rgba(59, 130, 246, 0.08);
}
.promotions-page .form-description {
    padding: 0 24px 20px;
    font-size: 0.95rem;
    color: #475569;
    line-height: 1.6;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fbff;
}
.promotions-page .form-group {
    margin-bottom: 18px;
}
.promotions-page .form-group label {
    display: block;
    margin-bottom: 10px;
    color: #334155;
    font-weight: 600;
    font-size: 0.95rem;
}
.promotions-page .form-group input,
.promotions-page .form-group select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #cbd5e1;
    background: #fbfdff;
    color: #0f172a;
    font-size: 0.95rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.promotions-page .form-group input:focus,
.promotions-page .form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.12);
    background: #ffffff;
}
..promotions-page .discount-type-group {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.promotions-page .discount-type-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid #dbeafe;
    background: #f8fbff;
    cursor: pointer;
    color: #334155;
    font-weight: 600;
    transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
}
.promotions-page .discount-type-group label:hover {
    background: #eff6ff;
}
.promotions-page .discount-type-group input[type="radio"] {
    accent-color: #2563eb;
}
.promotions-page .discount-type-group input:checked + span,
.promotions-page .discount-type-group input:focus + span {
    color: #1d4ed8;
}
.promotions-page .discount-type-group input:checked + span::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #2563eb;
    margin-right: 8px;
}
.promotions-page .form-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}
.promotions-page .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 0 24px 24px;
}
.promotions-page .modal-content form {
    padding: 22px 24px 0;
}
.promotions-page .btn-primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}
.promotions-page .btn-primary:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
}
.promotions-page .btn-secondary {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #334155;
}
.promotions-page .btn-secondary:hover {
    background: #e2e8f0;
}
@media (max-width: 760px) {
    .promotions-page .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
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
    </div>

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
        <div class="modal-body">
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
                    <label><input type="radio" name="discount_type" value="percent" checked><span>Giảm theo %</span></label>
                    <label><input type="radio" name="discount_type" value="amount"><span>Giảm theo số tiền</span></label>
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
                <button type="submit" class="btn btn-primary">Lưu mã</button>
                <button type="button" class="btn btn-secondary close-modal">Hủy</button>
            </div>
            </form>
        </div>
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