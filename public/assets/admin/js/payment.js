document.addEventListener('DOMContentLoaded', function() {
    let paymentsData = [];

    function formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    function formatDate(dateString) {
        if (!dateString) return '---';
        let date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    /* ===== Lấy badge (giao diện) cho trạng thái thanh toán ===== */
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

    function getMethodText(method) {
        return method === 'cash' ? 'Tiền mặt' : 'Chuyển khoản';
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
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    /* ===== Tải danh sách thanh toán từ API dựa trên bộ lọc (trạng thái, phương thức) ===== */
    function loadPayments() {
        const status = document.getElementById('statusFilter').value;
        const method = document.getElementById('methodFilter').value;
        const url = `${BASE_URL}/admin/api/payments?status=${status}&method=${method}`;
        console.log('Fetching:', url);
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                console.log('API response:', data);
                if(data.success) {
                    paymentsData = data.data;
                    renderPaymentsTable();
                    updateStatistics();
                    document.getElementById('orderCount').innerText = paymentsData.length + ' đơn';
                } else {
                    document.getElementById('paymentsTableBody').innerHTML = '<tr><td colspan="8" style="text-align: center; color: red;">❌ Lỗi tải dữ liệu</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('paymentsTableBody').innerHTML = '<tr><td colspan="8" style="text-align: center; color: red;">❌ Lỗi kết nối</td></tr>';
            });
    }

    /* ===== Render bảng danh sách thanh toán ===== */
    function renderPaymentsTable() {
        let html = '';
        if(paymentsData.length > 0) {
            paymentsData.forEach(payment => {
                const amount = parseFloat(payment.amount) || 0;
                let actionHtml = '';
                
                if (payment.payment_status === 'pending_verification' && payment.payment_method === 'transfer') {
                    actionHtml = `<button class="btn-icon confirm-payment" data-id="${payment.id}" title="Xác nhận đã nhận tiền chuyển khoản">
                                    <i class="fas fa-check-circle"></i> Xác nhận
                                </button>`;
                } else if (payment.payment_method === 'cash' && payment.payment_status === 'unpaid') {
                    actionHtml = `<button class="btn-icon confirm-payment" data-id="${payment.id}" title="Xác nhận thanh toán tiền mặt">
                                    <i class="fas fa-money-bill"></i> Xác nhận
                                </button>`;
                } else if (payment.payment_status === 'paid') {
                    actionHtml = `<span style="color: #10b981; font-size: 12px;">✓ Đã thanh toán</span>`;
                } else {
                    actionHtml = `<span style="color: #94a3b8; font-size: 12px;">---</span>`;
                }
                
                html += `<tr>
                    <td><strong>#${payment.id}</strong></td>
                    <td>${escapeHtml(payment.user_name || 'N/A')}</td>
                    <td>${escapeHtml(payment.phone || '---')}</td>
                    <td><strong>${formatNumber(amount)}đ</strong></td>
                    <td>${getMethodText(payment.payment_method)}</td>
                    <td>${formatDate(payment.created_at)}</td>
                    <td>${getPaymentBadge(payment.payment_status)}</td>
                    <td>${actionHtml}</td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="8" style="text-align: center;">📭 Không có đơn hàng nào</td></tr>';
        }
        document.getElementById('paymentsTableBody').innerHTML = html;
        
        // Gắn sự kiện cho nút xác nhận
        document.querySelectorAll('.confirm-payment').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.dataset.id;
                confirmPayment(orderId);
            });
        });
    }

    /* ===== Cập nhật thống kê: số đơn chờ xác nhận, đã thanh toán, chưa thanh toán ===== */
    function updateStatistics() {
        const pendingCount = paymentsData.filter(p => p.payment_status === 'pending_verification').length;
        const paidCount = paymentsData.filter(p => p.payment_status === 'paid').length;
        const unpaidCount = paymentsData.filter(p => p.payment_status === 'unpaid').length;
        
        document.getElementById('statPendingVerification').innerText = pendingCount;
        document.getElementById('statPaid').innerText = paidCount;
        document.getElementById('statUnpaid').innerText = unpaidCount;
    }

    /* ===== Xác nhận thanh toán cho một đơn hàng (cập nhật trạng thái thành 'paid') ===== */
    function confirmPayment(orderId) {
        if (!confirm('Xác nhận đã nhận tiền từ khách hàng?')) return;
        
        fetch(BASE_URL + '/admin/payments/confirm', {
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

    /* ===== Gắn sự kiện cho các bộ lọc và nút làm mới ===== */
    document.getElementById('statusFilter')?.addEventListener('change', loadPayments);
    document.getElementById('methodFilter')?.addEventListener('change', loadPayments);
    document.getElementById('refreshBtn')?.addEventListener('click', loadPayments);
    
    loadPayments();
});