﻿﻿﻿// ========== BASE URL ==========
const BASE_URL = 'http://localhost:8080/LoopFit/public';

// ========== Toggle sidebar trên mobile ==========
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            sidebar.classList.toggle('open');
        });
    }
});

// ========== Toast notification ==========
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ========== Confirm delete (tùy chọn) ==========
function confirmDelete(message, callback) {
    if (confirm(message)) callback();
}

// ==================== PRODUCT MANAGEMENT ====================
// childMap sẽ được định nghĩa từ view (products/index.php)

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
                        <td><span class="badge ${p.stock > 0 ? 'active' : 'banned'}">${p.stock}</span></td>
                        <td><span class="badge active">${categoryName}</span></td>
                        <td>
                            <button class="btn-icon edit" data-id="${p.id}" title="Sửa"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon delete" data-id="${p.id}" title="Xóa"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">📦 Không có sản phẩm nào<\/td><\/tr>';
            }
            document.querySelector('#productsTable tbody').innerHTML = html;
            attachProductEvents();
        })
        .catch(error => {
            console.error('Lỗi khi gọi API:', error);
            document.querySelector('#productsTable tbody').innerHTML = '<tr><td colspan="7" class="text-center">❌ Lỗi tải dữ liệu<\/td><\/tr>';
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
            if (!p) {
                console.error('Không tìm thấy sản phẩm');
                return;
            }
            
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa sản phẩm';
            document.getElementById('productId').value = p.id;
            document.getElementById('productName').value = p.name || '';
            document.getElementById('productPrice').value = p.price || 0;
            document.getElementById('productStock').value = p.stock || 0;
            document.getElementById('productDescription').value = p.description || '';
            if(p.image) document.getElementById('currentImagePreview').innerHTML = `<img src="${p.image}" width="80">`;
            
            // Tìm parentId chứa category_id (dựa vào childMap từ view)
            let parentId = null;
            if (typeof childMap !== 'undefined') {
                for (const [pid, children] of Object.entries(childMap)) {
                    if (children.some(child => child.id == p.category_id)) {
                        parentId = pid;
                        break;
                    }
                }
            }
            
            if (parentId) {
                document.getElementById('parentCategory').value = parentId;
                const changeEvent = new Event('change');
                document.getElementById('parentCategory').dispatchEvent(changeEvent);
                
                setTimeout(() => {
                    const childSelect = document.getElementById('childCategory');
                    childSelect.value = p.category_id;
                    if (childSelect.value != p.category_id) {
                        for (let i = 0; i < childSelect.options.length; i++) {
                            if (childSelect.options[i].value == p.category_id) {
                                childSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }, 100);
            } else {
                console.warn('Không tìm thấy parent cho category_id =', p.category_id);
                document.getElementById('parentCategory').value = '';
                document.getElementById('childCategory').disabled = true;
                document.getElementById('childCategory').innerHTML = '<option value="">-- Chọn danh mục --</option>';
            }
            
            document.getElementById('productModal').style.display = 'block';
        })
        .catch(err => console.error('Lỗi khi lấy sản phẩm:', err));
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

// Khởi tạo các sự kiện khi DOM sẵn sàng (chỉ cho trang products)
document.addEventListener('DOMContentLoaded', function() {
    const parentCategory = document.getElementById('parentCategory');
    if (!parentCategory) return; // không phải trang products
    
    parentCategory.addEventListener('change', function() {
        const parentId = this.value;
        const childSelect = document.getElementById('childCategory');
        if (!parentId) {
            childSelect.disabled = true;
            childSelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
            return;
        }
        const children = (typeof childMap !== 'undefined' && childMap[parentId]) ? childMap[parentId] : [];
        childSelect.disabled = false;
        childSelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
        children.forEach(child => {
            childSelect.innerHTML += `<option value="${child.id}">${child.name}</option>`;
        });
    });
    
    document.getElementById('openCreateModalBtn')?.addEventListener('click', () => {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm sản phẩm';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('currentImagePreview').innerHTML = '';
        document.getElementById('parentCategory').value = '';
        document.getElementById('childCategory').disabled = true;
        document.getElementById('childCategory').innerHTML = '<option value="">-- Chọn danh mục --</option>';
        document.getElementById('productModal').style.display = 'block';
    });
    
    document.getElementById('productForm')?.addEventListener('submit', (e) => {
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
    });
    
    document.querySelectorAll('.close, .close-modal').forEach(el => {
        el.addEventListener('click', () => document.getElementById('productModal').style.display = 'none');
    });
    
    document.getElementById('searchProduct')?.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#productsTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    loadProducts();
});