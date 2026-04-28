<?php 
$pageTitle = 'Quản lý đơn hàng'; 
$activeMenu = 'orders';
?>
<div class="orders-page">
    <div class="page-header">
        <h1>Quản lý đơn hàng</h1>
        <div class="search-filters">
            <input type="text" id="searchOrder" placeholder="Tìm kiếm..." class="form-control" style="width: 280px;">
            <button id="resetFilters" class="btn btn-secondary">Reset</button>
        </div>
    </div>

    <!-- Filter Groups -->
    <div class="filter-groups">
        <div class="filter-group">
            <label class="filter-label">
                <svg class="filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Thanh toán
            </label>
            <div class="filter-chips" id="paymentFilter">
                <button class="chip active" data-value="all">Tất cả</button>
                <button class="chip" data-value="pending_verification">Chờ xác nhận</button>
                <button class="chip" data-value="paid">Đã thanh toán</button>
                <button class="chip" data-value="unpaid">Chưa thanh toán</button>
            </div>
        </div>

        <div class="filter-group">
            <label class="filter-label">
                <svg class="filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Đơn hàng
            </label>
            <div class="filter-chips" id="orderFilter">
                <button class="chip active" data-value="all">Tất cả</button>
                <button class="chip" data-value="pending">Chờ xác nhận</button>
                <button class="chip" data-value="processing">Đang xử lý</button>
                <button class="chip" data-value="renting">Đang thuê</button>
                <button class="chip" data-value="completed">Hoàn trả</button>
                <button class="chip" data-value="cancelled">Đã hủy</button>
            </div>
        </div>

        <div class="filter-group">
            <label class="filter-label">
                <svg class="filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tiền cọc
            </label>
            <div class="filter-chips" id="depositFilter">
                <button class="chip active" data-value="all">Tất cả</button>
                <button class="chip" data-value="pending">Chưa hoàn</button>
                <button class="chip" data-value="pending_return">Chờ hoàn cọc</button>
                <button class="chip" data-value="refunded_full">Đã hoàn toàn bộ</button>
                <button class="chip" data-value="refunded_partial">Hoàn một phần</button>
            </div>
        </div>
    </div>

    <!-- Batch Actions -->
    <div class="batch-actions">
        <div>
            <svg class="batch-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span id="selectedCount">Đã chọn 0 đơn</span>
        </div>
        <div>
            <button id="batchApproveBtn" class="btn-batch btn-batch-success">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Duyệt đã chọn
            </button>
            <button id="batchCancelBtn" class="btn-batch btn-batch-danger">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Hủy đã chọn
            </button>
            <button id="clearSelectionBtn" class="btn-batch btn-batch-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Bỏ chọn
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h3>Danh sách đơn hàng</h3>
            <span class="badge active" id="orderCount">0 đơn hàng</span>
        </div>
        <div class="card-body" style="padding: 20px; overflow-x: auto;">
            <table class="data-table" id="ordersTable" style="width: 100%; min-width: 1200px;">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" id="selectAllOrders"></th>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Email/SĐT</th>
                        <th>Ngày thuê</th>
                        <th>Ngày trả</th>
                        <th>Tổng tiền</th>
                        <th>Tiền cọc</th>
                        <th>Trạng thái đơn</th>
                        <th>Trạng thái thanh toán</th>
                        <th>Trạng thái cọc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="12" style="text-align: center;">Đang tải...<\/tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?? "http://localhost:8080/LoopFit/public" ?>';

// ===== KHAI BÁO BIẾN =====
let ordersData = [];
let selectedOrders = [];

// ĐÃ SỬA: cập nhật đúng tên trạng thái
const orderStatusLabels = {
    'pending': 'Chờ xác nhận',
    'processing': 'Đang xử lý',
    'renting': 'Đang thuê',
    'completed': 'Hoàn trả',
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
    'refunded_partial': 'Hoàn một phần'
};

// ===== CÁC HÀM =====
function formatNumber(num) {
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
    var toast = document.createElement('div');
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

function updateSelectedCount() {
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.innerText = `Đã chọn ${selectedOrders.length} đơn`;
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

function loadOrders() {
    fetch(BASE_URL + '/index.php?url=admin/api/orders')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                ordersData = data.data;
                renderOrdersTable();
            }
        });
}

