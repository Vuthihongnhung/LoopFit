﻿var ordersData = [];

// ========== GLOBAL FUNCTIONS ==========
function formatNumber(num) {
    if (!num) return '0';
    return new Intl.NumberFormat('vi-VN').format(num);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
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
    if (!parentCategory) return;
    
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
        fetch(url, { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(formData).toString()
        })
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

// ========== PRODUCT PAGE ONLY (version có lọc) ==========
if (document.getElementById('productsTable')) {
    function renderProducts(products) {
    let html = '';
    if (products.length > 0) {
        products.forEach(p => {
            let imageUrl = p.image ? p.image : BASE_URL + '/uploads/default.png';
            let categoryName = p.category_name || 'Chưa phân loại';
            html += `<tr>
                <td>${p.id}</td>
                <td><img src="${imageUrl}" width="50" height="50" style="object-fit:cover; border-radius:8px;"></td>
                <td><strong>${p.name}</strong></td>
                <td class="price">${new Intl.NumberFormat('vi-VN').format(p.price)}đ</td>
                <td class="discount">${p.discount_percent ? p.discount_percent + '%' : '0%'}</td>
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
        html = '<tr><td colspan="12" class="text-center">🔍 Không tìm thấy sản phẩm phù hợp</td></table>';
    }
    document.querySelector('#productsTable tbody').innerHTML = html;
    
    document.querySelectorAll('.delete').forEach(btn => {
        btn.onclick = () => deleteProduct(btn.dataset.id);
    });
    document.querySelectorAll('.edit').forEach(btn => {
        btn.onclick = () => editProduct(btn.dataset.id);
    });
}
    let allProducts = [];
    
    function loadProducts() {
        fetch(BASE_URL + '/index.php?url=admin/api/products')
            .then(res => res.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    allProducts = data.data;
                    renderProducts(allProducts);
                } else {
                    document.querySelector('#productsTable tbody').innerHTML = '<tr><td colspan="12" class="text-center">📦 Không có sản phẩm nào</td></tr>';
                }
            })
            .catch(error => {
                console.error('Lỗi khi gọi API:', error);
                document.querySelector('#productsTable tbody').innerHTML = '<tr><td colspan="12" class="text-center">❌ Lỗi tải dữ liệu</td></tr>';
            });
    }
    
    function renderProducts(products) {
        let html = '';
        if (products.length > 0) {
            products.forEach(p => {
                let imageUrl = p.image ? p.image : BASE_URL + '/uploads/default.png';
                let categoryName = p.category_name || 'Chưa phân loại';
                html += `<tr>
                    <td>${p.id}</td>
                    <td><img src="${imageUrl}" width="50" height="50" style="object-fit:cover; border-radius:8px;"></td>
                    <td><strong>${p.name}</strong></td>
                    <td class="price">${new Intl.NumberFormat('vi-VN').format(p.price)}đ</td>
                    <td class="discount">${p.discount_percent ? p.discount_percent + '%' : '0%'}</td>
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
            html = '<tr><td colspan="12" class="text-center">🔍 Không tìm thấy sản phẩm phù hợp</td></tr>';
        }
        document.querySelector('#productsTable tbody').innerHTML = html;
        
        document.querySelectorAll('.delete').forEach(btn => {
            btn.onclick = () => deleteProduct(btn.dataset.id);
        });
        document.querySelectorAll('.edit').forEach(btn => {
            btn.onclick = () => editProduct(btn.dataset.id);
        });
    }
    
    function filterProducts() {
        const searchTerm = document.getElementById('searchProduct')?.value.toLowerCase() || '';
        const genderFilter = document.getElementById('filterGender')?.value || '';
        const groupFilter = document.getElementById('filterCategoryGroup')?.value || '';
        
        let filtered = [...allProducts];
        
        if (searchTerm) {
            filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm));
        }
        
        if (genderFilter) {
            filtered = filtered.filter(p => p.gender === genderFilter);
        }
        
        if (groupFilter) {
            const categoryToGroup = {
                5: 1, 6: 1, 7: 1, 8: 1,
                9: 2, 10: 2,
                11: 3, 12: 3, 13: 3, 14: 3, 15: 3,
                16: 4, 17: 4, 18: 4
            };
            filtered = filtered.filter(p => categoryToGroup[p.category_id] == groupFilter);
        }
        
        renderProducts(filtered);
    }
    
    function editProduct(id) {
        window.location.href = BASE_URL + '/index.php?url=admin/products/edit&id=' + id;
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
    
    document.getElementById('searchProduct')?.addEventListener('keyup', filterProducts);
    document.getElementById('filterGender')?.addEventListener('change', filterProducts);
    document.getElementById('filterCategoryGroup')?.addEventListener('change', filterProducts);
    document.getElementById('resetFilter')?.addEventListener('click', function() {
        document.getElementById('searchProduct').value = '';
        document.getElementById('filterGender').value = '';
        document.getElementById('filterCategoryGroup').value = '';
        filterProducts();
    });
    
    loadProducts();
}

// ========== QUẢN LÝ ĐƠN HÀNG: Cập nhật status_after_return ==========
window.updateStatusAfterReturn = function(detailId, status) {
    fetch(BASE_URL + '/index.php?url=admin/orders/updateStatusAfterReturn', {  
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `detail_id=${detailId}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.error || 'Cập nhật thất bại', 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối', 'error'));
};

// ========== XỬ LÝ HOÀN CỌC ==========
window.processDeposit = function(orderId) {
    fetch(BASE_URL + '/index.php?url=admin/orders/processDeposit', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Hoàn cọc thất bại', 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối', 'error'));
};

// ========== XÁC NHẬN THANH TOÁN ==========
window.confirmPayment = function(orderId) {
    fetch(BASE_URL + '/index.php?url=admin/orders/approvePayment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast(data.error || 'Xác nhận thất bại', 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối', 'error'));
};

// ========== CATEGORIES MODULE ==========
function initCategoriesModule() {
    // Dùng BASE_URL global, không khai báo lại
    
    function loadCategoriesTree() {
        const treeContainer = document.getElementById('categoriesTree');
        if (!treeContainer) {
            console.error('Không tìm thấy #categoriesTree');
            return;
        }
        
        fetch(BASE_URL + '/index.php?url=admin/api/categories')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    renderTree(data.data);
                } else {
                    treeContainer.innerHTML = '<p>Không có dữ liệu</p>';
                }
            })
            .catch(err => {
                console.error('Lỗi load danh mục:', err);
                treeContainer.innerHTML = '<p>Lỗi tải dữ liệu</p>';
            });
    }

    function renderTree(categories) {
        const treeContainer = document.getElementById('categoriesTree');
        if (!treeContainer) return;
        
        let html = '<ul class="category-tree">';
        categories.forEach(parent => {
            html += `<li>
                <div class="category-item">
                    <span><i class="fas ${parent.icon || 'fa-folder'}"></i> <strong>${escapeHtml(parent.name)}</strong></span>
                    <div class="category-actions">
                        <button class="btn-icon edit-cat" data-id="${parent.id}" data-name="${escapeHtml(parent.name)}" data-parent="0" data-icon="${parent.icon || ''}"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon delete-cat" data-id="${parent.id}"><i class="fas fa-trash"></i></button>
                        <button class="btn-icon add-child" data-id="${parent.id}" data-name="${escapeHtml(parent.name)}"><i class="fas fa-plus-circle"></i> Thêm con</button>
                    </div>
                </div>`;
            if (parent.children && parent.children.length) {
                html += '<ul class="child-list">';
                parent.children.forEach(child => {
                    html += `<li>
                        <div class="category-item">
                            <span><i class="fas ${child.icon || 'fa-file-alt'}"></i> ${escapeHtml(child.name)}</span>
                            <div class="category-actions">
                                <button class="btn-icon edit-cat" data-id="${child.id}" data-name="${escapeHtml(child.name)}" data-parent="${parent.id}" data-icon="${child.icon || ''}"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon delete-cat" data-id="${child.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </li>`;
                });
                html += '</ul>';
            }
            html += '</li>';
        });
        html += '</ul>';
        treeContainer.innerHTML = html;
        
        document.querySelectorAll('.edit-cat').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditModal(btn.dataset.id, btn.dataset.name, btn.dataset.parent, btn.dataset.icon);
            });
        });
        document.querySelectorAll('.delete-cat').forEach(btn => {
            btn.addEventListener('click', () => {
                if (confirm('Xóa danh mục này? Sản phẩm liên quan sẽ mất danh mục.')) {
                    deleteCategory(btn.dataset.id);
                }
            });
        });
        document.querySelectorAll('.add-child').forEach(btn => {
            btn.addEventListener('click', () => {
                openAddChildModal(btn.dataset.id, btn.dataset.name);
            });
        });
    }

    function openAddParentModal() {
        document.getElementById('modalTitle').innerText = 'Thêm nhóm lớn';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryIcon').value = '';
        document.getElementById('parentSelectGroup').style.display = 'none';
        document.getElementById('categoryParent').value = '0';
        showCategoryModal();
    }

    function openAddChildModal(parentId, parentName) {
        document.getElementById('modalTitle').innerText = `Thêm danh mục con cho "${parentName}"`;
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryIcon').value = '';
        fetch(BASE_URL + '/index.php?url=admin/api/categories')
            .then(res => res.json())
            .then(data => {
                let options = '<option value="0">-- Chọn nhóm lớn --</option>';
                if (data.success && data.data) {
                    data.data.forEach(parent => {
                        const selected = (parent.id == parentId) ? 'selected' : '';
                        options += `<option value="${parent.id}" ${selected}>${escapeHtml(parent.name)}</option>`;
                    });
                }
                document.getElementById('categoryParent').innerHTML = options;
                document.getElementById('parentSelectGroup').style.display = 'block';
                showCategoryModal();
            });
    }

    function openEditModal(id, name, parentId, icon) {
        document.getElementById('modalTitle').innerText = 'Sửa danh mục';
        document.getElementById('categoryId').value = id;
        document.getElementById('categoryName').value = name;
        document.getElementById('categoryIcon').value = icon;
        if (parentId && parentId != 0) {
            fetch(BASE_URL + '/index.php?url=admin/api/categories')
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="0">-- Chọn nhóm lớn --</option>';
                    if (data.success && data.data) {
                        data.data.forEach(parent => {
                            const selected = (parent.id == parentId) ? 'selected' : '';
                            options += `<option value="${parent.id}" ${selected}>${escapeHtml(parent.name)}</option>`;
                        });
                    }
                    document.getElementById('categoryParent').innerHTML = options;
                    document.getElementById('parentSelectGroup').style.display = 'block';
                    showCategoryModal();
                });
        } else {
            document.getElementById('parentSelectGroup').style.display = 'none';
            document.getElementById('categoryParent').value = '0';
            showCategoryModal();
        }
    }

    function submitCategoryForm(e) {
        e.preventDefault();
        
        if (window.isSubmitting) {
            console.log('Đang xử lý, vui lòng đợi...');
            return;
        }
        window.isSubmitting = true;
        
        const submitBtn = document.querySelector('#categoryForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xử lý...';
        }
        
        const formData = new FormData(e.target);
        const id = document.getElementById('categoryId').value;
        const url = id ? BASE_URL + '/index.php?url=admin/categories/update' : BASE_URL + '/index.php?url=admin/categories/store';
        
        fetch(url, { 
            method: 'POST', 
            body: formData 
        })
        .then(res => {
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return res.text().then(text => {
                    throw new Error('Server error: ' + text.substring(0, 100));
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                hideCategoryModal();
                showToast(data.message, 'success');
                loadCategoriesTree();
                e.target.reset();
            } else {
                showToast(data.error || data.message || 'Lỗi không xác định', 'error');
            }
        })
        .catch(err => {
            console.error('Lỗi chi tiết:', err);
            showToast('Lỗi: ' + err.message, 'error');
        })
        .finally(() => {
            window.isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Lưu';
            }
        });
    }

    function deleteCategory(id) {
        fetch(BASE_URL + '/index.php?url=admin/categories/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                loadCategoriesTree();
            } else {
                showToast(data.error || 'Xóa thất bại', 'error');
            }
        });
    }

    function showCategoryModal() {
        const modal = document.getElementById('categoryModal');
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
    }
    
    function hideCategoryModal() {
        const modal = document.getElementById('categoryModal');
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    const addBtn = document.getElementById('addParentBtn');
    if (addBtn) {
        addBtn.addEventListener('click', openAddParentModal);
    }
    document.querySelectorAll('.close-modal, [data-dismiss="modal"]').forEach(el => {
        el.addEventListener('click', hideCategoryModal);
    });
    document.getElementById('categoryForm')?.addEventListener('submit', submitCategoryForm);
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('categoryModal');
        if (e.target === modal) hideCategoryModal();
    });
    
    loadCategoriesTree();
}

if (document.getElementById('categoriesTree')) {
    initCategoriesModule();
}
// ========== ORDER DETAIL PAGE - KHỞI TẠO RIÊNG ==========
// Chỉ chạy khi đang ở trang chi tiết đơn hàng
(function initOrderDetailPage() {
    // Đợi DOM load xong
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        // Kiểm tra xem có đang ở trang chi tiết đơn hàng không
        const orderStatusSelect = document.getElementById('orderStatus');
        const depositBtn = document.getElementById('processDepositBtn');
        const statusAfterReturnSelects = document.querySelectorAll('.status-after-return');
        
        // Nếu không có phần tử nào của trang detail thì thoát (không ảnh hưởng trang khác)
        if (!orderStatusSelect && !depositBtn && statusAfterReturnSelects.length === 0) {
            return;
        }
        
        // 1. XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
        if (orderStatusSelect) {
            // Lưu giá trị cũ để rollback nếu lỗi
            let previousValue = orderStatusSelect.value;
            
            orderStatusSelect.addEventListener('change', function() {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;
                
                // Hiển thị loading
                const originalText = this.options[this.selectedIndex]?.text;
                this.disabled = true;
                
                fetch(BASE_URL + '/index.php?url=admin/orders/updateStatus', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${orderId}&status=${newStatus}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        previousValue = newStatus;
                        // Reload lại trang để cập nhật các trạng thái khác (deposit_status, payment_status)
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.error || 'Cập nhật thất bại', 'error');
                        // Rollback về giá trị cũ
                        this.value = previousValue;
                    }
                })
                .catch(err => {
                    console.error('Lỗi:', err);
                    showToast('Lỗi kết nối máy chủ', 'error');
                    this.value = previousValue;
                })
                .finally(() => {
                    this.disabled = false;
                });
            });
        }
        
        // 2. XỬ LÝ CẬP NHẬT TÌNH TRẠNG SẢN PHẨM SAU TRẢ (status_after_return)
        if (statusAfterReturnSelects.length > 0) {
            statusAfterReturnSelects.forEach(select => {
                // Lưu giá trị cũ
                let previousValue = select.value;
                
                select.addEventListener('change', function() {
                    const detailId = this.dataset.detailId;
                    const newStatus = this.value;
                    
                    if (!newStatus) {
                        showToast('Vui lòng chọn tình trạng sản phẩm', 'error');
                        this.value = previousValue;
                        return;
                    }
                    
                    // Hiển thị loading
                    this.disabled = true;
                    
                    fetch(BASE_URL + '/index.php?url=admin/orders/updateStatusAfterReturn', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `detail_id=${detailId}&status=${newStatus}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            previousValue = newStatus;
                        } else {
                            showToast(data.error || 'Cập nhật thất bại', 'error');
                            this.value = previousValue;
                        }
                    })
                    .catch(err => {
                        console.error('Lỗi:', err);
                        showToast('Lỗi kết nối máy chủ', 'error');
                        this.value = previousValue;
                    })
                    .finally(() => {
                        this.disabled = false;
                    });
                });
            });
        }
        
        // 3. XỬ LÝ NÚT HOÀN CỌC
        if (depositBtn) {
            depositBtn.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                
                if (!confirm('Xác nhận hoàn tiền cọc cho khách hàng?\n\n⚠️ Lưu ý: Hành động này không thể hoàn tác!')) {
                    return;
                }
                
                // Hiển thị loading
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                
                fetch(BASE_URL + '/index.php?url=admin/orders/processDeposit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `order_id=${orderId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.error || 'Hoàn cọc thất bại', 'error');
                        this.disabled = false;
                        this.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error('Lỗi:', err);
                    showToast('Lỗi kết nối máy chủ', 'error');
                    this.disabled = false;
                    this.innerHTML = originalText;
                });
            });
        }
    }
})();

