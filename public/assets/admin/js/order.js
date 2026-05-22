document.addEventListener('DOMContentLoaded', function() {
    // Lấy BASE_URL
    let BASE_URL = '';
    const baseMeta = document.querySelector('meta[name="base-url"]');
    if (baseMeta) {
        BASE_URL = baseMeta.getAttribute('content');
    } else if (typeof window.BASE_URL !== 'undefined') {
        BASE_URL = window.BASE_URL;
    } else {
        const path = window.location.pathname;
        if (path.includes('/LoopFit/public')) BASE_URL = '/LoopFit/public';
        else if (path.includes('/public')) BASE_URL = '/public';
        else BASE_URL = '';
    }

    let ordersData = [];
    let selectedOrders = [];

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '0';
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

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.padding = '12px 24px';
        toast.style.borderRadius = '8px';
        toast.style.color = 'white';
        toast.style.zIndex = '9999';
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    /* ===== Mapping trạng thái hiển thị ===== */
    const orderStatusLabels = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao',
        'renting': 'Đang thuê',
        'returning': 'Đang hoàn trả',
        'completed': 'Hoàn tất',
        'cancelled': 'Đã hủy'
    };

    const paymentLabels = {
        'unpaid': 'Chưa thanh toán',
        'pending': 'Chờ thanh toán',
        'pending_verification': 'Chờ xác nhận',
        'paid': 'Đã thanh toán'
    };

    const depositLabels = {
        'pending': 'Chưa hoàn',
        'pending_return': 'Chờ hoàn cọc',
        'refunded_full': 'Đã hoàn toàn bộ',
        'refunded_partial': 'Hoàn một phần',
        'forfeited':      'Bị giữ (không hoàn)'
    };

    /* ===== Cập nhật số lượng đơn đã chọn trên giao diện ===== */
    function updateSelectedCount() {
        const countEl = document.getElementById('selectedCount');
        if (countEl) countEl.innerText = `Đã chọn ${selectedOrders.length} đơn`;
    }

    /* ===== Gắn sự kiện cho checkbox từng dòng ===== */
    function attachCheckboxEvents() {
        document.querySelectorAll('.order-checkbox-admin').forEach(cb => {
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

    /* ===== Render bảng đơn hàng dựa trên bộ lọc hiện tại ===== */
    function renderOrdersTable() {
        const searchTerm = document.getElementById('searchOrder')?.value.toLowerCase() || '';
        const paymentFilter = document.getElementById('paymentFilter')?.querySelector('.chip.active')?.dataset.value || 'all';
        const orderFilter = document.getElementById('orderFilter')?.querySelector('.chip.active')?.dataset.value || 'all';
        const depositFilter = document.getElementById('depositFilter')?.querySelector('.chip.active')?.dataset.value || 'all';
        
        // Lọc dữ liệu
        let filtered = ordersData.filter(order => {
            const matchSearch = order.id.toString().includes(searchTerm) || 
                (order.user_name || '').toLowerCase().includes(searchTerm) ||
                (order.email || '').toLowerCase().includes(searchTerm);
            const matchPayment = paymentFilter === 'all' || order.payment_status === paymentFilter;
            const matchOrder = orderFilter === 'all' || order.status === orderFilter;
            const matchDeposit = depositFilter === 'all' || order.deposit_status === depositFilter;
            return matchSearch && matchPayment && matchOrder && matchDeposit;
        });
        
        // Cập nhật số lượng kết quả
        document.getElementById('orderCount').innerText = filtered.length + ' đơn hàng';
        
        let html = '';
        if (filtered.length > 0) {
            filtered.forEach(order => {
                const isChecked = selectedOrders.includes(order.id) ? 'checked' : '';
                let totalDisplay = order.final_total;
                if (totalDisplay === null || totalDisplay === undefined) {
                    const totalPrice = parseFloat(order.total_price) || 0;
                    const deposit = parseFloat(order.deposit_amount) || 0;
                    const shipping = parseFloat(order.shipping_fee) || 0;
                    const discount = parseFloat(order.discount_price) || 0;
                    totalDisplay = totalPrice + deposit + shipping - discount;
                }
                const depositAmount = parseFloat(order.deposit_amount) || 0;
                const rentDate = order.rent_date ? new Date(order.rent_date).toLocaleDateString('vi-VN') : '---';
                const returnDate = order.return_date ? new Date(order.return_date).toLocaleDateString('vi-VN') : '---';
                
                html += `<tr>
                    <td style="text-align: center;"><input type="checkbox" class="order-checkbox-admin" data-id="${order.id}" ${isChecked}><\/td>
                    <td><strong>#${order.id}<\/strong><\/td>
                    <td><strong>${escapeHtml(order.user_name || 'N/A')}<\/strong><\/td>
                    <td>${escapeHtml(order.email || '')}<br><small>${escapeHtml(order.phone || '---')}<\/small><\/td>
                    <td>${rentDate}<\/td>
                    <td>${returnDate}<\/td>
                    <td><strong>${formatNumber(totalDisplay)}đ<\/strong><\/td>
                    <td>${formatNumber(depositAmount)}đ<\/td>
                    <td><span class="status-badge status-${order.status}">${orderStatusLabels[order.status] || order.status}<\/span><\/td>
                    <td>
                        <span class="status-badge payment-${order.payment_status}">${paymentLabels[order.payment_status] || order.payment_status}<\/span>
                        ${order.payment_status === 'pending_verification' ? `<button class="btn-confirm-payment" onclick="confirmPayment(${order.id})">Xác nhận</button>` : ''}
                    <\/td>
                    <td><span class="status-badge deposit-${order.deposit_status}">${depositLabels[order.deposit_status] || order.deposit_status}<\/span><\/td>
                    <td><button class="btn-icon-action" onclick="viewOrderDetail(${order.id})"><i class="fas fa-eye"><\/i><\/button><\/td>
                <\/tr>`;
            });
        } else {
            html = '<tr><td colspan="12" style="text-align: center;">📭 Không tìm thấy đơn hàng nào<\/tr>';
        }
        document.querySelector('#ordersTable tbody').innerHTML = html;
        attachCheckboxEvents();
    }

    /* ===== Tải danh sách đơn hàng từ API và render ===== */
    function loadOrders() {
        fetch(BASE_URL + '/index.php?url=admin/api/orders')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    ordersData = data.data;
                    renderOrdersTable();
                } else {
                    console.error('API error:', data.error);
                    document.querySelector('#ordersTable tbody').innerHTML = '<tr><td colspan="12" class="text-center">❌ Lỗi: ' + escapeHtml(data.error || 'Không xác định') + '<\/td><\/tr>';
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                document.querySelector('#ordersTable tbody').innerHTML = '<tr><td colspan="12" class="text-center">❌ Không thể kết nối đến máy chủ<\/td><\/tr>';
            });
    }

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
                if (data.success) {
                    showToast(`Đã duyệt ${data.approved} đơn, bỏ qua ${data.skipped} đơn`, 'success');
                    selectedOrders = [];
                    document.querySelectorAll('.order-checkbox-admin').forEach(cb => cb.checked = false);
                    const selectAll = document.getElementById('selectAllOrders');
                    if (selectAll) selectAll.checked = false;
                    updateSelectedCount();
                    loadOrders();
                } else {
                    showToast(data.error || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(err => showToast('Lỗi kết nối', 'error'));
        }
    });

    /* ===== Tải danh sách đơn hàng từ API và render ===== */

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
                if (data.success) {
                    showToast(`Đã hủy ${data.cancelled} đơn`, 'success');
                    selectedOrders = [];
                    document.querySelectorAll('.order-checkbox-admin').forEach(cb => cb.checked = false);
                    const selectAll = document.getElementById('selectAllOrders');
                    if (selectAll) selectAll.checked = false;
                    updateSelectedCount();
                    loadOrders();
                } else {
                    showToast(data.error || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(err => showToast('Lỗi kết nối', 'error'));
        }
    });

    // Chọn tất cả
    document.getElementById('selectAllOrders')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox-admin');
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

    // Xóa lựa chọn
    document.getElementById('clearSelectionBtn')?.addEventListener('click', function() {
        selectedOrders = [];
        document.querySelectorAll('.order-checkbox-admin').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAllOrders');
        if (selectAll) selectAll.checked = false;
        updateSelectedCount();
    });

    // Reset bộ lọc
    document.getElementById('resetFilters')?.addEventListener('click', function() {
        document.getElementById('searchOrder').value = '';
        document.querySelectorAll('.filter-chips .chip').forEach(chip => {
            if (chip.dataset.value === 'all') chip.classList.add('active');
            else chip.classList.remove('active');
        });
        selectedOrders = [];
        renderOrdersTable();
    });

    // Tìm kiếm realtime
    document.getElementById('searchOrder')?.addEventListener('keyup', function() {
        renderOrdersTable();
    });

    // Bộ lọc chip: gắn sự kiện cho tất cả chip lọc (trạng thái đơn, thanh toán, cọc)
    document.querySelectorAll('.filter-chips .chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            renderOrdersTable();
        });
    });

    // Hàm xác nhận thanh toán
    window.confirmPayment = function(orderId) {
        if (!confirm('Xác nhận đã nhận được tiền?')) return;
        fetch(BASE_URL + '/index.php?url=admin/orders/approvePayment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) showToast(data.message, 'success');
            else showToast(data.error, 'error');
            loadOrders();
        })
        .catch(err => showToast('Lỗi kết nối', 'error'));
    };

    // Hàm xem chi tiết đơn hàng
    window.viewOrderDetail = function(id) {
        window.location.href = BASE_URL + '/index.php?url=admin/orders/detail&id=' + id;
    };

    // Khởi tạo: tải danh sách đơn hàng
    loadOrders();
});