function renderOrdersTable() {
    const searchTerm = document.getElementById('searchOrder')?.value.toLowerCase() || '';
    const paymentFilter = document.getElementById('paymentFilter')?.querySelector('.chip.active')?.dataset.value || 'all';
    const orderFilter = document.getElementById('orderFilter')?.querySelector('.chip.active')?.dataset.value || 'all';
    const depositFilter = document.getElementById('depositFilter')?.querySelector('.chip.active')?.dataset.value || 'all';
    
    let filtered = ordersData.filter(order => {
        const matchSearch = order.id.toString().includes(searchTerm) || 
            (order.user_name || '').toLowerCase().includes(searchTerm) ||
            (order.email || '').toLowerCase().includes(searchTerm);
        
        const matchPayment = paymentFilter === 'all' || order.payment_status === paymentFilter;
        const matchOrder = orderFilter === 'all' || order.status === orderFilter;
        const matchDeposit = depositFilter === 'all' || order.deposit_status === depositFilter;
        
        return matchSearch && matchPayment && matchOrder && matchDeposit;
    });
    
    document.getElementById('orderCount').innerText = filtered.length + ' đơn hàng';
    
    let html = '';
    if(filtered.length > 0) {
        filtered.forEach(order => {
            const isChecked = selectedOrders.includes(order.id) ? 'checked' : '';
            html += `<tr>
                <td style="text-align: center;"><input type="checkbox" class="order-checkbox" data-id="${order.id}" ${isChecked}><\/td>
                <td><strong>#${order.id}<\/strong><\/td>
                <td><strong>${escapeHtml(order.user_name || 'N/A')}<\/strong><\/td>
                <td>${escapeHtml(order.email || '')}<br><small>${escapeHtml(order.phone || '---')}<\/small><\/td>
                <td>${order.rent_date ? new Date(order.rent_date).toLocaleDateString('vi-VN') : '---'}<\/td>
                <td>${order.return_date ? new Date(order.return_date).toLocaleDateString('vi-VN') : '---'}<\/td>
                <td><strong>${formatNumber(order.discount_price || order.total_price)}đ<\/strong><\/td>
                <td>${formatNumber(order.total_deposit || 0)}đ<\/td>
                <td><span class="status-badge status-${order.status}">${orderStatusLabels[order.status]}</span><\/td>
                <td>
                    <span class="status-badge payment-${order.payment_status}">${paymentLabels[order.payment_status]}</span>
                    ${order.payment_status === 'pending_verification' ? `<button class="btn-confirm-payment" onclick="confirmPayment(${order.id})">Xác nhận</button>` : ''}
                <\/td>
                <td><span class="status-badge deposit-${order.deposit_status}">${depositLabels[order.deposit_status]}</span><\/td>
               <td><button class="btn-icon-action" onclick="viewOrderDetail(${order.id})"><i class="fas fa-eye"></i><\/button><\/td>
            <\/tr>`;
        });
    } else {
        html = '<tr><td colspan="12" style="text-align: center;">📭 Không tìm thấy đơn hàng nào<\/tr>';
    }
    document.querySelector('#ordersTable tbody').innerHTML = html;
    attachCheckboxEvents();
}

function confirmPayment(orderId) {
    if (!confirm('Xác nhận đã nhận được tiền?')) return;
    fetch(BASE_URL + '/index.php?url=admin/orders/approvePayment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}`
    }).then(res => res.json()).then(data => {
        if(data.success) showToast(data.message, 'success');
        else showToast(data.error, 'error');
        loadOrders();
    });
}

function viewOrderDetail(id) {
    window.location.href = BASE_URL + '/index.php?url=admin/orders/detail&id=' + id;
}

// Select all
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
    renderOrdersTable();
});

// Search
document.getElementById('searchOrder')?.addEventListener('keyup', renderOrdersTable);

// Khởi tạo filter chips
document.querySelectorAll('.filter-chips .chip').forEach(chip => {
    chip.addEventListener('click', function() {
        const parent = this.parentElement;
        parent.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        renderOrdersTable();
    });
});

loadOrders();
</script>