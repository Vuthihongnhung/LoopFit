<div class="page-header" style="margin-bottom: 20px;">
    <button class="btn btn-primary" id="openBulkPromoModalBtn">
        <i class="fas fa-plus"></i> Thêm chương trình khuyến mãi
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Danh sách chương trình khuyến mãi</h3>
        <div class="search-box">
            <input type="text" id="searchBulkPromo" placeholder="🔍 Tìm kiếm chương trình..." class="form-control">
        </div>
    </div>
    <div class="card-body" style="padding: 20px; overflow-x: auto;">
        <table class="data-table" id="bulkPromotionsTable" style="width: 100%; min-width: 1000px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên chương trình</th>
                    <th>Loại khuyến mãi</th>
                    <th>Giá trị</th>
                    <th>Phạm vi áp dụng</th>
                    <th>Số sản phẩm</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="10" style="text-align: center;">📦 Đang tải...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal thêm/sửa chương trình khuyến mãi -->
<div id="bulkPromoModal" class="modal">
    <div class="modal-content" style="width: 600px;">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle"></i> Thêm chương trình khuyến mãi</h2>
            <span class="close">&times;</span>
        </div>
        <form id="bulkPromoForm">
            <input type="hidden" name="id" id="bulkPromoId">
            <input type="hidden" name="update_flag" id="bulkPromoUpdateFlag">
            
            <div class="form-group">
                <label>Tên chương trình <span style="color: red;">*</span></label>
                <input type="text" name="name" id="bulkPromoName" required>
            </div>

            <div class="form-group">
                <label>Loại khuyến mãi <span style="color: red;">*</span></label>
                <div class="discount-type-group">
                    <label><input type="radio" name="promotion_type" value="percentage" checked> Giảm theo %</label>
                    <label><input type="radio" name="promotion_type" value="fixed_price"> Đồng giá</label>
                </div>
            </div>

            <div class="form-group" id="bulk-percent-group">
                <label>Phần trăm giảm (%) <span style="color: red;">*</span></label>
                <input type="number" name="discount_value" id="bulkPromoDiscountPercent" min="0" max="100" value="10">
            </div>

            <div class="form-group" id="bulk-fixed-price-group" style="display: none;">
                <label>Giá thuê cố định (VNĐ) <span style="color: red;">*</span></label>
                <input type="number" name="fixed_price" id="bulkPromoFixedPrice" min="0" step="1000">
            </div>

            <div class="form-group">
                <label>Phạm vi áp dụng <span style="color: red;">*</span></label>
                <select name="scope_type" id="bulkPromoScopeType" required>
                    <option value="">-- Chọn phạm vi --</option>
                    <option value="all_products">Toàn bộ sản phẩm</option>
                    <option value="category">Một danh mục</option>
                    <option value="specific_products">Sản phẩm cụ thể</option>
                </select>
            </div>

            <div class="form-group" id="bulk-category-group" style="display: none;">
    <label>Chọn danh mục</label>
    <select name="category_ids[]" id="bulkPromoCategoryId" multiple size="5" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ddd;">
        <!-- options sẽ load bằng JS -->
    </select>
    <small style="display: block; margin-top: 5px; color: #666;">💡 Giữ phím Ctrl (hoặc Cmd trên Mac) để chọn nhiều danh mục</small>
</div>

            <div class="form-group" id="bulk-products-group" style="display: none;">
                <label>Chọn sản phẩm</label>
                <div id="bulkPromoProductsList" style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; padding: 10px; border-radius: 4px;">
                    <p style="text-align: center; color: #999;">Đang tải sản phẩm...</p>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Ngày bắt đầu <span style="color: red;">*</span></label>
                    <input type="datetime-local" name="start_date" id="bulkPromoStart" required>
                </div>
                <div class="form-group">
                    <label>Ngày kết thúc <span style="color: red;">*</span></label>
                    <input type="datetime-local" name="end_date" id="bulkPromoEnd" required>
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="bulkPromoIsActive" checked>
                    <span>Kích hoạt chương trình</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <button type="button" class="btn btn-secondary close-modal">Hủy</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/admin/js/bulk_promotion.js"></script>
