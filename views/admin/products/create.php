<?php $pageTitle = 'Thêm sản phẩm'; $activeMenu = 'products'; ?>
<div class="products-page">
    <div class="page-header">
        <h1>Thêm sản phẩm mới</h1>
        <a href="<?php echo BASE_URL; ?>/admin/products" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form id="createProductForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Giá (VNĐ)</label>
                        <input type="number" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Tồn kho</label>
                        <input type="number" name="stock" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id" required>
                        <option value="1">Áo</option>
                        <option value="2">Quần</option>
                        <option value="3">Váy</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Ảnh sản phẩm</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                    <a href="<?php echo BASE_URL; ?>/admin/products" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('createProductForm').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch(BASE_URL + '/admin/products/store', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Thêm sản phẩm thành công');
            window.location.href = BASE_URL + '/admin/products';
        } else {
            showToast(data.error || 'Lỗi khi thêm', 'error');
        }
    };
</script>