document.addEventListener('DOMContentLoaded', function() {
    let bulkPromotionsData = [];
    let productsData = [];
    let categoriesData = [];
    let selectedProducts = [];

    // DOM elements
    const searchInput = document.getElementById('searchBulkPromo');
    const tbody = document.querySelector('#bulkPromotionsTable tbody');
    const openModalBtn = document.getElementById('openBulkPromoModalBtn');
    const bulkPromoModal = document.getElementById('bulkPromoModal');
    const bulkPromoForm = document.getElementById('bulkPromoForm');
    const bulkPromoId = document.getElementById('bulkPromoId');
    const bulkPromoUpdateFlag = document.getElementById('bulkPromoUpdateFlag');
    const bulkPromoName = document.getElementById('bulkPromoName');
    const bulkPromoScopeType = document.getElementById('bulkPromoScopeType');
    const promotionTypeRadios = document.querySelectorAll('input[name="promotion_type"]');
    const percentGroup = document.getElementById('bulk-percent-group');
    const fixedPriceGroup = document.getElementById('bulk-fixed-price-group');
    const categoryGroup = document.getElementById('bulk-category-group');
    const productsGroup = document.getElementById('bulk-products-group');
    const bulkPromoCategoryId = document.getElementById('bulkPromoCategoryId');
    const bulkPromoProductsList = document.getElementById('bulkPromoProductsList');
    const modalTitle = document.querySelector('#bulkPromoModal .modal-header h2');
    const closeButtons = document.querySelectorAll('#bulkPromoModal .close, #bulkPromoModal .close-modal');

    function formatNumber(num) {
        if (num === null || num === undefined) return '0';
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('vi-VN');
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

    /* ===== Hiển thị trạng thái của chương trình khuyến mãi ===== */
    function getStatusBadge(status, startDate, endDate) {
        const now = new Date();
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (now < start) {
            return '<span class="status-badge status-upcoming">🔜 Sắp diễn ra</span>';
        } else if (now > end) {
            return '<span class="status-badge status-expired">🔚 Đã kết thúc</span>';
        } else if (status == 1) {
            return '<span class="status-badge status-active">🟢 Đang diễn ra</span>';
        } else {
            return '<span class="status-badge">🔴 Bị vô hiệu</span>';
        }
    }

    function getPromotionTypeLabel(type) {
        return type === 'percentage' ? 'Giảm %' : 'Đồng giá';
    }

    function getScopeTypeLabel(type) {
        const labels = {
            'all_products': 'Toàn bộ sản phẩm',
            'category': 'Một danh mục',
            'specific_products': 'Sản phẩm cụ thể'
        };
        return labels[type] || type;
    }

    /* ===== Load danh sách danh mục ===== */
    function loadCategories() {
        fetch(BASE_URL + '/index.php?url=admin/api/categories')
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    categoriesData = data.data;
                    populateCategoryDropdown();
                }
            })
            .catch(err => {
                console.warn('⚠️ Không thể tải danh mục:', err);
                bulkPromoCategoryId.innerHTML = '<option value="">-- Không thể tải danh mục --</option>';
            });
    }

   function populateCategoryDropdown() {
    let html = '<option value="">-- Chọn danh mục --</option>';
    
    // Duyệt qua từng danh mục cha
    categoriesData.forEach(parent => {
        html += `<option value="${parent.id}"><strong>📁 ${escapeHtml(parent.name)}</strong></option>`;
        
        // Duyệt danh mục con
        if (parent.children && parent.children.length > 0) {
            parent.children.forEach(child => {
                html += `<option value="${child.id}">&nbsp;&nbsp;&nbsp;└─ ${escapeHtml(child.name)}</option>`;
            });
        }
    });
    
    bulkPromoCategoryId.innerHTML = html;
}

    /* ===== Load danh sách sản phẩm ===== */
    function loadProducts() {
        fetch(BASE_URL + '/index.php?url=admin/api/products')
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    productsData = data.data;
                    populateProductsList();
                }
            })
            .catch(err => {
                console.warn('⚠️ Không thể tải sản phẩm:', err);
                if (bulkPromoProductsList) {
                    bulkPromoProductsList.innerHTML = '<p style="text-align: center; color: #999;">❌ Không thể tải danh sách sản phẩm</p>';
                }
            });
    }

    function populateProductsList() {
        if (!bulkPromoProductsList) return;
        
        let html = '';
        productsData.forEach(product => {
            const isChecked = selectedProducts.includes(product.id) ? 'checked' : '';
            html += `
                <div style="display: flex; align-items: center; gap: 8px; padding: 8px; border-bottom: 1px solid #f0f0f0;">
                    <input type="checkbox" class="product-checkbox" value="${product.id}" ${isChecked} 
                           data-name="${escapeHtml(product.name)}" 
                           onchange="handleProductCheckboxChange(this)">
                    <label style="flex: 1; cursor: pointer; margin: 0;">
                        ${escapeHtml(product.name)}
                        <br>
                        <small style="color: #999;">ID: ${product.id} | Giá: ${formatNumber(product.price)}đ</small>
                    </label>
                </div>
            `;
        });
        
        if (html === '') {
            html = '<p style="text-align: center; color: #999;">📭 Không có sản phẩm nào</p>';
        }
        
        bulkPromoProductsList.innerHTML = html;
    }

    /* ===== Hàm global để xử lý checkbox thay đổi ===== */
    window.handleProductCheckboxChange = function(checkbox) {
        const productId = parseInt(checkbox.value);
        if (checkbox.checked) {
            if (!selectedProducts.includes(productId)) {
                selectedProducts.push(productId);
            }
        } else {
            selectedProducts = selectedProducts.filter(id => id !== productId);
        }
    };


    /* ===== Xử lý hiển/ẩn input theo loại khuyến mãi ===== */
    promotionTypeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'percentage') {
            percentGroup.style.display = 'block';
            fixedPriceGroup.style.display = 'none';
            // Bỏ required của fixed_price, thêm required cho discount_value
            document.getElementById('bulkPromoFixedPrice').removeAttribute('required');
            document.getElementById('bulkPromoDiscountPercent').setAttribute('required', 'required');
        } else {
            percentGroup.style.display = 'none';
            fixedPriceGroup.style.display = 'block';
            // Bỏ required của discount_value, thêm required cho fixed_price
            document.getElementById('bulkPromoDiscountPercent').removeAttribute('required');
            document.getElementById('bulkPromoFixedPrice').setAttribute('required', 'required');
        }
    });
});

    /* ===== Xử lý hiển/ẩn phạm vi áp dụng ===== */
    bulkPromoScopeType.addEventListener('change', function() {
        categoryGroup.style.display = 'none';
        productsGroup.style.display = 'none';

        if (this.value === 'category') {
            categoryGroup.style.display = 'block';
        } else if (this.value === 'specific_products') {
            productsGroup.style.display = 'block';
        }
    });

    function loadBulkPromotions() {
        fetch(BASE_URL + '/index.php?url=admin/api/bulk-promotions')
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response không phải JSON');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    bulkPromotionsData = data.data || [];
                    renderTable();
                } else {
                    tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">❌ ' + (data.error || 'Lỗi tải dữ liệu') + '</td></tr>';
                }
            })
            .catch(err => {
                console.error('❌ Lỗi khi tải chương trình khuyến mãi:', err);
                tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">❌ ' + err.message + '</td></tr>';
            });
    }

    function renderTable() {
        if (bulkPromotionsData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">📭 Không có chương trình khuyến mãi nào</td></tr>';
            return;
        }

        let html = '';
        bulkPromotionsData.forEach(promo => {
            const statusBadge = getStatusBadge(promo.is_active, promo.start_date, promo.end_date);
            const typeLabel = getPromotionTypeLabel(promo.promotion_type);
            const scopeLabel = getScopeTypeLabel(promo.scope_type);
            const value = promo.promotion_type === 'percentage' ? `${promo.discount_value}%` : formatNumber(promo.fixed_price) + 'đ';

            html += `
                <tr>
                    <td>${promo.id}</td>
                    <td><strong>${escapeHtml(promo.name)}</strong></td>
                    <td>${typeLabel}</td>
                    <td>${value}</td>
                    <td>${scopeLabel}</td>
                    <td>${promo.product_count || 0}</td>
                    <td>${formatDate(promo.start_date)}</td>
                    <td>${formatDate(promo.end_date)}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="action-buttons" style="display: flex; gap: 5px; justify-content: center;">
                            <button class="btn btn-sm btn-info edit-btn" data-id="${promo.id}" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${promo.id}" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Attach event listeners
        tbody.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', handleEdit);
        });

        tbody.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', handleDelete);
        });
    }

    function handleEdit(e) {
        const id = e.currentTarget.dataset.id;
        const promo = bulkPromotionsData.find(p => p.id == id);

        if (!promo) return;

        bulkPromoId.value = promo.id;
        bulkPromoUpdateFlag.value = '1';
        bulkPromoName.value = promo.name;
        
        // Set promotion type
        document.querySelectorAll('input[name="promotion_type"]').forEach(radio => {
            radio.checked = radio.value === promo.promotion_type;
        });

        // Update display based on type
        if (promo.promotion_type === 'percentage') {
            percentGroup.style.display = 'block';
            fixedPriceGroup.style.display = 'none';
            document.getElementById('bulkPromoDiscountPercent').value = promo.discount_value;
        } else {
            percentGroup.style.display = 'none';
            fixedPriceGroup.style.display = 'block';
            document.getElementById('bulkPromoFixedPrice').value = promo.fixed_price;
        }

        bulkPromoScopeType.value = promo.scope_type;
        document.getElementById('bulkPromoStart').value = promo.start_date.replace(' ', 'T');
        document.getElementById('bulkPromoEnd').value = promo.end_date.replace(' ', 'T');
        document.getElementById('bulkPromoIsActive').checked = promo.is_active == 1;

        // Trigger scope type change to update visible fields
        bulkPromoScopeType.dispatchEvent(new Event('change'));

        modalTitle.innerHTML = '<i class="fas fa-edit"></i> Chỉnh sửa chương trình khuyến mãi';
        bulkPromoModal.style.display = 'flex';
    }

    function handleDelete(e) {
        const id = e.currentTarget.dataset.id;
        if (confirm('Bạn chắc chắn muốn xóa chương trình khuyến mãi này?')) {
            deletePromo(id);
        }
    }

    function deletePromo(id) {
        const formData = new FormData();
        
        formData.append('id', id);

        fetch(BASE_URL + '/index.php?url=admin/bulk-promotions/delete', {
            method: 'POST',
            body: formData
        })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response không phải JSON');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Xóa chương trình thành công');
                    loadBulkPromotions();
                } else {
                    showToast(data.error || 'Lỗi khi xóa', 'error');
                }
            })
            .catch(err => {
                console.error('❌ Lỗi khi xóa:', err);
                showToast(err.message || 'Có lỗi xảy ra', 'error');
            });
    }

    /* ===== Mở Modal Thêm ===== */
    openModalBtn.addEventListener('click', () => {
        bulkPromoId.value = '';
        bulkPromoUpdateFlag.value = '';
        bulkPromoForm.reset();
        
        // Reset display
        document.querySelector('input[name="promotion_type"][value="percentage"]').checked = true;
        percentGroup.style.display = 'block';
        fixedPriceGroup.style.display = 'none';
        categoryGroup.style.display = 'none';
        productsGroup.style.display = 'none';
        
       // Set required mặc định cho discount_value (vì đang chọn %)
    document.getElementById('bulkPromoDiscountPercent').setAttribute('required', 'required');
    document.getElementById('bulkPromoFixedPrice').removeAttribute('required');
    
    modalTitle.innerHTML = '<i class="fas fa-plus-circle"></i> Thêm chương trình khuyến mãi';
    bulkPromoModal.style.display = 'flex';
});

    /* ===== Đóng Modal ===== */
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            bulkPromoModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === bulkPromoModal) {
            bulkPromoModal.style.display = 'none';
        }
    });

    /* ===== Xử lý Submit Form ===== */
    bulkPromoForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const isUpdate = bulkPromoUpdateFlag.value === '1';
        const url = isUpdate 
            ? BASE_URL + '/index.php?url=admin/bulk-promotions/update'
            : BASE_URL + '/index.php?url=admin/bulk-promotions/store';

        const formData = new FormData(this);

        // Thêm category_ids vào formData nếu chọn danh mục
const scopeType = document.getElementById('bulkPromoScopeType').value;
if (scopeType === 'category') {
    const categorySelect = document.getElementById('bulkPromoCategoryId');
    const selectedOptions = Array.from(categorySelect.selectedOptions);
    selectedOptions.forEach(option => {
        formData.append('category_ids[]', option.value);
    });
}

// Thêm product_ids vào formData nếu chọn sản phẩm cụ thể
if (scopeType === 'specific_products') {
    const productCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    productCheckboxes.forEach(cb => {
        formData.append('product_ids[]', cb.value);
    });
}

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response không phải JSON');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(isUpdate ? 'Cập nhật thành công' : 'Tạo chương trình thành công');
                    bulkPromoModal.style.display = 'none';
                    selectedProducts = [];
                    loadBulkPromotions();
                } else {
                    showToast(data.error || 'Lỗi khi lưu', 'error');
                }
            })
            .catch(err => {
                console.error('❌ Lỗi khi lưu:', err);
                showToast(err.message || 'Có lỗi xảy ra', 'error');
            });
    });

    /* ===== Tìm kiếm ===== */
    searchInput.addEventListener('input', function() {
        const keyword = this.value.toLowerCase();
        tbody.querySelectorAll('tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });
    });

    // Load initial data
    loadCategories();
    loadProducts();
    loadBulkPromotions();
});
