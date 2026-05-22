document.addEventListener('DOMContentLoaded', function() {
    let promotionsData = [];

    // DOM elements
    const searchInput = document.getElementById('searchPromo');
    const tbody = document.querySelector('#promotionsTable tbody');
    const openModalBtn = document.getElementById('openModalBtn');
    const promoModal = document.getElementById('promoModal');
    const promoForm = document.getElementById('promoForm');
    const editId = document.getElementById('editId');
    const updateFlag = document.getElementById('updateFlag');
    const promoCode = document.getElementById('promoCode');
    const promoDiscountPercent = document.getElementById('promoDiscountPercent');
    const promoDiscountAmount = document.getElementById('promoDiscountAmount');
    const promoMinOrder = document.getElementById('promoMinOrder');
    const promoUsageLimit = document.getElementById('promoUsageLimit');
    const promoStart = document.getElementById('promoStart');
    const promoEnd = document.getElementById('promoEnd');
    const modalTitle = document.querySelector('#promoModal .modal-header h2');
    const discountTypeRadios = document.querySelectorAll('input[name="discount_type"]');
    const percentGroup = document.getElementById('percent-group');
    const amountGroup = document.getElementById('amount-group');

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

    /* ===== Hiển thị trạng thái của mã khuyến mãi ===== */
    function getStatusBadge(status) {
        switch(status) {
            case 'active':
                return '<span class="status-badge status-active">🟢 Đang diễn ra</span>';
            case 'upcoming':
                return '<span class="status-badge status-upcoming">🔜 Sắp diễn ra</span>';
            case 'expired':
                return '<span class="status-badge status-expired">🔚 Đã kết thúc</span>';
            default:
                return '<span class="status-badge">—</span>';
        }
    }

    function getDiscountHtml(promo) {
        let percent = parseFloat(promo.discount_percent) || 0;
        let amount = parseFloat(promo.discount_amount) || 0;
        if (percent > 0) return `${percent}%`;
        if (amount > 0) return `${formatNumber(amount)}đ`;
        return '0%';
    }

    /* ===== Xử lý hiển/ẩn nhập liệu giảm giá theo loại ===== */
    discountTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'percent') {
                percentGroup.style.display = 'block';
                amountGroup.style.display = 'none';
                promoDiscountAmount.value = '';
            } else {
                percentGroup.style.display = 'none';
                amountGroup.style.display = 'block';
                promoDiscountPercent.value = '';
            }
        });
    });

    function loadPromotions() {
        fetch(BASE_URL + '/index.php?url=admin/api/promotions')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    promotionsData = data.data;
                    renderTable();
                } else {
                    tbody.innerHTML = '<tr><td colspan="11" style="text-align: center;">❌ Lỗi tải dữ liệu</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="11" style="text-align: center;">❌ Lỗi kết nối</td></tr>';
            });
    }

    function renderTable() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        let filtered = promotionsData.filter(p => p.code.toLowerCase().includes(searchTerm));
        let html = '';
        if (filtered.length > 0) {
            filtered.forEach(p => {
                const minOrder = p.min_order_value > 0 ? formatNumber(p.min_order_value) + 'đ' : '0đ (không giới hạn)';
                const usageLimit = p.usage_limit > 0 ? p.usage_limit : '∞';
                const remaining = p.usage_limit > 0 ? p.usage_limit - (p.used_count || 0) : '∞';
                html += `<tr>
                    <td>${p.id}</td>
                    <td><strong class="discount-badge">${escapeHtml(p.code)}</strong></td>
                    <td>${getDiscountHtml(p)}</td>
                    <td>${minOrder}</td>
                    <td>${usageLimit}</td>
                    <td>${p.used_count || 0}</td>
                    <td>${remaining}</td>
                    <td>${formatDate(p.start_date)}</td>
                    <td>${formatDate(p.end_date)}</td>
                    <td>${getStatusBadge(p.status)}</td>
                    <td>
                        <button class="btn-icon edit-promo" data-id="${p.id}" data-code="${p.code}" data-discount-percent="${p.discount_percent || 0}" data-discount-amount="${p.discount_amount || 0}" data-min-order="${p.min_order_value || 0}" data-usage-limit="${p.usage_limit || 0}" data-start="${p.start_date || ''}" data-end="${p.end_date || ''}" title="Sửa"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon delete-promo" data-id="${p.id}" data-code="${p.code}" title="Xóa"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="11" style="text-align: center;">📭 Không có mã giảm giá nào</td></tr>';
        }
        tbody.innerHTML = html;
        attachEvents();
    }

    /* ===== Gắn sự kiện click cho các nút "Sửa" và "Xóa" trong bảng ===== */
    function attachEvents() {
        document.querySelectorAll('.edit-promo').forEach(btn => {
            btn.addEventListener('click', function() {
                editPromotion(
                    this.dataset.id,
                    this.dataset.code,
                    this.dataset.discountPercent,
                    this.dataset.discountAmount,
                    this.dataset.minOrder,
                    this.dataset.usageLimit,
                    this.dataset.start,
                    this.dataset.end
                );
            });
        });
        document.querySelectorAll('.delete-promo').forEach(btn => {
            btn.addEventListener('click', function() {
                deletePromotion(this.dataset.id, this.dataset.code);
            });
        });
    }

    /* ===== Reset form thêm/sửa về trạng thái ban đầu (thêm mới) ===== */
    function resetForm() {
        editId.value = '';
        updateFlag.value = '';
        promoCode.value = '';
        promoDiscountPercent.value = '';
        promoDiscountAmount.value = '';
        promoMinOrder.value = '0';
        promoUsageLimit.value = '0';
        promoStart.value = '';
        promoEnd.value = '';
        modalTitle.innerHTML = '<i class="fas fa-plus-circle"></i> Thêm mã giảm giá';
    
        document.querySelector('input[name="discount_type"][value="percent"]').checked = true;
        percentGroup.style.display = 'block';
        amountGroup.style.display = 'none';
        promoDiscountAmount.value = '';
    }

    function editPromotion(id, code, discountPercent, discountAmount, minOrder, usageLimit, startDate, endDate) {
        editId.value = id;
        updateFlag.value = 'update';
        promoCode.value = code;
        promoDiscountPercent.value = discountPercent;
        promoDiscountAmount.value = discountAmount;
        promoMinOrder.value = minOrder;
        promoUsageLimit.value = usageLimit;
        promoStart.value = startDate ? startDate.replace(' ', 'T') : '';
        promoEnd.value = endDate ? endDate.replace(' ', 'T') : '';
        modalTitle.innerHTML = '<i class="fas fa-edit"></i> Sửa mã giảm giá';

        if (parseFloat(discountPercent) > 0) {
            document.querySelector('input[name="discount_type"][value="percent"]').checked = true;
            percentGroup.style.display = 'block';
            amountGroup.style.display = 'none';
        } else if (parseFloat(discountAmount) > 0) {
            document.querySelector('input[name="discount_type"][value="amount"]').checked = true;
            percentGroup.style.display = 'none';
            amountGroup.style.display = 'block';
        } else {
            document.querySelector('input[name="discount_type"][value="percent"]').checked = true;
            percentGroup.style.display = 'block';
            amountGroup.style.display = 'none';
        }
        promoModal.style.display = 'block';
    }

    function deletePromotion(id, code) {
        if (confirm(`Bạn có chắc muốn xóa mã "${code}"?`)) {
            fetch(BASE_URL + '/index.php?url=admin/promotions/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Xóa mã thành công', 'success');
                    loadPromotions();
                } else {
                    showToast('Xóa thất bại', 'error');
                }
            })
            .catch(() => showToast('Lỗi kết nối', 'error'));
        }
    }

    if (openModalBtn) {
        openModalBtn.addEventListener('click', () => {
            resetForm();
            promoModal.style.display = 'block';
        });
    }
    document.querySelectorAll('#promoModal .close, #promoModal .close-modal').forEach(el => {
        el.addEventListener('click', () => {
            promoModal.style.display = 'none';
            resetForm();
        });
    });
    window.addEventListener('click', (e) => {
        if (e.target === promoModal) {
            promoModal.style.display = 'none';
            resetForm();
        }
    });

    // Xử lý submit form thêm/sửa (gửi dữ liệu qua AJAX)
    if (promoForm) {
        promoForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Validate code
            if (!promoCode.value.trim()) {
                showToast('Vui lòng nhập mã giảm giá', 'error');
                return;
            }
            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            let discountPercent = parseFloat(promoDiscountPercent.value);
            let discountAmount = parseFloat(promoDiscountAmount.value);
            if (discountType === 'percent' && (isNaN(discountPercent) || discountPercent <= 0)) {
                showToast('Vui lòng nhập phần trăm giảm giá hợp lệ (>0)', 'error');
                return;
            }
            if (discountType === 'amount' && (isNaN(discountAmount) || discountAmount <= 0)) {
                showToast('Vui lòng nhập số tiền giảm hợp lệ (>0)', 'error');
                return;
            }
        
            const start = promoStart.value;
            const end = promoEnd.value;
            if (start && end && new Date(start) >= new Date(end)) {
                showToast('Ngày kết thúc phải sau ngày bắt đầu', 'error');
                return;
            }

            const formData = new FormData(promoForm);
            
            if (discountType === 'percent') {
                formData.delete('discount_amount');
                formData.set('discount_percent', discountPercent);
            } else {
                formData.delete('discount_percent');
                formData.set('discount_amount', discountAmount);
            }
            const isUpdate = updateFlag.value === 'update';
            const url = isUpdate ? BASE_URL + '/index.php?url=admin/promotions/update' : BASE_URL + '/index.php?url=admin/promotions/store';
            fetch(url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        promoModal.style.display = 'none';
                        resetForm();
                        loadPromotions();
                    } else {
                        showToast(data.error || 'Thao tác thất bại', 'error');
                    }
                })
                .catch(err => showToast('Lỗi kết nối: ' + err.message, 'error'));
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', renderTable);
    }

    loadPromotions();
});