// ========== BỔ SUNG BỘ LỌC CHO TRANG DANH SÁCH ĐƠN HÀNG ==========
(function initOrderFilters() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        const ordersTable = document.getElementById('ordersTable');
        if (!ordersTable) return;
        
        const searchFiltersDiv = document.querySelector('.search-filters');
        if (searchFiltersDiv && !document.getElementById('filterPaymentStatus')) {
            const filterHtml = `
                <select id="filterPaymentStatus" class="form-control" style="width: 200px; margin-left: 10px;">
                    <option value="all">📋 Tất cả thanh toán</option>
                    <option value="pending_verification">🟡 Chờ xác nhận</option>
                    <option value="paid">✅ Đã thanh toán</option>
                    <option value="unpaid">⏳ Chưa thanh toán</option>
                </select>
                <select id="filterOrderStatus" class="form-control" style="width: 180px; margin-left: 10px;">
                    <option value="all">📦 Tất cả đơn hàng</option>
                    <option value="pending">⏰ Chờ duyệt</option>
                    <option value="confirmed">✅ Đã xác nhận</option>
                    <option value="renting">🚚 Đang thuê</option>
                    <option value="returned">📦 Đã trả</option>
                    <option value="cancelled">❌ Đã hủy</option>
                </select>
                <select id="filterDepositStatus" class="form-control" style="width: 180px; margin-left: 10px;">
                    <option value="all">💰 Tất cả cọc</option>
                    <option value="pending">⏳ Chưa hoàn</option>
                    <option value="pending_return">🔄 Chờ hoàn cọc</option>
                    <option value="refunded_full">✅ Đã hoàn toàn bộ</option>
                    <option value="refunded_partial">⚠️ Hoàn một phần</option>
                </select>
                <button id="resetFilters" class="btn btn-secondary" style="margin-left: 10px;">🔄 Xóa lọc</button>
            `;
            searchFiltersDiv.insertAdjacentHTML('beforeend', filterHtml);
        }
        
        // LƯU HÀM RENDER CŨ
        const originalRender = window.renderOrdersTable;
        
        // GHI ĐÈ (KHÔNG KHAI BÁO LẠI BIẾN)
        window.renderOrdersTable = function() {
            const searchTerm = document.getElementById('searchOrder')?.value.toLowerCase() || '';
            const paymentFilter = document.getElementById('filterPaymentStatus')?.value || 'all';
            const orderFilter = document.getElementById('filterOrderStatus')?.value || 'all';
            const depositFilter = document.getElementById('filterDepositStatus')?.value || 'all';
            
            let filtered = ordersData.filter(order => {
                const matchSearch = order.id.toString().includes(searchTerm) || 
                    (order.user_name || '').toLowerCase().includes(searchTerm) ||
                    (order.email || '').toLowerCase().includes(searchTerm) ||
                    (order.phone || '').toLowerCase().includes(searchTerm);
                
                const matchPayment = paymentFilter === 'all' || order.payment_status === paymentFilter;
                const matchOrder = orderFilter === 'all' || order.status === orderFilter;
                const matchDeposit = depositFilter === 'all' || order.deposit_status === depositFilter;
                
                return matchSearch && matchPayment && matchOrder && matchDeposit;
            });
            
            document.getElementById('orderCount').innerText = `${filtered.length} đơn hàng`;
            
            let html = '';
            if (filtered.length > 0) {
                filtered.forEach(order => {
                    const rowClass = (order.payment_status === 'pending_verification') ? 'pending-verification-row' : '';
                    
                    html += `<tr class="${rowClass}">
                        <td><strong>#${order.id}</strong></td>
                        <td><strong>${escapeHtml(order.user_name || 'N/A')}</strong><br><small>${escapeHtml(order.email || '')}</small></td>
                        <td>${escapeHtml(order.phone || '---')}</td>
                        <td>${order.rent_date ? new Date(order.rent_date).toLocaleDateString('vi-VN') : '---'}</td>
                        <td>${order.return_date ? new Date(order.return_date).toLocaleDateString('vi-VN') : '---'}</td>
                        <td><strong>${formatNumber(order.discount_price || order.total_price)}đ</strong></td>
                        <td><span class="status-badge status-${order.status}">${orderStatusLabels[order.status] || order.status}</span></td>
                        <td>
                            <span class="status-badge payment-${order.payment_status}">${paymentLabels[order.payment_status] || order.payment_status}</span>
                            ${order.payment_status === 'pending_verification' ? `<button class="btn-confirm-payment" onclick="confirmPayment(${order.id})">✓ Xác nhận</button>` : ''}
                        </td>
                        <td><span class="status-badge deposit-${order.deposit_status}">${depositLabels[order.deposit_status] || order.deposit_status}</span></td>
                        <td><button class="btn-icon view-order" onclick="viewOrderDetail(${order.id})"><i class="fas fa-eye"></i></button></td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="10" style="text-align: center;">📭 Không tìm thấy đơn hàng nào</td><\/tr>';
            }
            document.querySelector('#ordersTable tbody').innerHTML = html;
        };
        
        // Gắn sự kiện
        const paymentFilter = document.getElementById('filterPaymentStatus');
        const orderFilter = document.getElementById('filterOrderStatus');
        const depositFilter = document.getElementById('filterDepositStatus');
        const resetBtn = document.getElementById('resetFilters');
        const searchInput = document.getElementById('searchOrder');
        
        if (paymentFilter) paymentFilter.addEventListener('change', () => window.renderOrdersTable());
        if (orderFilter) orderFilter.addEventListener('change', () => window.renderOrdersTable());
        if (depositFilter) depositFilter.addEventListener('change', () => window.renderOrdersTable());
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (paymentFilter) paymentFilter.value = 'all';
                if (orderFilter) orderFilter.value = 'all';
                if (depositFilter) depositFilter.value = 'all';
                window.renderOrdersTable();
            });
        }
    }
})();
function editPromotion(id, code, discount, minOrder, usageLimit, startDate, endDate) {
    // Mở modal và điền dữ liệu vào form
    document.getElementById('promoCode').value = code;
    document.getElementById('promoDiscount').value = discount;
    document.getElementById('promoMinOrder').value = minOrder;
    document.getElementById('promoUsageLimit').value = usageLimit;
    document.getElementById('promoStart').value = startDate?.replace(' ', 'T');
    document.getElementById('promoEnd').value = endDate?.replace(' ', 'T');
    
    // Thêm hidden input để biết là đang sửa
    let editInput = document.getElementById('editId');
    if (!editInput) {
        editInput = document.createElement('input');
        editInput.type = 'hidden';
        editInput.id = 'editId';
        editInput.name = 'id';
        document.getElementById('promoForm').appendChild(editInput);
    }
    editInput.value = id;
    
    // Đổi tiêu đề modal
    document.querySelector('#promoModal .modal-header h2').innerHTML = '<i class="fas fa-edit"></i> Sửa mã giảm giá';
    
    // Mở modal
    document.getElementById('promoModal').style.display = 'block';
}
// ========== PROMOTIONS MANAGEMENT ==========
// Xử lý nút Sửa trong trang khuyến mãi
function attachPromotionEvents() {
    // Nút Sửa
    document.querySelectorAll('.edit-promo').forEach(btn => {
        btn.onclick = () => editPromotion(
            btn.dataset.id,
            btn.dataset.code,
            btn.dataset.discount,
            btn.dataset.minOrder,
            btn.dataset.usageLimit,
            btn.dataset.start,
            btn.dataset.end
        );
    });
    
    // Nút Xóa (nếu chưa có)
    document.querySelectorAll('.delete-promo').forEach(btn => {
        btn.onclick = () => deletePromotion(btn.dataset.id, btn.dataset.code);
    });
}
// ========== PROMOTIONS MANAGEMENT ==========
let promotionsData = [];

function loadPromotions() {
    fetch(BASE_URL + '/index.php?url=admin/api/promotions')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                promotionsData = data.data;
                renderPromotionsTable();
            } else {
                document.querySelector('#promotionsTable tbody').innerHTML = '<tr><td colspan="11" style="text-align: center;">❌ Lỗi tải dữ liệu</td></tr>';
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            document.querySelector('#promotionsTable tbody').innerHTML = '<tr><td colspan="11" style="text-align: center;">❌ Lỗi kết nối</td></tr>';
        });
}

function renderPromotionsTable() {
    const searchTerm = document.getElementById('searchPromo')?.value.toLowerCase() || '';
    
    let filtered = promotionsData.filter(p => p.code.toLowerCase().includes(searchTerm));
    
    let html = '';
    if(filtered.length > 0) {
        filtered.forEach(p => {
            let statusClass = p.status === 'active' ? 'active' : (p.status === 'upcoming' ? 'upcoming' : 'expired');
            let statusText = p.status === 'active' ? '🟢 Đang diễn ra' : (p.status === 'upcoming' ? '🔜 Sắp diễn ra' : '🔚 Đã kết thúc');
            
            let minOrder = (p.min_order_value && p.min_order_value > 0) ? formatNumber(p.min_order_value) + 'đ' : '0đ (không giới hạn)';
            let usageLimit = (p.usage_limit && p.usage_limit > 0) ? p.usage_limit : '∞';
            let usedCount = p.used_count || 0;
            let remaining = (p.usage_limit && p.usage_limit > 0) ? p.usage_limit - usedCount : '∞';
            
            html += `<tr>
                <td>${p.id}</td>
                <td><strong class="discount-badge">${p.code}</strong></td>
                <td><span class="discount-percent">${p.discount_percent}%</span></td>
                <td>${minOrder}</td>
                <td>${usageLimit}</td>
                <td>${usedCount}</td>
                <td>${remaining}</td>
                <td>${p.start_date || 'N/A'}</td>
                <td>${p.end_date || 'N/A'}</td>
                <td><span class="status-badge status-${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn-icon edit-promo" data-id="${p.id}" data-code="${p.code}" data-discount="${p.discount_percent}" data-min-order="${p.min_order_value}" data-usage-limit="${p.usage_limit}" data-start="${p.start_date}" data-end="${p.end_date}" title="Sửa"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon delete-promo" data-id="${p.id}" data-code="${p.code}" title="Xóa"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="11" style="text-align: center;">📭 Không có mã giảm giá nào</td></tr>';
    }
    document.querySelector('#promotionsTable tbody').innerHTML = html;
    attachPromotionEvents();
}

function attachPromotionEvents() {
    document.querySelectorAll('.delete-promo').forEach(btn => {
        btn.onclick = () => deletePromotion(btn.dataset.id, btn.dataset.code);
    });
    
    document.querySelectorAll('.edit-promo').forEach(btn => {
        btn.onclick = () => editPromotion(
            btn.dataset.id,
            btn.dataset.code,
            btn.dataset.discount,
            btn.dataset.minOrder,
            btn.dataset.usageLimit,
            btn.dataset.start,
            btn.dataset.end
        );
    });
}

function editPromotion(id, code, discount, minOrder, usageLimit, startDate, endDate) {
    document.getElementById('editId').value = id;
    document.getElementById('updateFlag').value = 'update';
    document.getElementById('promoCode').value = code;
    document.getElementById('promoDiscount').value = discount;
    document.getElementById('promoMinOrder').value = minOrder || 0;
    document.getElementById('promoUsageLimit').value = usageLimit || 0;
    document.getElementById('promoStart').value = startDate ? startDate.replace(' ', 'T') : '';
    document.getElementById('promoEnd').value = endDate ? endDate.replace(' ', 'T') : '';
    
    document.querySelector('#promoModal .modal-header h2').innerHTML = '<i class="fas fa-edit"></i> Sửa mã giảm giá';
    document.getElementById('promoModal').style.display = 'block';
}

function resetPromotionModal() {
    document.getElementById('promoForm').reset();
    document.getElementById('editId').value = '';
    document.getElementById('updateFlag').value = '';
    document.getElementById('promoMinOrder').value = 0;
    document.getElementById('promoUsageLimit').value = 0;
    document.querySelector('#promoModal .modal-header h2').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm mã giảm giá';
}

function deletePromotion(id, code) {
    if(confirm(`Bạn có chắc muốn xóa mã "${code}"?`)) {
        fetch(BASE_URL + '/index.php?url=admin/promotions/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        }).then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast('Xóa mã thành công', 'success');
                loadPromotions();
            } else {
                alert('Xóa thất bại');
            }
        });
    }
}

// Khởi tạo promotion page
if (document.getElementById('promotionsTable')) {
    // Modal events
    document.getElementById('openModalBtn').onclick = () => {
        resetPromotionModal();
        document.getElementById('promoModal').style.display = 'block';
    };
    
    document.getElementById('promoForm').onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const isUpdate = document.getElementById('updateFlag').value === 'update';
        const url = isUpdate ? BASE_URL + '/index.php?url=admin/promotions/update' : BASE_URL + '/index.php?url=admin/promotions/store';
        
        fetch(url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('promoModal').style.display = 'none';
                    showToast(data.message, 'success');
                    resetPromotionModal();
                    loadPromotions();
                } else {
                    alert(data.error || 'Thao tác thất bại');
                }
            });
    };
    
    document.querySelectorAll('#promoModal .close, #promoModal .close-modal').forEach(el => {
        el.onclick = () => {
            document.getElementById('promoModal').style.display = 'none';
            resetPromotionModal();
        };
    });
    
    window.onclick = function(e) {
        const modal = document.getElementById('promoModal');
        if (e.target === modal) {
            modal.style.display = 'none';
            resetPromotionModal();
        }
    };
    
    document.getElementById('searchPromo')?.addEventListener('keyup', renderPromotionsTable);
    
    loadPromotions();
}
// ========== ORDERS PAGE - HEROICONS FILTER & BATCH ==========


