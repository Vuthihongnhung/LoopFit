<!-- Header -->
<div class="page-header">
    <h1>Tổng quan</h1>
    <p>Chào mừng trở lại, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>!</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid" id="statsGrid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <h3>Sản phẩm</h3>
            <p class="stat-number" id="statProducts">0</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-info">
            <h3>Đơn hàng</h3>
            <p class="stat-number" id="statOrders">0</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3>Người dùng</h3>
            <p class="stat-number" id="statUsers">0</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <h3>Doanh thu</h3>
            <p class="stat-number" id="statRevenue">0đ</p>
        </div>
    </div>
</div>

<!-- ===== THÔNG BÁO CẦN XỬ LÝ ===== -->
<div class="row" style="display: flex; gap: 24px; margin-bottom: 30px; flex-wrap: wrap;">
    <!-- Đơn hàng chờ xác nhận chuyển khoản -->
    <div class="card" style="flex: 1; min-width: 250px;">
        <div class="card-header" style="background: #c6f1fc; color: #063546;">
            <h3><i class="fas fa-clock"></i> Đơn hàng chờ xác nhận chuyển khoản</h3>
            <span class="badge" id="pendingPaymentCount" style="background: #226e8f; color: white;">0</span>
        </div>
        <div class="card-body" style="padding: 0; max-height: 300px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f1f5f9;">
                    <tr>
                        <th style="padding: 8px 4px; text-align: left; width: 50px;">Mã</th>
                        <th style="padding: 8px 4px; text-align: left;">Khách hàng</th>
                        <th style="padding: 8px 4px; text-align: right; width: 80px;">Tiền</th>
                        <th style="padding: 8px 4px; text-align: center; width: 85px;">Xác nhận</th>
                    </tr>
                </thead>
                <tbody id="pendingPaymentList">
                    <tr><td colspan="4" style="padding: 20px; text-align: center;">Đang tải...</td>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Đơn hàng chờ kiểm tra hoàn cọc -->
    <div class="card" style="flex: 1; min-width: 250px;">
        <div class="card-header" style="background: #dbeafe; color: #1e40af;">
            <h3><i class="fas fa-hand-holding-usd"></i> Đơn hàng chờ hoàn cọc</h3>
            <span class="badge" id="pendingDepositCount" style="background: #2563eb; color: white;">0</span>
        </div>
        <div class="card-body" style="padding: 0; max-height: 300px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f1f5f9;">
                    <tr>
                        <th style="padding: 8px 4px; text-align: left; width: 50px;">Mã</th>
                        <th style="padding: 8px 4px; text-align: left;">Khách hàng</th>
                        <th style="padding: 8px 4px; text-align: right; width: 80px;">Cọc</th>
                        <th style="padding: 8px 4px; text-align: center; width: 85px;">Kiểm tra</th>
                    </tr>
                </thead>
                <tbody id="pendingDepositList">
                    <tr><td colspan="4" style="padding: 20px; text-align: center;">Đang tải...</td>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Biểu đồ -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header" style="background: linear-gradient(135deg, #4a67c5, #2563eb);">
        <h3 style="color: white; margin: 0;"><i class="fas fa-chart-bar"></i> Biểu đồ doanh thu theo tháng</h3>
    </div>
    <div class="card-body" style="padding: 20px;">
        <canvas id="revenueChart" width="800" height="300"></canvas>
    </div>
</div>

<!-- Top sản phẩm -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header" style="background: linear-gradient(135deg, #1e40af, #2563eb);">
        <h3 style="color: white; margin: 0;"><i class="fas fa-fire"></i> Top sản phẩm cho thuê nhiều nhất</h3>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div class="table-responsive">
            <table class="data-table" id="topProductsTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Sản phẩm</th>
                        <th>Số lần cho thuê</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" style="text-align: center;">Đang tải...</td><\/tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const BASE_URL = '<?php echo BASE_URL; ?>';

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
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Load thống kê
fetch(BASE_URL + '/index.php?url=admin/api/stats')
    .then(res => res.json())
    .then(data => {
        document.getElementById('statProducts').innerText = data.total_products || 0;
        document.getElementById('statOrders').innerText = data.total_orders || 0;
        document.getElementById('statUsers').innerText = data.total_users || 0;
        document.getElementById('statRevenue').innerText = formatNumber(data.revenue || 0) + 'đ';
        if(data.pending_transfers !== undefined) {
            document.getElementById('pendingPaymentCount').innerText = data.pending_transfers || 0;
        }
        if(data.pending_deposits !== undefined) {
            document.getElementById('pendingDepositCount').innerText = data.pending_deposits || 0;
        }
    })
    .catch(error => console.error('Lỗi:', error));

// ===== LOAD ĐƠN HÀNG CHỜ XÁC NHẬN CHUYỂN KHOẢN =====
function loadPendingPayments() {
    fetch(BASE_URL + '/index.php?url=admin/api/pending-transfers')
        .then(res => res.json())
        .then(data => {
            if(data.success && data.data.length > 0) {
                document.getElementById('pendingPaymentCount').innerText = data.data.length;
                let html = '';
                data.data.forEach(order => {
                    let amount = order.amount || 0;
                    html += `<tr>
                        <td style="padding: 6px 4px; width: 50px;">#${order.id}</td>
                        <td style="padding: 6px 4px;"><strong>${escapeHtml(order.user_name || 'N/A')}</strong></td>
                        <td style="padding: 6px 4px; text-align: right; width: 80px;">${formatNumber(amount)}đ</td>
                        <td style="padding: 6px 4px; text-align: center; width: 85px;"><button class="btn-confirm-payment" onclick="confirmPaymentFromDashboard(${order.id})">Xác nhận</button></td>
                    </tr>`;
                });
                document.getElementById('pendingPaymentList').innerHTML = html;
            } else {
                document.getElementById('pendingPaymentList').innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center;">✅ Không có đơn hàng nào</td></tr>';
                if(!data.data) document.getElementById('pendingPaymentCount').innerText = '0';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('pendingPaymentList').innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center;">❌ Lỗi tải dữ liệu</td></tr>';
        });
}

// ===== LOAD ĐƠN HÀNG CHỜ HOÀN CỌC =====
function loadPendingDeposits() {
    fetch(BASE_URL + '/index.php?url=admin/api/pending-deposits')
        .then(res => res.json())
        .then(data => {
            if(data.success && data.data.length > 0) {
                document.getElementById('pendingDepositCount').innerText = data.data.length;
                let html = '';
                data.data.forEach(order => {
                    let deposit = order.deposit_amount || 0;
                    html += `<tr>
                        <td style="padding: 6px 4px; width: 50px;">#${order.id}</td>
                        <td style="padding: 6px 4px;"><strong>${escapeHtml(order.user_name || 'N/A')}</strong></td>
                        <td style="padding: 6px 4px; text-align: right; width: 80px;">${formatNumber(deposit)}đ</td>
                        <td style="padding: 6px 4px; text-align: center; width: 85px;"><button class="btn-deposit-small" onclick="processDepositFromDashboard(${order.id})">Kiểm tra</button></td>
                    </tr>`;
                });
                document.getElementById('pendingDepositList').innerHTML = html;
            } else {
                document.getElementById('pendingDepositList').innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center;">✅ Không có đơn hàng nào</td></tr>';
                if(!data.data) document.getElementById('pendingDepositCount').innerText = '0';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('pendingDepositList').innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center;">❌ Lỗi tải dữ liệu</td></tr>';
        });
}

// Xác nhận thanh toán từ dashboard
window.confirmPaymentFromDashboard = function(orderId) {
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
            loadPendingPayments();
            // Cập nhật lại thống kê
            fetch(BASE_URL + '/index.php?url=admin/api/stats')
                .then(res => res.json())
                .then(stats => {
                    document.getElementById('pendingPaymentCount').innerText = stats.pending_transfers || 0;
                });
        } else {
            showToast(data.error || 'Xác nhận thất bại', 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối', 'error'));
};

// Hoàn cọc từ dashboard (chuyển sang trang chi tiết đơn hàng)
window.processDepositFromDashboard = function(orderId) {
    window.location.href = BASE_URL + '/index.php?url=admin/orders/detail&id=' + orderId;
};

// Load biểu đồ
fetch(BASE_URL + '/index.php?url=admin/api/monthly-revenue')
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: data.data,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                        borderColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatNumber(context.raw) + 'đ';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatNumber(value) + 'đ';
                                }
                            }
                        }
                    }
                }
            });
        }
    })
    .catch(err => console.error('Lỗi load biểu đồ:', err));

