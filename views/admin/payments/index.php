<?php 
$pageTitle = 'Quản lý thanh toán'; 
$activeMenu = 'payments';
?>
<script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>

<div class="payments-page">
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Quản lý thanh toán</h1>
        <div class="search-filters">
            <select id="statusFilter" class="form-control">
                <option value="all">Tất cả trạng thái</option>
                <option value="unpaid">Chưa thanh toán</option>
                <option value="pending_verification">Chờ xác nhận</option>
                <option value="paid">Đã thanh toán</option>
            </select>
            <button id="refreshBtn" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i> Làm mới
            </button>
        </div>
    </div>

    <!-- Thống kê nhanh -->
    <div class="stats-grid" id="paymentStats">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3>Chờ xác nhận</h3>
                <p class="stat-number" id="statPendingVerification">0</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3>Đã thanh toán</h3>
                <p class="stat-number" id="statPaid">0</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-hourglass"></i></div>
            <div class="stat-info">
                <h3>Chưa thanh toán</h3>
                <p class="stat-number" id="statUnpaid">0</p>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách đơn hàng chuyển khoản</h3>
            <span class="badge active" id="orderCount">0 đơn</span>
        </div>
        <div class="card-body" style="padding: 20px; overflow-x: auto;">
            <table class="data-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <tr><td colspan="7" style="text-align: center;">Đang tải...<\/td><\/tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let paymentsData = [];

function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

function formatDate(dateString) {
    if (!dateString) return '---';
    let date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

function getPaymentBadge(status) {
    switch(status) {
        case 'paid':
            return '<span class="status-badge payment-paid">Đã thanh toán</span>';
        case 'pending_verification':
            return '<span class="status-badge payment-pending_verification">Chờ xác nhận</span>';
        case 'unpaid':
        default:
            return '<span class="status-badge payment-unpaid">Chưa thanh toán</span>';
    }
}

function loadPayments() {
    const status = document.getElementById('statusFilter').value;
    fetch(BASE_URL + '/index.php?url=admin/api/payments&status=' + status)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                paymentsData = data.data; // Dòng này phải chạy
                renderPaymentsTable();
                updateStatistics();
                document.getElementById('orderCount').innerText = paymentsData.length + ' đơn';
            } else {
                document.getElementById('paymentsTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">❌ Lỗi tải dữ liệu<\/td><\/tr>';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('paymentsTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">❌ Lỗi kết nối<\/td><\/tr>';
        });
}

function renderPaymentsTable() {
    let html = '';
    if(paymentsData.length > 0) {
        paymentsData.forEach(payment => {
            html += `<tr>
                <td><strong>#${payment.id}</strong></td>
                <td>${escapeHtml(payment.user_name || 'N/A')}</td>
                <td>${escapeHtml(payment.phone || '---')}</td>
                <td><strong>${formatNumber(payment.discount_price || payment.total_price || 0)}đ</strong></td>
                <td>${formatDate(payment.created_at)}</td>
                <td>${getPaymentBadge(payment.payment_status)}</td>
                <td>
                    ${payment.payment_status === 'pending_verification' ? 
                        `<button class="btn-icon confirm-payment" data-id="${payment.id}" title="Xác nhận đã nhận tiền">
                            <i class="fas fa-check-circle"></i> Xác nhận
                        </button>` : 
                        `<span style="color: #94a3b8; font-size: 12px;">---</span>`
                    }
                </td>
            <\/tr>`;
        });
    } else {
        html = '<tr><td colspan="7" style="text-align: center;">📭 Không có đơn hàng nào<\/td><\/tr>';
    }
    document.getElementById('paymentsTableBody').innerHTML = html;
    
    document.querySelectorAll('.confirm-payment').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.id;
            confirmPayment(orderId);
        });
    });
}

function updateStatistics() {
    const pendingCount = paymentsData.filter(p => p.payment_status === 'pending_verification').length;
    const paidCount = paymentsData.filter(p => p.payment_status === 'paid').length;
    const unpaidCount = paymentsData.filter(p => p.payment_status === 'unpaid').length;
    
    document.getElementById('statPendingVerification').innerText = pendingCount;
    document.getElementById('statPaid').innerText = paidCount;
    document.getElementById('statUnpaid').innerText = unpaidCount;
}

function confirmPayment(orderId) {
    if (!confirm('Xác nhận đã nhận tiền từ khách hàng?')) return;
    
    fetch(BASE_URL + '/index.php?url=admin/payments/confirm', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showToast(data.message, 'success');
            loadPayments();
        } else {
            showToast(data.error || 'Xác nhận thất bại', 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối', 'error'));
}

function showToast(message, type) {
    var toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
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

document.getElementById('statusFilter').addEventListener('change', loadPayments);
document.getElementById('refreshBtn').addEventListener('click', loadPayments);
loadPayments();
</script>