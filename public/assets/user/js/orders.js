$(document).ready(function() {
    if ($('#orders-list-container').length === 0) return;

    let currentStatus = 'all';
    let isLoading = false;

    let BASE_URL = window.BASE_URL || '';
    if (!BASE_URL) {
        let protocol = window.location.protocol; 
        let host = window.location.host;         
        let pathname = window.location.pathname;
        let basePath = '';
        if (pathname.includes('/LoopFit/public')) {
            basePath = '/LoopFit/public';
        } else if (pathname.includes('/public')) {
            basePath = '/public';
        }
        BASE_URL = protocol + '//' + host + basePath;
    }
    console.log('BASE_URL:', BASE_URL);

    // Map trạng thái
    const statusLabels = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao',
        'renting': 'Đang thuê',
        'returning': 'Đang hoàn trả',
        'completed': 'Hoàn tất',
        'cancelled': 'Đã hủy'
    };

    const cancelableStatuses = ['pending', 'confirmed'];
    const returnableStatus = 'renting';

    function showToast(type, message, title = '') {
        if (typeof Toast !== 'undefined') {
            if (type === 'success') Toast.success(message, title);
            else if (type === 'error') Toast.error(message, title);
            else Toast.info(message, title);
        } else {
            alert(message);
        }
    }

    function loadOrders(status) {
        if (isLoading) return;
        isLoading = true;
        $('#orders-list-container').html('<div class="loading-spinner">Đang tải đơn hàng...</div>');

        var url = BASE_URL + '/api/user/orders';
        console.log('Calling API:', url, 'status:', status);

        $.ajax({
            url: url,
            type: 'GET',
            data: { status: status },
            dataType: 'json',
            success: function(res) {
                console.log('API response:', res);
                if (res.success) {
                    renderOrders(res.orders);
                } else {
                    $('#orders-list-container').html('<p class="error-message">' + (res.message || 'Không thể tải đơn hàng') + '</p>');
                    showToast('error', res.message || 'Không thể tải đơn hàng', 'Lỗi');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr.responseText);
                $('#orders-list-container').html('<p class="error-message">Lỗi kết nối server (' + xhr.status + '). Vui lòng thử lại.</p>');
                showToast('error', 'Lỗi kết nối server', 'Lỗi');
            },
            complete: function() {
                isLoading = false;
            }
        });
    }

    function renderOrders(orders) {
        if (!orders || orders.length === 0) {
            $('#orders-list-container').html('<div class="empty-orders">Bạn chưa có đơn hàng nào.</div>');
            return;
        }

        let html = '';
        orders.forEach(order => {
            const statusSlug = order.status;
            const statusName = statusLabels[statusSlug] || statusSlug;
            const createdDate = order.created_at ? new Date(order.created_at).toLocaleDateString('vi-VN') : '';

            html += `
                <div class="order-card" data-order-id="${order.id}">
                    <div class="order-header">
                        <div>
                            <strong>Mã đơn: #${order.id}</strong><br>
                            <small>${createdDate}</small>
                        </div>
                        <div class="order-status">
                            <span class="badge badge-${statusSlug}">${statusName}</span>
                        </div>
                    </div>
                    <div class="order-items">
            `;
            if (order.items && order.items.length) {
                order.items.forEach(item => {
                    let productId = item.product_id || item.id || 0;
                    if (!productId) {
                        console.warn('Missing product_id for item', item);
                    }

                    let imageUrl = item.product_image || item.image || (BASE_URL + '/assets/user/images/default-product.png');
                    if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                        imageUrl = BASE_URL + '/uploads/' + imageUrl;
                    }
                    const startDate = item.rental_start || '';
                    const endDate = item.rental_end || '';
                    const pricePerDayFormatted = (item.price_per_day || 0).toLocaleString('vi-VN');
                    const totalPriceFormatted = (item.total_price || 0).toLocaleString('vi-VN');
                    
                    let reviewButton = '';
                    if (statusSlug === 'completed' && productId) {
                        reviewButton = `<button class="btn-review" data-order-id="${order.id}" data-product-id="${productId}">📝 Đánh giá</button>`;
                    }
                    
                    html += `
                        <div class="order-item">
                            <img src="${imageUrl}" alt="${escapeHtml(item.product_name)}" class="item-img">
                            <div class="item-info">
                                <div class="item-name">${escapeHtml(item.product_name)}</div>
                                <div class="item-meta">Số lượng: ${item.quantity} x ${pricePerDayFormatted}đ/ngày</div>
                                <div class="item-dates">${startDate} → ${endDate}</div>
                            </div>
                            <div class="item-subtotal">${totalPriceFormatted}đ</div>
                            ${reviewButton}
                        </div>
                    `;
                });
            } else {
                html += `<div class="no-items">Không có sản phẩm</div>`;
            }
            html += `
                    </div>
                    <div class="order-footer">
                        <div class="order-total">Tổng tiền: <strong>${(order.total_amount || 0).toLocaleString('vi-VN')}đ</strong></div>
                        <div class="order-actions">
                            <a href="${BASE_URL}/order/${order.id}" class="btn-detail">Xem chi tiết</a>
                            ${cancelableStatuses.includes(statusSlug) ? `<button class="btn-cancel" data-id="${order.id}">Hủy đơn</button>` : ''}
                            ${statusSlug === returnableStatus ? `<button class="btn-return" data-id="${order.id}">Yêu cầu hoàn trả</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        $('#orders-list-container').html(html);

        $('.btn-cancel').off('click').on('click', function() {
            let orderId = $(this).data('id');
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                cancelOrder(orderId);
            }
        });

        $('.btn-return').off('click').on('click', function() {
            let orderId = $(this).data('id');
            if (confirm('Xác nhận yêu cầu hoàn trả?')) {
                returnOrder(orderId);
            }
        });

        $('.btn-review').off('click').on('click', function(e) {
            e.preventDefault();
            let orderId = $(this).data('order-id');
            let productId = $(this).data('product-id');
            if (!productId) {
                showToast('error', 'Không tìm thấy mã sản phẩm, không thể đánh giá.', 'Lỗi');
                return;
            }
            let url = BASE_URL + '/review/order/' + orderId + '/product/' + productId;
            console.log('Redirecting to:', url);
            window.location.href = url;
        });
    }

    function cancelOrder(orderId) {
        $.ajax({
            url: BASE_URL + '/api/user/orders/cancel',
            type: 'POST',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('success', res.message, 'Thành công');
                    loadOrders(currentStatus);
                } else {
                    showToast('error', res.message, 'Lỗi');
                }
            },
            error: function() {
                showToast('error', 'Lỗi kết nối, vui lòng thử lại', 'Lỗi');
            }
        });
    }

    function returnOrder(orderId) {
        $.ajax({
            url: BASE_URL + '/api/user/orders/return',
            type: 'POST',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('success', res.message, 'Thành công');
                    loadOrders(currentStatus);
                } else {
                    showToast('error', res.message, 'Lỗi');
                }
            },
            error: function() {
                showToast('error', 'Lỗi kết nối, vui lòng thử lại', 'Lỗi');
            }
        });
    }

    $('.tab-btn').click(function() {
        let status = $(this).data('status');
        if (status === currentStatus) return;
        currentStatus = status;
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        loadOrders(status);
    });

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    loadOrders('all');
});