function updateSelectedCount() {
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.innerHTML = `Đã chọn ${selectedOrders.length} đơn`;
}
function updateSelectedCount() {
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.innerHTML = `Đã chọn ${selectedOrders.length} đơn`;
}

function attachCheckboxEvents() {
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        cb.onchange = function() {
            const id = parseInt(this.dataset.id);
            if (this.checked) {
                if (!selectedOrders.includes(id)) selectedOrders.push(id);
            } else {
                selectedOrders = selectedOrders.filter(oid => oid !== id);
                const selectAll = document.getElementById('selectAllOrders');
                if (selectAll) selectAll.checked = false;
            }
            updateSelectedCount();
        };
    });
}

function initOrderFilters() {
    document.querySelectorAll('.filter-chips .chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            if (typeof renderOrdersTable === 'function') renderOrdersTable();
        });
    });
}

// Select all orders
document.getElementById('selectAllOrders')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    if (this.checked) {
        selectedOrders = [];
        checkboxes.forEach(cb => {
            cb.checked = true;
            const id = parseInt(cb.dataset.id);
            if (!selectedOrders.includes(id)) selectedOrders.push(id);
        });
    } else {
        checkboxes.forEach(cb => cb.checked = false);
        selectedOrders = [];
    }
    updateSelectedCount();
});

