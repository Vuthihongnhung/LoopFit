﻿// ========== HÀM TOÀN CỤC (GLOBAL FUNCTIONS) ==========
/* ===== Định dạng số theo kiểu Việt Nam (phân cách hàng nghìn bằng dấu chấm, thập phân là dấu phẩy) ===== */
function formatNumber(num) {
    if (!num) return '0';
    return new Intl.NumberFormat('vi-VN').format(num);
}

/* ===== Định dạng số theo kiểu Việt Nam (phân cách hàng nghìn bằng dấu chấm, thập phân là dấu phẩy) ===== */
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

/* ===== Hiển thị thông báo dạng toast (hộp thông báo tạm thời) ===== */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

/* ===== Xác nhận trước khi xóa (dùng confirm mặc định của trình duyệt) ===== */
function confirmDelete(message, callback) {
    if (confirm(message)) callback();
}

// ========== TOGGLE SIDEBAR MOBILE ==========
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

// ==================== PRODUCT MANAGEMENT ====================
// childMap sẽ được định nghĩa từ view (products/index.php)
document.addEventListener('DOMContentLoaded', function() {
    // --- Biến toàn cục ---
    const BASE_URL = window.BASE_URL || '/LoopFit/public';
    let allProducts = [];
    let childCategoriesCache = {}; 
    
    function showToast(message, type = 'info') {
        if (typeof toastr !== 'undefined') {
            if (type === 'success') toastr.success(message);
            else if (type === 'error') toastr.error(message);
            else toastr.info(message);
        } else {
            alert(message);
        }
    }
    
    /* ===== Render bảng sản phẩm ===== */
    function renderProducts(products) {
        const tbody = document.querySelector('#productsTable tbody');
        if (!tbody) return;
        if (!products.length) {
            tbody.innerHTML = '<tr><td colspan="15" class="text-center">📦 Không có sản phẩm nào</td></tr>';
            return;
        }
        
        let html = '';
        products.forEach(p => {
            const id = p.id;
            const name = p.name || '';
            const price = Number(p.price || 0);
            const discount = p.discount_percent || 0;
            const depositPrice = Number(p.deposit_price || 0);
            const stock = Number(p.stock || 0);
            const rentedQuantity = Number((p.current_rented ?? p.rented_quantity) || 0);
            const available = stock - rentedQuantity;
            
            // Trạng thái
            let statusText = '', statusClass = '';
            if (available > 0) {
                statusText = 'Còn hàng';
                statusClass = 'status-instock';
            } else if (rentedQuantity > 0) {
                statusText = 'Đang được thuê';
                statusClass = 'status-rented';
            } else {
                statusText = 'Hết hàng';
                statusClass = 'status-outstock';
            }
            
            const categoryName = p.category_name || 'Chưa phân loại';
            const gender = p.gender === 'male' ? 'Nam' : (p.gender === 'female' ? 'Nữ' : 'Unisex');
            const rentedCount = Number(p.rented_count || 0);
            
            let imageUrl = p.image_url || p.image || '';
            if (!imageUrl) imageUrl = BASE_URL + '/uploads/default.png';
            else if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) imageUrl = BASE_URL + '/uploads/' + imageUrl;
            
            html += `<tr data-gender="${p.gender || 'unisex'}" data-category-id="${p.category_id || 0}" data-product-name="${name.toLowerCase()}">
                <td>${id}</td>
                <td><img src="${imageUrl}" width="50" height="50" style="object-fit:cover; border-radius:8px;"></td>
                <td><strong>${escapeHtml(name)}</strong></td>
                <td>${formatNumber(price)}₫</td>
                <td>${discount}%</td>
                <td>${formatNumber(depositPrice)}₫</td>
                <td>${stock}</td>
                <td>${rentedQuantity}</td>
                <td>${available}</td>
                <td class="${statusClass}">${statusText} ${available > 0 ? `<span class="stock-badge">(còn ${available})</span>` : (rentedQuantity > 0 ? `<span class="rented-badge">(${rentedQuantity} đang thuê)</span>` : '')}</td>
                <td>${escapeHtml(categoryName)}</td>
                <td>${gender}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-info view-stock-details" data-id="${id}" title="Chi tiết kho">
                        👁 Chi tiết kho
                    </button>
                </td>
                <td>${rentedCount}</td>
                <td class="actions">
                    <a href="${BASE_URL}/admin/products/edit?id=${id}" class="btn-icon edit" title="Sửa"><i class="fas fa-edit"></i></a>
                    <button class="btn-icon delete" data-id="${id}" title="Xóa"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
        
        // Gắn sự kiện xóa
        document.querySelectorAll('#productsTable .delete').forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                const productId = btn.getAttribute('data-id');
                deleteProduct(productId);
            };
        });

        // Gắn sự kiện xem chi tiết kho
        document.querySelectorAll('#productsTable .view-stock-details').forEach(btn => {
            btn.onclick = async (e) => {
                e.preventDefault();
                await loadInventoryDetails(btn.getAttribute('data-id'));
            };
        });
    }

    async function loadInventoryDetails(productId) {
        const inventoryModal = document.getElementById('inventoryModal');
        const inventoryTableBody = document.querySelector('#inventoryDetailTable tbody');
        const inventoryTotal = document.getElementById('inventoryTotal');
        const inventoryProductName = document.getElementById('inventoryProductName');
        if (!inventoryModal || !inventoryTableBody || !inventoryTotal || !inventoryProductName) return;

        try {
            const res = await fetch(`${BASE_URL}/index.php?url=admin/api/product&id=${productId}`);
            const data = await res.json();
            if (!data.success) {
                showToast(data.error || 'Lỗi khi tải chi tiết kho', 'error');
                return;
            }

            const product = data.data;
            inventoryProductName.textContent = `Sản phẩm: ${product.name || ''}`;
            inventoryTableBody.innerHTML = '';
            let total = 0;

            if (product.variants && product.variants.length) {
                product.variants.forEach(variant => {
                    const stock = Number(variant.stock || 0);
                    const rentedQuantity = Number(variant.rented_quantity || 0);
                    const available = stock - rentedQuantity;
                    const statusClass = available <= 0 ? 'status-outstock' : (available < 2 ? 'status-rented' : 'status-instock');
                    const status = available <= 0 ? '❌ Hết hàng' : (available < 2 ? '⚠️ Sắp hết hàng' : '✅ Còn hàng');
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${variant.color}</td>
                        <td>${variant.size}</td>
                        <td>${stock}</td>
                        <td>${rentedQuantity}</td>
                        <td>${available}</td>
                        <td class="${statusClass}">${status}</td>
                    `;
                    inventoryTableBody.appendChild(row);
                    total += stock;
                });
            } else {
                const rentedQuantity = Number(product.rented_quantity || 0);
                const available = Number(product.stock || 0) - rentedQuantity;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="6" style="text-align:center;color:#6b7280;">Chưa có biến thể. Tổng tồn kho hiện tại: ${product.stock}, đang thuê: ${rentedQuantity}, còn lại: ${available}</td>
                `;
                inventoryTableBody.appendChild(row);
                total = Number(product.stock || 0);
            }

            inventoryTotal.textContent = `Tổng tồn kho: ${total}`;
            inventoryModal.style.display = 'flex';
        } catch (err) {
            console.error(err);
            showToast('Lỗi khi tải chi tiết kho', 'error');
        }
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
    function formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }
    
    /* ===== Load danh sách sản phẩm từ API ===== */
    function loadProducts() {
        fetch(BASE_URL + '/admin/api/products')
            .then(res => res.json())
            .then(data => {
                if (data.success && Array.isArray(data.data)) {
                    allProducts = data.data;
                    renderProducts(allProducts);
                } else {
                    console.error('API trả về không đúng cấu trúc', data);
                    renderProducts([]);
                }
            })
            .catch(err => {
                console.error('Lỗi tải sản phẩm:', err);
                renderProducts([]);
                showToast('Lỗi tải danh sách sản phẩm', 'error');
            });
    }
    
    /* ===== Xóa sản phẩm ===== */
    function deleteProduct(id) {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
        fetch(BASE_URL + '/admin/products/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Xóa sản phẩm thành công', 'success');
                loadProducts(); // reload danh sách
            } else {
                showToast(data.error || 'Xóa thất bại', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Lỗi kết nối khi xóa', 'error');
        });
    }
    
    /* ===== Lấy danh sách danh mục con ===== */
    async function getChildCategoryIds(parentId) {
        if (childCategoriesCache[parentId]) return childCategoriesCache[parentId];
        try {
            const res = await fetch(`${BASE_URL}/admin/api/categories?parent_id=${parentId}`);
            const data = await res.json();
            let childIds = [];
            if (data.success && Array.isArray(data.categories)) {
                childIds = data.categories.map(cat => String(cat.id));
            }
            childCategoriesCache[parentId] = childIds;
            return childIds;
        } catch (err) {
            console.error('Lỗi lấy danh mục con:', err);
            return [];
        }
    }
    
    /* ===== Lọc sản phẩm (tìm kiếm, giới tính, danh mục cha) ===== */
    async function filterProducts() {
        const searchTerm = document.getElementById('searchProduct')?.value.trim().toLowerCase() || '';
        const gender = document.getElementById('filterGender')?.value || '';
        const groupId = document.getElementById('filterCategoryGroup')?.value || '';
        
        let filtered = [...allProducts];
        
        // Lọc theo tên
        if (searchTerm) {
            filtered = filtered.filter(p => (p.name || '').toLowerCase().includes(searchTerm));
        }
        // Lọc theo giới tính
        if (gender) {
            filtered = filtered.filter(p => p.gender === gender);
        }
        // Lọc theo nhóm danh mục (cha)
        if (groupId) {
            const childIds = await getChildCategoryIds(groupId);
            filtered = filtered.filter(p => childIds.includes(String(p.category_id)));
        }
        
        renderProducts(filtered);
    }
    
    /* ===== Reset bộ lọc ===== */
    function resetFilters() {
        const search = document.getElementById('searchProduct');
        const gender = document.getElementById('filterGender');
        const group = document.getElementById('filterCategoryGroup');
        if (search) search.value = '';
        if (gender) gender.value = '';
        if (group) group.value = '';
        filterProducts();
    }
    
    // --- Gắn sự kiện ---
    const searchInput = document.getElementById('searchProduct');
    const genderSelect = document.getElementById('filterGender');
    const groupSelect = document.getElementById('filterCategoryGroup');
    const resetBtn = document.getElementById('resetFilter');
    
    if (searchInput) searchInput.addEventListener('keyup', filterProducts);
    if (genderSelect) genderSelect.addEventListener('change', filterProducts);
    if (groupSelect) groupSelect.addEventListener('change', filterProducts);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);
    
    // Khởi tạo
    loadProducts();
});

// ========== CATEGORIES MODULE ==========
    function initCategoriesModule() {
        /* ===== Tải cây danh mục từ API và render ===== */
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

        /* ===== Render cây danh mục dạng lồng nhau dạng ===== */
        function renderTree(categories) {
        const treeContainer = document.getElementById('categoriesTree');
        if (!treeContainer) return;
        let html = '<ul class="lf-categories-tree">';
        categories.forEach(parent => {
            html += `<li>
                <div class="lf-category-item">
                    <span><i class="fas ${parent.icon || 'fa-folder'}"></i> <strong>${escapeHtml(parent.name)}</strong></span>
                    <div class="lf-category-actions">
                        <button class="lf-btn-icon lf-edit-cat" data-id="${parent.id}" data-name="${escapeHtml(parent.name)}" data-parent="0" data-icon="${parent.icon || ''}"><i class="fas fa-edit"></i></button>
                        <button class="lf-btn-icon lf-delete-cat" data-id="${parent.id}"><i class="fas fa-trash"></i></button>
                        <button class="lf-btn-icon lf-add-child" data-id="${parent.id}" data-name="${escapeHtml(parent.name)}"><i class="fas fa-plus-circle"></i> Thêm con</button>
                    </div>
                </div>`;
            if (parent.children && parent.children.length) {
                html += '<ul class="lf-child-list">';
                parent.children.forEach(child => {
                    html += `<li>
                        <div class="lf-category-item">
                            <span><i class="fas ${child.icon || 'fa-file-alt'}"></i> ${escapeHtml(child.name)}</span>
                            <div class="lf-category-actions">
                                <button class="lf-btn-icon lf-edit-cat" data-id="${child.id}" data-name="${escapeHtml(child.name)}" data-parent="${parent.id}" data-icon="${child.icon || ''}"><i class="fas fa-edit"></i></button>
                                <button class="lf-btn-icon lf-delete-cat" data-id="${child.id}"><i class="fas fa-trash"></i></button>
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

        // Gắn sự kiện (dùng class mới)
        document.querySelectorAll('.lf-edit-cat').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditModal(btn.dataset.id, btn.dataset.name, btn.dataset.parent, btn.dataset.icon);
            });
        });
        document.querySelectorAll('.lf-delete-cat').forEach(btn => {
            btn.addEventListener('click', () => {
                if (confirm('Xóa danh mục này? Sản phẩm liên quan sẽ mất danh mục.')) {
                    deleteCategory(btn.dataset.id);
                }
            });
        });
        document.querySelectorAll('.lf-add-child').forEach(btn => {
            btn.addEventListener('click', () => {
                openAddChildModal(btn.dataset.id, btn.dataset.name);
            });
        });
    }
    /* ===== Mở modal thêm danh mục cấp cha (parent_id = 0) ===== */
    function openAddParentModal() {
        document.getElementById('modalTitle').innerText = 'Thêm nhóm lớn';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryIcon').value = '';
        document.getElementById('parentSelectGroup').style.display = 'none';
        document.getElementById('categoryParent').value = '0';
        showCategoryModal();
    }

    /* ===== Mở modal thêm danh mục con (truyền parentId và tên cha) ===== */
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

    /* ===== Mở modal sửa danh mục (điền dữ liệu hiện tại) ===== */
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

    /* ===== Xử lý submit form thêm/sửa danh mục (gửi dữ liệu qua AJAX) ===== */
    function submitCategoryForm(e) {
        e.preventDefault();
        if (window.isSubmitting) return;
        window.isSubmitting = true;
        const submitBtn = document.querySelector('#categoryForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xử lý...';
        }
        const formData = new FormData(e.target);
        const id = document.getElementById('categoryId').value;
        const url = id ? BASE_URL + '/index.php?url=admin/categories/update' : BASE_URL + '/index.php?url=admin/categories/store';
        fetch(url, { method: 'POST', body: formData })
            .then(res => {
                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return res.text().then(text => { throw new Error('Server error: ' + text.substring(0, 100)); });
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

    /* ===== Xóa danh mục ===== */
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

    /* ===== Hiển thị modal ===== */
    function showCategoryModal() {
        const modal = document.getElementById('categoryModal');
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
    }

    /* ===== Ẩn modal ===== */
    function hideCategoryModal() {
        const modal = document.getElementById('categoryModal');
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    const addBtn = document.getElementById('addParentBtn');
    if (addBtn) addBtn.addEventListener('click', openAddParentModal);
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