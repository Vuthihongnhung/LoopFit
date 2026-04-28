// assets/user/js/orders.js
$(document).ready(function() {
    if ($('#orders-list-container').length === 0) {
        return;
    }
    let currentStatus = 'all';
    let isLoading = false;

    // Xác định BASE_URL
    let BASE_URL = $('meta[name="base-url"]').attr('content') || '';
    if (!BASE_URL) {
        let path = window.location.pathname;
        if (path.includes('/LoopFit/public')) BASE_URL = '/LoopFit/public';
        else if (path.includes('/public')) BASE_URL = '/public';
        else BASE_URL = '';
    }

    function loadOrders(status) {
        if (isLoading) return;
        isLoading = true;
        $('#orders-list-container').html('<div class="loading-spinner">Đang tải...</div>');

        $.ajax({
            url: BASE_URL + '/api/orders',
            type: 'GET',
            data: { status: status },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    renderOrders(res.data);
                } else {
                    $('#orders-list-container').html('<p class="error-message">' + (res.message || 'Không thể tải đơn hàng') + '</p>');
                    Toast.error(res.message || 'Không thể tải đơn hàng', 'Lỗi');
                }
            },
            error: function() {
                $('#orders-list-container').html('<p class="error-message">Lỗi kết nối server. Vui lòng thử lại.</p>');
                Toast.error('Lỗi kết nối server', 'Lỗi');
            },
            complete: function() {
                isLoading = false;
            }
        });
    }

    function renderOrders(orders) {
        if (!orders.length) {
            $('#orders-list-container').html('<div class="empty-orders">Bạn chưa có đơn hàng nào.</div>');
            return;
        }

        let html = '';
        orders.forEach(order => {
            let detailUrl = BASE_URL + '/order/' + order.id;
            html += `
                <div class="order-card" data-order-id="${order.id}">
                    <div class="order-header">
                        <span class="order-code">Mã đơn: ${order.order_code}</span>
                        <span class="order-date">${formatDate(order.created_at)}</span>
                        <span class="order-status status-${order.status_slug}">${order.status_name}</span>
                    </div>
                    <div class="order-items">
                        ${renderOrderItems(order.items)}
                    </div>
                    <div class="order-footer">
                        <div class="order-total">Tổng tiền: <strong>${order.final_amount_formatted}đ</strong></div>
                        <div class="order-actions">
                            <a href="${detailUrl}" class="btn-detail">Xem chi tiết</a>
                            ${canCancel(order.status_slug) ? `<button class="btn-cancel" data-id="${order.id}">Hủy đơn</button>` : ''}
                            ${canReturn(order.status_slug) ? `<button class="btn-return" data-id="${order.id}">Yêu cầu hoàn trả</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        $('#orders-list-container').html(html);

        // Gắn sự kiện hủy đơn
        $('.btn-cancel').click(function() {
            let orderId = $(this).data('id');
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                cancelOrder(orderId);
            }
        });

        // Gắn sự kiện yêu cầu hoàn trả
        $('.btn-return').click(function() {
            let orderId = $(this).data('id');
            if (confirm('Xác nhận yêu cầu hoàn trả?')) {
                returnOrder(orderId);
            }
        });
    }

    function renderOrderItems(items) {
        if (!items.length) return '';
        let html = '<div class="items-list">';
        items.forEach(item => {
            html += `
                <div class="order-item">
                    <img src="${item.product_image}" alt="${item.product_name}" class="item-img">
                    <div class="item-info">
                        <div class="item-name">${item.product_name}</div>
                        <div class="item-meta">Số lượng: ${item.quantity} x ${item.price_per_day}đ/ngày</div>
                        <div class="item-dates">${item.rental_start} → ${item.rental_end}</div>
                    </div>
                    <div class="item-subtotal">${item.subtotal}đ</div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        let date = new Date(dateStr);
        return `${date.getDate()}/${date.getMonth()+1}/${date.getFullYear()}`;
    }

    function canCancel(status) {
        return ['pending', 'processing'].includes(status);
    }

    function canReturn(status) {
        return status === 'renting';
    }

    function cancelOrder(orderId) {
        $.ajax({
            url: BASE_URL + '/api/order/cancel',
            type: 'POST',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Toast.success(res.message, 'Thành công');
                    loadOrders(currentStatus);
                } else {
                    Toast.error(res.message, 'Lỗi');
                }
            },
            error: function() {
                Toast.error('Lỗi kết nối, vui lòng thử lại', 'Lỗi');
            }
        });
    }

    function returnOrder(orderId) {
        $.ajax({
            url: BASE_URL + '/api/order/return',
            type: 'POST',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Toast.success(res.message, 'Thành công');
                    loadOrders(currentStatus);
                } else {
                    Toast.error(res.message, 'Lỗi');
                }
            },
            error: function() {
                Toast.error('Lỗi kết nối, vui lòng thử lại', 'Lỗi');
            }
        });
    }

    // Xử lý click tab
    $('.tab-btn').click(function() {
        let status = $(this).data('status');
        if (status === currentStatus) return;
        currentStatus = status;
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        loadOrders(status);
    });

    // Load lần đầu
    loadOrders('all');
});