// Clear selection
document.getElementById('clearSelectionBtn')?.addEventListener('click', function() {
    selectedOrders = [];
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('selectAllOrders');
    if (selectAll) selectAll.checked = false;
    updateSelectedCount();
});

// Batch approve
document.getElementById('batchApproveBtn')?.addEventListener('click', function() {
    if (selectedOrders.length === 0) {
        showToast('Vui lòng chọn ít nhất một đơn hàng', 'error');
        return;
    }
    
    if (confirm(`Xác nhận duyệt ${selectedOrders.length} đơn hàng đã chọn?`)) {
        fetch(BASE_URL + '/index.php?url=admin/orders/batchApprove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_ids: selectedOrders })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast(`Đã duyệt ${data.approved} đơn, bỏ qua ${data.skipped} đơn`, 'success');
                selectedOrders = [];
                document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
                if (document.getElementById('selectAllOrders')) document.getElementById('selectAllOrders').checked = false;
                updateSelectedCount();
                if (typeof loadOrders === 'function') loadOrders();
            } else {
                showToast(data.error || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => showToast('Lỗi kết nối', 'error'));
    }
});

// Batch cancel
document.getElementById('batchCancelBtn')?.addEventListener('click', function() {
    if (selectedOrders.length === 0) {
        showToast('Vui lòng chọn ít nhất một đơn hàng', 'error');
        return;
    }
    
    if (confirm(`Xác nhận hủy ${selectedOrders.length} đơn hàng đã chọn?`)) {
        fetch(BASE_URL + '/index.php?url=admin/orders/batchCancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_ids: selectedOrders })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast(`Đã hủy ${data.cancelled} đơn`, 'success');
                selectedOrders = [];
                document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
                if (document.getElementById('selectAllOrders')) document.getElementById('selectAllOrders').checked = false;
                updateSelectedCount();
                if (typeof loadOrders === 'function') loadOrders();
            } else {
                showToast(data.error || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => showToast('Lỗi kết nối', 'error'));
    }
});

// Reset filters
document.getElementById('resetFilters')?.addEventListener('click', function() {
    document.getElementById('searchOrder').value = '';
    document.querySelectorAll('.filter-chips .chip').forEach(chip => {
        if (chip.dataset.value === 'all') {
            chip.classList.add('active');
        } else {
            chip.classList.remove('active');
        }
    });
    selectedOrders = [];
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
    if (document.getElementById('selectAllOrders')) document.getElementById('selectAllOrders').checked = false;
    updateSelectedCount();
    if (typeof renderOrdersTable === 'function') renderOrdersTable();
});

// Search
document.getElementById('searchOrder')?.addEventListener('keyup', function() {
    if (typeof renderOrdersTable === 'function') renderOrdersTable();
});
function loadOrders() {
    fetch(BASE_URL + '/index.php?url=admin/api/orders')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                ordersData = data.data;
                renderOrdersTable();
            } else {
                console.error('Lỗi load orders:', data);
            }
        })
        .catch(err => console.error('Lỗi fetch orders:', err));
}

// ========== GLOBAL FUNCTIONS ==========
function formatNumber(num) {
    if (!num) return '0';
    return new Intl.NumberFormat('vi-VN').format(num);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ========== Toggle sidebar ==========
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
