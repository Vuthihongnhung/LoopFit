<?php 
$pageTitle = 'Quản lý danh mục'; 
$activeMenu = 'categories';
?>
<script>const BASE_URL = '<?= BASE_URL ?? "http://localhost:8080/LoopFit/public" ?>';</script>
<div class="categories-page">
    <div class="page-header">
        <h1><i class="fas fa-folder-tree"></i> Quản lý danh mục</h1>
        <button class="btn btn-primary" id="addParentBtn"><i class="fas fa-plus"></i> Thêm nhóm lớn</button>
    </div>
    <div class="card">
        <div class="card-body">
            <div id="categoriesTree" class="categories-tree"></div>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa danh mục -->
<div id="categoryModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Thêm danh mục</h3>
                <button type="button" class="close-modal" data-dismiss="modal">&times;</button>
            </div>
            <form id="categoryForm">
                <input type="hidden" name="id" id="categoryId">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên danh mục</label>
                        <input type="text" name="name" id="categoryName" required>
                    </div>
                    <div class="form-group" id="parentSelectGroup" style="display: none;">
                        <label>Thuộc nhóm lớn</label>
                        <select name="parent_id" id="categoryParent">
                            <option value="0">-- Chọn nhóm lớn --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Icon (Font Awesome)</label>
                        <input type="text" name="icon" id="categoryIcon" placeholder="VD: fa-fire, fa-tshirt">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Khởi tạo quản lý danh mục (sẽ được định nghĩa trong admin.js)
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initCategoriesModule === 'function') {
    initCategoriesModule();
} else {
        console.warn('initCategoryManager chưa được định nghĩa trong admin.js');
    }
});
</script>