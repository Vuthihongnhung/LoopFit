<?php $pageTitle = 'Quản lý sản phẩm'; $activeMenu = 'products'; ?>
<div class="products-page">
    <div class="page-header">
        <h1>Sản phẩm</h1>
        <button class="btn btn-primary" id="openCreateModalBtn"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="data-table" id="productsTable">
                <thead>
                    <tr><th>ID</th><th>Ảnh</th><th>Tên</th><th>Giá</th><th>Tồn kho</th><th>Danh mục</th><th>Thao tác</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa sản phẩm -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Thêm sản phẩm</h2>
            <span class="close">&times;</span>
        </div>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="productId">
            <div class="form-group">
                <label>Tên sản phẩm</label>
                <input type="text" name="name" id="productName" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Giá (VNĐ)</label>
                    <input type="number" name="price" id="productPrice" required>
                </div>
                <div class="form-group">
                    <label>Tồn kho</label>
                    <input type="number" name="stock" id="productStock" required>
                </div>
            </div>
            <div class="form-group">
                <label>Danh mục</label>
                <select name="category_id" id="productCategory" required>
                    <option value="1">Áo</option>
                    <option value="2">Quần</option>
                    <option value="3">Váy</option>
                </select>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" id="productDescription" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Ảnh sản phẩm</label>
                <input type="file" name="image" id="productImage" accept="image/*">
                <div id="currentImagePreview" style="margin-top:10px;"></div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <button type="button" class="btn btn-secondary close-modal">Hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
    const BASE_URL = 'http://localhost:8080/LoopFit/public';
    function loadProducts() {
    console.log('Đang gọi API...');
    fetch(BASE_URL + '/index.php?url=admin/api/products')
        .then(res => res.json())
        .then(data => {
            console.log('Dữ liệu nhận được:', data);
            console.log('Số lượng sản phẩm:', data.data?.length);
            
            let html = '';
            if (data.data && data.data.length > 0) {
                data.data.forEach(p => {
                    let imageUrl = p.image ? p.image : BASE_URL + '/uploads/default.png';
                    html += `<tr>
                        <td>${p.id}</td>
                        <td><img src="${imageUrl}" width="50" height="50"></td>
                        <td>${p.name}</td>
                        <td>${new Intl.NumberFormat('vi-VN').format(p.price)}đ</td>
                        <td>${p.stock}</td>
                        <td>${p.category.name}</td>
                        <td>
                            <button class="btn-icon edit" data-id="${p.id}">✏️</button>
                            <button class="btn-icon delete" data-id="${p.id}">🗑️</button>
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="7">Không có sản phẩm nào</td></tr>';
            }
            document.querySelector('#productsTable tbody').innerHTML = html;
            attachProductEvents();
        })
        .catch(error => {
            console.error('Lỗi khi gọi API:', error);
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
                document.getElementById('modalTitle').innerText = 'Sửa sản phẩm';
                document.getElementById('productId').value = p.id;
                document.getElementById('productName').value = p.name;
                document.getElementById('productPrice').value = p.price;
                document.getElementById('productStock').value = p.stock;
                document.getElementById('productCategory').value = p.category.id;
                document.getElementById('productDescription').value = p.description;
                if(p.image) document.getElementById('currentImagePreview').innerHTML = `<img src="${p.image}" width="80">`;
                document.getElementById('productModal').style.display = 'block';
            });
    }
    function deleteProduct(id) {
        if(confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
            fetch(BASE_URL + '/index.php?url=admin/api/products/delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}`
            }).then(res => res.json()).then(data => {
                if(data.success) loadProducts();
                else alert('Xóa thất bại');
            });
        }
    }
    document.getElementById('openCreateModalBtn').onclick = () => {
        document.getElementById('modalTitle').innerText = 'Thêm sản phẩm';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('currentImagePreview').innerHTML = '';
        document.getElementById('productModal').style.display = 'block';
    };
    document.getElementById('productForm').onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        fetch(BASE_URL + '/index.php?url=admin/products/update', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('productModal').style.display = 'none';
                    loadProducts();
                } else alert(data.error);
            });
    };
    // modal close
    document.querySelectorAll('.close, .close-modal').forEach(el => {
        el.onclick = () => document.getElementById('productModal').style.display = 'none';
    });
    loadProducts();
</script>