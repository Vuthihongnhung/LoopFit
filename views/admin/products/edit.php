<?php 
$pageTitle = 'Sửa sản phẩm'; 
$activeMenu = 'products'; 
// Giả sử controller truyền biến $product
?>
<div class="products-page">
    <div class="page-header">
        <h1>Sửa sản phẩm</h1>
        <a href="<?php echo BASE_URL; ?>/admin/products" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Giá (VNĐ)</label>
                        <input type="number" name="price" value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tồn kho</label>
                        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id" required>
                        <option value="1" <?php echo $product['category_id']==1?'selected':''; ?>>Áo</option>
                        <option value="2" <?php echo $product['category_id']==2?'selected':''; ?>>Quần</option>
                        <option value="3" <?php echo $product['category_id']==3?'selected':''; ?>>Váy</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Ảnh hiện tại</label><br>
                    <?php if ($product['image']): ?>
                        <img src="<?php echo BASE_URL; ?>/uploads/<?php echo $product['image']; ?>" width="80" style="margin-bottom:10px;">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                    <a href="<?php echo BASE_URL; ?>/admin/products" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('editProductForm').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch(BASE_URL + '/admin/products/update', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Cập nhật thành công');
            window.location.href = BASE_URL + '/admin/products';
        } else {
            showToast(data.error || 'Lỗi cập nhật', 'error');
        }
    };
</script>