// Load top sản phẩm
fetch(BASE_URL + '/index.php?url=admin/api/top-products')
    .then(res => res.json())
    .then(data => {
        if(data.success && data.data.length > 0) {
            let html = '';
            data.data.forEach((product, index) => {
                html += `<tr>
                    <td>${index + 1}</td>
                    <td><strong>${escapeHtml(product.name)}</strong></td>
                    <td>${product.total_rented || 0} lần</td>
                    <td>${formatNumber(product.total_revenue || 0)}đ</td>
                </tr>`;
            });
            document.querySelector('#topProductsTable tbody').innerHTML = html;
        } else {
            document.querySelector('#topProductsTable tbody').innerHTML = '<tr><td colspan="4" style="text-align: center;">📭 Chưa có dữ liệu</td></tr>';
        }
    })
    .catch(err => {
        console.error('Lỗi load top products:', err);
        document.querySelector('#topProductsTable tbody').innerHTML = '<tr><td colspan="4" style="text-align: center;">❌ Lỗi tải dữ liệu</td></tr>';
    });

// Khởi tạo
loadPendingPayments();
loadPendingDeposits();
</script>

<style>
.btn-confirm-payment {
    background: #209a71;
    color: white;
    border: none;
    padding: 4px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 11px;
}
.btn-confirm-payment:hover {
    background: #1da87c;
}
.btn-deposit-small {
    background: #315dbb;
    color: white;
    border: none;
    padding: 4px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 11px;
}
.btn-deposit-small:hover {
    background: #3960ca;
}
/* Khung đẹp cho dashboard */
.row .card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}
.row .card-header {
    border-bottom: 1px solid #e2e8f0;
}
.row table {
    border-collapse: collapse;
}
.row table th {
    font-weight: 600;
    color: #1e293b;
    border-bottom: 1px solid #638ec7;
}
.row table td {
    border-bottom: 1px solid #a2bfdc;
}
.row table tr:last-child td {
    border-bottom: none;
}
.row table tr:hover td {
    background: #b9d6f4;
}
</style>