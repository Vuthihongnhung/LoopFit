<?php 
$pageTitle = 'Quản lý sản phẩm'; 
$activeMenu = 'products';
?>
<div class="products-page">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Sản phẩm</h1>
        <button class="btn btn-primary" id="openCreateModalBtn">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách sản phẩm</h3>
            <div class="search-box">
                <input type="text" id="searchProduct" placeholder="Tìm kiếm sản phẩm..." class="form-control">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Danh mục</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" class="text-center">Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.search-box {
    width: 250px;
}
.search-box input {
    border-radius: 40px;
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
}
.text-center {
    text-align: center;
}
.table-responsive {
    overflow-x: auto;
}
.image-preview img {
    max-width: 100px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 4px;
}
</style>

<!-- Modal thêm/sửa sản phẩm -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"><i class="fas fa-plus-circle"></i> Thêm sản phẩm</h2>
            <span class="close">&times;</span>
        </div>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="productId">
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Tên sản phẩm</label>
                <input type="text" name="name" id="productName" placeholder="Nhập tên sản phẩm" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Giá (VNĐ)</label>
                    <input type="number" name="price" id="productPrice" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-cubes"></i> Tồn kho</label>
                    <input type="number" name="stock" id="productStock" placeholder="0" required>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-folder"></i> Danh mục</label>
                <select name="menu_category_id" id="productCategory" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $row): ?>
                        <option value="<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Mô tả</label>
                <textarea name="description" id="productDescription" rows="3" placeholder="Mô tả sản phẩm..."></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-image"></i> Ảnh sản phẩm</label>
                <input type="file" name="image" id="productImage" accept="image/*">
                <div id="currentImagePreview" class="image-preview" style="margin-top:10px;"></div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <button type="button" class="btn btn-secondary close-modal"><i class="fas fa-times"></i> Hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';

function loadProducts() {
    fetch(BASE_URL + '/index.php?url=admin/api/products')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.data && data.data.length > 0) {
                data.data.forEach(p => {
                    let imageUrl = p.image ? p.image : BASE_URL + '/uploads/default.png';
                    let categoryName = p.menu_category || p.category?.name || 'Chưa phân loại';
                    html += `<tr>
                        <td>${p.id}</td>
                        <td><img src="${imageUrl}" width="50" height="50" style="object-fit:cover; border-radius:8px;"></td>
                        <td><strong>${p.name}</strong></td>
                        <td class="price">${new Intl.NumberFormat('vi-VN').format(p.price)}đ</td>
                        <td><span class="badge ${p.stock > 0 ? 'active' : 'banned'}">${p.stock}</span></td>
                        <td><span class="badge active">${categoryName}</span></td>
                        <td>
                            <button class="btn-icon edit" data-id="${p.id}" title="Sửa"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon delete" data-id="${p.id}" title="Xóa"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">📦 Không có sản phẩm nào</td></tr>';
            }
            document.querySelector('#productsTable tbody').innerHTML = html;
            attachProductEvents();
        })
        .catch(error => {
            console.error('Lỗi khi gọi API:', error);
            document.querySelector('#productsTable tbody').innerHTML = '<tr><td colspan="7" class="text-center">❌ Lỗi tải dữ liệu</td></tr>';
        });
}

function attachProductEvents() {
    document.querySelectorAll('.edit').forEach(btn => {
        btn.onclick = () => editProduct(btn.dataset.id);
    });
    document.querySelectorAll('.delete').forEach(btn => {
        btn.onclick = () => deleteProduct(btn.dataset.id);
    });
}

function editProduct(id) {
    fetch(BASE_URL + `/index.php?url=admin/api/products?id=${id}`)
        .then(res => res.json())
        .then(data => {
            const p = data.data;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa sản phẩm';
            document.getElementById('productId').value = p.id;
            document.getElementById('productName').value = p.name;
            document.getElementById('productPrice').value = p.price;
            document.getElementById('productStock').value = p.stock;
            document.getElementById('productCategory').value = p.menu_category_id || '';
            document.getElementById('productDescription').value = p.description;
            if(p.image) document.getElementById('currentImagePreview').innerHTML = `<img src="${p.image}" width="80">`;
            document.getElementById('productModal').style.display = 'block';
        });
}

function deleteProduct(id) {
    if(confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        fetch(BASE_URL + '/index.php?url=admin/products/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        }).then(res => res.json()).then(data => {
            if(data.success) {
                showToast('Xóa sản phẩm thành công', 'success');
                loadProducts();
            } else alert('Xóa thất bại');
        });
    }
}

document.getElementById('openCreateModalBtn').onclick = () => {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm sản phẩm';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('currentImagePreview').innerHTML = '';
    document.getElementById('productModal').style.display = 'block';
};

document.getElementById('productForm').onsubmit = (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const url = document.getElementById('productId').value ? 
        BASE_URL + '/index.php?url=admin/products/update' : 
        BASE_URL + '/index.php?url=admin/products/store';
    fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('productModal').style.display = 'none';
                showToast(data.message, 'success');
                loadProducts();
            } else alert(data.error);
        });
};

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

document.getElementById('searchProduct')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#productsTable tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

document.querySelectorAll('.close, .close-modal').forEach(el => {
    el.onclick = () => document.getElementById('productModal').style.display = 'none';
});

loadProducts();
</script>