<?php 
$pageTitle = 'Quản lý sản phẩm'; 
$activeMenu = 'products';
?>
<script>const BASE_URL = 'http://localhost:8080/LoopFit/public';</script>
<div class="products-page">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Sản phẩm</h1>
        <a href="http://localhost:8080/LoopFit/public/index.php?url=admin/products/create" class="btn btn-primary">
    <i class="fas fa-plus"></i> Thêm sản phẩm
</a>
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
        <th>Giá cọc</th>
        <th>Tồn kho</th>
        <th>Danh mục</th>
        <th>Giới tính</th>
        <th>Màu sắc</th>
        <th>Kích thước</th>
        <th>Số lần thuê</th>
        <th>Thao tác</th>
    </tr>
</thead>
                    <tbody>
                        <td><td colspan="12" class="text-center">Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function loadProducts() {
    fetch(BASE_URL + '/index.php?url=admin/api/products')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.data && data.data.length > 0) {
                data.data.forEach(p => {
                    let imageUrl = p.image ? p.image : BASE_URL + '/uploads/default.png';
                    let categoryName = p.category_name || 'Chưa phân loại';
                    html += `<tr>
    <td>${p.id}</td>
    <td><img src="${imageUrl}" width="50" height="50" style="object-fit:cover; border-radius:8px;"></td>
    <td><strong>${p.name}</strong></td>
    <td class="price">${new Intl.NumberFormat('vi-VN').format(p.price)}đ</td>
    <td class="price">${new Intl.NumberFormat('vi-VN').format(p.deposit_price)}đ</td>
    <td><span class="badge ${p.stock > 0 ? 'active' : 'banned'}">${p.stock}</span></td>
    <td><span class="badge active">${categoryName}</span></td>
    <td>${p.gender || 'unisex'}</td>
    <td>${p.colors || '-'}</td>
    <td>${p.sizes || '-'}</td>
    <td>${p.rented_count || 0}</td>
    <td>
        <button class="btn-icon edit" data-id="${p.id}" title="Sửa"><i class="fas fa-edit"></i></button>
<button class="btn-icon delete" data-id="${p.id}" title="Xóa"><i class="fas fa-trash"></i></button>
    </td>
</tr>`;
                });
            } else {
                html = '<tr><td colspan="12" class="text-center">📦 Không có sản phẩm nào</td></tr>';
            }
            document.querySelector('#productsTable tbody').innerHTML = html;
            
            // Gán sự kiện xóa
            document.querySelectorAll('.delete').forEach(btn => {
                btn.onclick = () => deleteProduct(btn.dataset.id);
            });
            document.querySelectorAll('.edit').forEach(btn => {
        btn.onclick = () => editProduct(btn.dataset.id);
    });  
        })
        .catch(error => {
            console.error('Lỗi khi gọi API:', error);
            document.querySelector('#productsTable tbody').innerHTML = '<tr><td colspan="7" class="text-center">❌ Lỗi tải dữ liệu</td></tr>';
        });
}
function editProduct(id) {
    window.location.href = BASE_URL + '/index.php?url=admin/products/edit&id=' + id;
}
function deleteProduct(id) {
    console.log('Xóa sản phẩm ID:', id);  
    if(confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        console.log('Đã xác nhận xóa');  
        fetch(BASE_URL + '/index.php?url=admin/products/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        }).then(res => res.json()).then(data => {
            console.log('Kết quả:', data);  
            if(data.success) {
                showToast('Xóa sản phẩm thành công', 'success');
                loadProducts();
            } else alert('Xóa thất bại');
        });
    }
}

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

loadProducts();
</script>