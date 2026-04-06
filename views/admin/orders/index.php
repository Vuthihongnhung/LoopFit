<?php $pageTitle = 'Quản lý đơn hàng'; $activeMenu = 'orders'; ?>
<div class="orders-page">
    <div class="page-header">
        <h1>Đơn hàng</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="data-table" id="ordersTable">
                <thead>
                    <tr><th>ID</th><th>Người dùng</th><th>Ngày thuê</th><th>Ngày trả</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thao tác</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div id="orderDetailModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header"><h2>Chi tiết đơn hàng</h2><span class="close">&times;</span></div>
        <div class="modal-body" id="orderDetailBody"></div>
    </div>
</div>
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    function loadOrders() {
        fetch(BASE_URL + '/admin/api/orders')
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data.data && data.data.length) {
                    data.data.forEach(order => {
                        html += `<tr>
                            <td>#${order.id}</td>
                            <td>${order.user_name || order.user_email}</td>
                            <td>${order.rent_date}</td>
                            <td>${order.return_date}</td>
                            <td>${new Intl.NumberFormat('vi-VN').format(order.total_price)}đ</td>
                            <td><span class="badge ${order.status}">${order.status}</span></td>
                            <td>
                                <button class="btn-icon view-order" data-id="${order.id}"><i class="fas fa-eye"></i></button>
                                <select class="status-update" data-id="${order.id}">
                                    <option value="pending" ${order.status=='pending'?'selected':''}>Chờ duyệt</option>
                                    <option value="confirmed" ${order.status=='confirmed'?'selected':''}>Xác nhận</option>
                                    <option value="renting" ${order.status=='renting'?'selected':''}>Đang thuê</option>
                                    <option value="returned" ${order.status=='returned'?'selected':''}>Đã trả</option>
                                    <option value="cancelled" ${order.status=='cancelled'?'selected':''}>Hủy</option>
                                </select>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="7">Không có đơn hàng nào</td></tr>';
                }
                document.querySelector('#ordersTable tbody').innerHTML = html;
                attachOrderEvents();
            });
    }
    function attachOrderEvents() {
        document.querySelectorAll('.view-order').forEach(btn => {
            btn.onclick = () => viewOrderDetail(btn.dataset.id);
        });
        document.querySelectorAll('.status-update').forEach(select => {
            select.onchange = () => updateOrderStatus(select.dataset.id, select.value);
        });
    }
    function viewOrderDetail(id) {
        fetch(BASE_URL + `/admin/api/order/detail?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return alert('Không thể tải chi tiết');
                let html = `<p><strong>Mã đơn:</strong> #${data.order.id}</p>
                            <p><strong>Khách hàng:</strong> ${data.order.user_name}</p>
                            <p><strong>Địa chỉ:</strong> ${data.order.address}</p>
                            <h3>Sản phẩm</h3>
                            <table class="data-table">
                                <thead><tr><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th></tr></thead>
                                <tbody>`;
                data.items.forEach(item => {
                    html += `<tr><td>${item.product_name}</td><td>${item.quantity}</td><td>${new Intl.NumberFormat('vi-VN').format(item.price)}đ</td></tr>`;
                });
                html += `</tbody></table>`;
                document.getElementById('orderDetailBody').innerHTML = html;
                document.getElementById('orderDetailModal').style.display = 'block';
            });
    }
    function updateOrderStatus(id, status) {
        fetch(BASE_URL + '/admin/api/order/update-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&status=${status}`
        }).then(res => res.json()).then(data => {
            if(data.success) loadOrders();
            else alert('Cập nhật thất bại');
        });
    }
    loadOrders();
    // modal close
    document.querySelectorAll('.close').forEach(el => {
        el.onclick = () => document.getElementById('orderDetailModal').style.display = 'none';
    });
    window.onclick = function(e) {
        let modal = document.getElementById('orderDetailModal');
        if (e.target == modal) modal.style.display = 'none';
    }
</script>