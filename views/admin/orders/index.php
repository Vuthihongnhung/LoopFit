<?php 
$pageTitle = 'Quản lý đơn hàng'; 
$activeMenu = 'orders';
?>
<div class="orders-page">
    <div class="page-header">
        <h1><i class="fas fa-shopping-cart"></i> Đơn hàng</h1>
        <div class="search-box">
            <input type="text" id="searchOrder" placeholder="🔍 Tìm kiếm theo ID hoặc khách hàng..." class="form-control">
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách đơn hàng</h3>
            <span class="badge active" id="orderCount">0 đơn hàng</span>
        </div>
        <div class="card-body" style="display: flex; justify-content: center; padding: 20px;">
            <div style="width: 100%; max-width: 1200px; overflow-x: auto;">
                <table class="data-table" id="ordersTable" style="width: 100%; margin: 0 auto;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Ngày thuê</th>
                            <th>Ngày trả</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody><tr><td colspan="7">Đang tải...</td></tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<style>
.search-box {
    margin-left: auto;
}
.search-box input {
    border-radius: 40px;
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    width: 250px;
}
</style>

<div id="orderDetailModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header"><h2><i class="fas fa-info-circle"></i> Chi tiết đơn hàng</h2><span class="close">&times;</span></div>
        <div class="modal-body" id="orderDetailBody"></div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
let ordersData = [];

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
    
    let filtered = ordersData.filter(order => 
        order.id.toString().includes(searchTerm) || 
        (order.user_name || '').toLowerCase().includes(searchTerm)
    );
    
    document.getElementById('orderCount').innerText = `${filtered.length} đơn hàng`;
    
    let html = '';
    if(filtered.length > 0) {
        filtered.forEach(order => {
            let statusClass = order.status === 'pending' ? 'pending' : 
                             (order.status === 'confirmed' ? 'confirmed' : 
                             (order.status === 'renting' ? 'renting' : 'returned'));
            let statusText = order.status === 'pending' ? '⏳ Chờ duyệt' : 
                            (order.status === 'confirmed' ? '✅ Đã xác nhận' : 
                            (order.status === 'renting' ? '🚚 Đang thuê' : '📦 Đã trả'));
            
            html += `<tr>
                <td>#${order.id}</td>
                <td><strong>${order.user_name || 'N/A'}</strong></td>
                <td>${order.rent_date || '-'}</td>
                <td>${order.return_date || '-'}</td>
                <td><strong>${new Intl.NumberFormat('vi-VN').format(order.total_price)}đ</strong></td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn-icon view-order" data-id="${order.id}" title="Xem chi tiết"><i class="fas fa-eye"></i></button>
                 </td>
             </tr>`;
        });
    } else {
        html = '<tr><td colspan="7" style="text-align: center;">📭 Không có đơn hàng nào</td></tr>';
    }
    document.querySelector('#ordersTable tbody').innerHTML = html;
    attachEvents();
}

function attachEvents() {
    document.querySelectorAll('.view-order').forEach(btn => {
        btn.onclick = () => viewOrderDetail(btn.dataset.id);
    });
}

function viewOrderDetail(id) {
    fetch(BASE_URL + `/index.php?url=admin/api/order/detail?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                let itemsHtml = '';
                data.items.forEach(item => {
                    itemsHtml += `<tr><td>${item.product_name}</td><td>${item.quantity}</td><td>${new Intl.NumberFormat('vi-VN').format(item.price)}đ</td></tr>`;
                });
                let html = `
                    <p><strong>Mã đơn:</strong> #${data.order.id}</p>
                    <p><strong>Khách hàng:</strong> ${data.order.user_name}</p>
                    <p><strong>Email:</strong> ${data.order.email}</p>
                    <p><strong>Ngày thuê:</strong> ${data.order.rent_date}</p>
                    <p><strong>Ngày trả:</strong> ${data.order.return_date}</p>
                    <p><strong>Tổng tiền:</strong> <strong style="color:#2563eb;">${new Intl.NumberFormat('vi-VN').format(data.order.total_price)}đ</strong></p>
                    <p><strong>Trạng thái:</strong> <span class="badge ${data.order.status}">${data.order.status}</span></p>
                    <h3>Sản phẩm</h3>
                    <table class="data-table"><thead><tr><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th></tr></thead><tbody>${itemsHtml}</tbody></table>
                `;
                document.getElementById('orderDetailBody').innerHTML = html;
                document.getElementById('orderDetailModal').style.display = 'block';
            }
        });
}

document.getElementById('searchOrder')?.addEventListener('keyup', renderOrdersTable);
document.querySelectorAll('.close').forEach(el => {
    el.onclick = () => document.getElementById('orderDetailModal').style.display = 'none';
});
window.onclick = (e) => {
    if(e.target === document.getElementById('orderDetailModal')) {
        document.getElementById('orderDetailModal').style.display = 'none';
    }
};
loadOrders();
</script>