<?php 
$pageTitle = 'Quản lý danh mục'; 
$activeMenu = 'categories';
?>
<script>const BASE_URL = '<?= BASE_URL ?? "http://localhost:8080/LoopFit/public" ?>';</script>
<div class="lf-categories-page">
    <div class="lf-page-header">
        <h1><i class="fas fa-folder-tree"></i> Quản lý danh mục</h1>
        <button class="lf-btn lf-btn-primary" id="addParentBtn"><i class="fas fa-plus"></i> Thêm nhóm lớn</button>
    </div>
    <div class="lf-card">
        <div class="lf-card-body">
            <div id="categoriesTree" class="lf-categories-tree"></div>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa danh mục -->
<div id="categoryModal" class="lf-modal">
    <div class="lf-modal-dialog">
        <div class="lf-modal-content">
            <div class="lf-modal-header">
                <h3 id="modalTitle">Thêm danh mục</h3>
                <button type="button" class="lf-close-modal" data-dismiss="modal">&times;</button>
            </div>
            <form id="categoryForm">
                <input type="hidden" name="id" id="categoryId">
                <div class="lf-modal-body">
                    <div class="lf-form-group">
                        <label>Tên danh mục</label>
                        <input type="text" name="name" id="categoryName" required>
                    </div>
                    <div class="lf-form-group" id="parentSelectGroup" style="display: none;">
                        <label>Thuộc nhóm lớn</label>
                        <select name="parent_id" id="categoryParent">
                            <option value="0">-- Chọn nhóm lớn --</option>
                        </select>
                    </div>
                    <div class="lf-form-group">
                        <label>Icon (Font Awesome)</label>
                        <input type="text" name="icon" id="categoryIcon" placeholder="VD: fa-fire, fa-tshirt">
                    </div>
                </div>
                <div class="lf-modal-footer">
                    <button type="button" class="lf-btn lf-btn-secondary lf-close-modal">Hủy</button>
                    <button type="submit" class="lf-btn lf-btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ===== LoopFit Categories Styles ===== */
.lf-categories-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.lf-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.lf-page-header h1 {
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, #1e3a8a, #2563eb, #3b82f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
}

.lf-card {
    background: #f8fafc;
    border-radius: 24px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.lf-card-body {
    padding: 24px;
}

.lf-categories-tree {
    list-style: none;
    padding-left: 0;
}

.lf-categories-tree li {
    margin-bottom: 8px;
}

.lf-category-item {
    background: #ffffff;
    border-radius: 12px;
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}

.lf-category-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59,130,246,0.1);
}

.lf-category-actions {
    display: flex;
    gap: 8px;
}

.lf-btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #64748b;
    transition: all 0.2s;
    padding: 4px;
    border-radius: 6px;
}

.lf-btn-icon:hover {
    background-color: #f1f5f9;
    transform: scale(1.05);
}

.lf-edit-cat {
    color: #3b82f6;
}

.lf-edit-cat:hover {
    color: #2563eb;
}

.lf-delete-cat {
    color: #ef4444;
}

.lf-delete-cat:hover {
    color: #dc2626;
}

.lf-add-child {
    color: #10b981;
}

.lf-add-child:hover {
    color: #059669;
}

.lf-child-list {
    list-style: none;
    padding-left: 30px;
    margin-top: 8px;
}

.lf-btn {
    padding: 8px 20px;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    font-size: 14px;
    transition: all 0.2s;
}

.lf-btn-primary {
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: white;
    box-shadow: 0 2px 4px rgba(37,99,235,0.2);
}

.lf-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}

.lf-btn-secondary {
    background: #f1f5f9;
    color: #1e293b;
}

.lf-btn-secondary:hover {
    background: #e2e8f0;
}

/* Modal styles */
.lf-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.lf-modal-dialog {
    width: 90%;
    max-width: 600px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 30px rgba(0,0,0,0.2);
    animation: lfModalFadeIn 0.3s ease;
}

@keyframes lfModalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.lf-modal-content {
    border-radius: 16px;
    overflow: hidden;
}

.lf-modal-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: none;
}

.lf-modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.lf-close-modal {
    background: none;
    border: none;
    font-size: 28px;
    color: white;
    cursor: pointer;
    line-height: 1;
}

.lf-modal-body {
    padding: 24px;
    background: #ffffff;
}

.lf-modal-footer {
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.lf-form-group {
    margin-bottom: 20px;
}

.lf-form-group label {
    font-weight: 600;
    color: #1e293b;
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
}

.lf-form-group input, 
.lf-form-group select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.2s;
}

.lf-form-group input:focus,
.lf-form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

@media (max-width: 768px) {
    .lf-categories-page {
        padding: 12px;
    }
    .lf-page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .lf-card-body {
        padding: 16px;
    }
    .lf-category-item {
        flex-wrap: wrap;
        gap: 8px;
    }
    .lf-category-actions {
        margin-left: auto;
    }
}
</style>

<script>
// Khởi tạo quản lý danh mục 
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initCategoriesModule === 'function') {
        initCategoriesModule();
    } else {
        console.warn('initCategoriesModule chưa được định nghĩa trong admin.js');
    }
});
</script>