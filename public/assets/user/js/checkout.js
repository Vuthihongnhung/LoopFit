// assets/user/js/checkout.js
$(document).ready(function() {
    // ========== BIẾN TOÀN CỤC ==========
    let totalRent = parseFloat($('#total_rent_value').val()) || 0;
    let totalDeposit = parseFloat($('#total_deposit_value').val()) || 0;
    let baseTotal = totalRent + totalDeposit;
    let currentDiscount = 0;
    let shippingFee = 0;
    let addresses = [];

    function formatMoney(amount) {
        return amount.toLocaleString('vi-VN') + 'đ';
    }

    // Tính phí vận chuyển dựa trên total_rent (tiền thuê)
    function calculateShippingFee(rentAmount) {
        return rentAmount < 400000 ? 30000 : 0;
    }

    // Khởi tạo phí ship ban đầu
    shippingFee = calculateShippingFee(totalRent);

    // Cập nhật tổng tiền sau discount và shipping
    function updateTotalDisplay() {
        let finalTotal = baseTotal - currentDiscount + shippingFee;
        $('#final-total').text(formatMoney(finalTotal));
        $('#discount-amount').text(formatMoney(currentDiscount));
        $('#shipping-fee').text(formatMoney(shippingFee));
        if (currentDiscount > 0) {
            $('.discount-row').show();
        } else {
            $('.discount-row').hide();
        }
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

    // Xác định BASE_URL
    let BASE_URL = '';
    var baseMeta = $('meta[name="base-url"]').attr('content');
    if (baseMeta) {
        BASE_URL = baseMeta;
    } else {
        let path = window.location.pathname;
        if (path.startsWith('/LoopFit/public')) {
            BASE_URL = '/LoopFit/public';
        } else {
            BASE_URL = '';
        }
    }

    // ========== QUẢN LÝ ĐỊA CHỈ ==========
    function loadAddresses() {
        if (!window.isLoggedIn) {
            $('#checkout-address-list').html('<p class="error">Vui lòng <a href="' + BASE_URL + '/login">đăng nhập</a> để chọn địa chỉ.</p>');
            return;
        }
        $('#checkout-address-list').html('<div class="loading">Đang tải...</div>');
        $.ajax({
            url: BASE_URL + '/api/addresses',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    addresses = res.data;
                    renderAddressList(addresses);
                } else {
                    $('#checkout-address-list').html('<p class="error">' + (res.message || 'Lỗi tải địa chỉ') + '</p>');
                    if (window.Toast) Toast.error('Lỗi tải địa chỉ');
                }
            },
            error: function() {
                $('#checkout-address-list').html('<p class="error">Lỗi kết nối</p>');
                if (window.Toast) Toast.error('Lỗi kết nối server');
            }
        });
    }

    function renderAddressList(addresses) {
        if (!addresses.length) {
            $('#checkout-address-list').html(`
                <div class="no-address-box">
                    <p>Bạn chưa có địa chỉ nào.</p>
                </div>
            `);
            return;
        }
        let html = '';
        $.each(addresses, function(index, addr) {
            let isDefault = (addr.is_default == 1);
            let checkedAttr = (index === 0 || isDefault) ? 'checked' : '';
            html += `
                <label class="address-item ${isDefault ? 'default' : ''}" data-id="${addr.id}">
                    <input type="radio" name="address_id" value="${addr.id}" ${checkedAttr} required>
                    <div class="address-details">
                        <span class="address-name">${escapeHtml(addr.full_name)} - ${escapeHtml(addr.phone)}</span>
                        <span class="address-full">${escapeHtml(addr.address_line)}</span>
                        ${isDefault ? '<span class="default-badge">(Mặc định)</span>' : ''}
                    </div>
                    <div class="address-actions">
                        <button type="button" class="edit-address-btn" data-id="${addr.id}">Sửa</button>
                        <button type="button" class="delete-address-btn" data-id="${addr.id}">Xóa</button>
                        ${!isDefault ? `<button type="button" class="set-default-btn" data-id="${addr.id}">Đặt mặc định</button>` : ''}
                    </div>
                </label>
            `;
        });
        $('#checkout-address-list').html(html);
        bindAddressEvents();
        $('.address-item input:checked').closest('.address-item').addClass('selected');
    }

    function bindAddressEvents() {
        $('.address-item').off('click').on('click', function(e) {
            if ($(e.target).is('button')) return;
            $(this).find('input[type="radio"]').prop('checked', true);
            $('.address-item').removeClass('selected');
            $(this).addClass('selected');
        });
        $('.edit-address-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            let id = $(this).data('id');
            openAddressModal(id);
        });
        $('.delete-address-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            let id = $(this).data('id');
            if (confirm('Bạn có chắc muốn xóa địa chỉ này?')) {
                $.ajax({
                    url: BASE_URL + '/api/address/delete',
                    type: 'POST',
                    data: JSON.stringify({ id: id }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            loadAddresses();
                            if (window.Toast) Toast.success('Xóa địa chỉ thành công');
                        } else {
                            if (window.Toast) Toast.error(res.message || 'Xóa thất bại');
                        }
                    },
                    error: function() { if (window.Toast) Toast.error('Lỗi xóa địa chỉ'); }
                });
            }
        });
        $('.set-default-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            let id = $(this).data('id');
            $.ajax({
                url: BASE_URL + '/api/address/set-default',
                type: 'POST',
                data: JSON.stringify({ id: id }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        loadAddresses();
                        if (window.Toast) Toast.success('Đặt địa chỉ mặc định thành công');
                    } else {
                        if (window.Toast) Toast.error(res.message || 'Thất bại');
                    }
                },
                error: function() { if (window.Toast) Toast.error('Lỗi đặt mặc định'); }
            });
        });
    }

    // ========== MODAL ĐỊA CHỈ ==========
    const $modal = $('#address-modal');
    const $modalTitle = $('#modal-title');
    const $editId = $('#edit-address-id');
    const $addrFullname = $('#addr-fullname');
    const $addrPhone = $('#addr-phone');
    const $addrLine = $('#addr-line');
    const $addrDefault = $('#addr-default');

    function openAddressModal(addressId = null) {
        $modalTitle.text(addressId ? 'Sửa địa chỉ' : 'Thêm địa chỉ mới');
        $editId.val(addressId || '');
        if (addressId) {
            let addr = addresses.find(a => a.id == addressId);
            if (addr) {
                $addrFullname.val(addr.full_name);
                $addrPhone.val(addr.phone);
                $addrLine.val(addr.address_line);
                $addrDefault.prop('checked', addr.is_default == 1);
            } else {
                if (window.Toast) Toast.error('Không tìm thấy địa chỉ');
                return;
            }
        } else {
            $addrFullname.val('');
            $addrPhone.val('');
            $addrLine.val('');
            $addrDefault.prop('checked', false);
        }
        $modal.css('display', 'flex');
    }

    function closeModal() {
        $modal.hide();
    }

    $('#checkout-add-address').on('click', function() { openAddressModal(); });
    $('.modal-close, #modal-close-btn').on('click', closeModal);
    $(window).on('click', function(e) { if ($(e.target).is($modal)) closeModal(); });

    $('#modal-save').on('click', function() {
        let id = $editId.val();
        let data = {
            full_name: $addrFullname.val().trim(),
            phone: $addrPhone.val().trim(),
            address_line: $addrLine.val().trim(),
            is_default: $addrDefault.is(':checked') ? 1 : 0
        };
        if (!data.full_name || !data.phone || !data.address_line) {
            if (window.Toast) Toast.warning('Vui lòng nhập đầy đủ thông tin');
            return;
        }
        let url = id ? BASE_URL + '/api/address/update' : BASE_URL + '/api/address/add';
        if (id) data.id = id;
        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    closeModal();
                    loadAddresses();
                    if (window.Toast) Toast.success(id ? 'Cập nhật địa chỉ thành công' : 'Thêm địa chỉ thành công');
                } else {
                    if (window.Toast) Toast.error(res.message || 'Lưu thất bại');
                }
            },
            error: function() { if (window.Toast) Toast.error('Lỗi lưu địa chỉ'); }
        });
    });

    // ========== VOUCHER ==========
    $('#apply-voucher').click(function() {
        let code = $('#voucher-code').val().trim();
        if (!code) {
            $('#voucher-message').text('Vui lòng nhập mã giảm giá').css('color', 'red');
            if (window.Toast) Toast.warning('Vui lòng nhập mã giảm giá');
            return;
        }
        // Gửi total_rent (tiền thuê) để backend tính giảm giá chỉ trên tiền thuê
        $.ajax({
            url: BASE_URL + '/checkout/apply-voucher',
            type: 'POST',
            data: { code: code, total_amount: totalRent },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    currentDiscount = parseFloat(res.discount);
                    updateTotalDisplay();
                    $('#voucher-message').text('Áp dụng thành công! Đã giảm ' + formatMoney(currentDiscount)).css('color', 'green');
                    if (window.Toast) Toast.success('Áp dụng mã giảm giá thành công');
                } else {
                    $('#voucher-message').text(res.message).css('color', 'red');
                    currentDiscount = 0;
                    updateTotalDisplay();
                    if (window.Toast) Toast.error(res.message);
                }
            },
            error: function() {
                $('#voucher-message').text('Lỗi kết nối, vui lòng thử lại').css('color', 'red');
                if (window.Toast) Toast.error('Lỗi kết nối server');
            }
        });
    });

    // ========== ĐẶT HÀNG ==========
    $('#checkout-form').submit(function(e) {
        e.preventDefault();
        if ($('input[name="address_id"]:checked').length === 0) {
            if (window.Toast) Toast.warning('Vui lòng chọn địa chỉ nhận hàng');
            return;
        }
        let paymentMethod = $('input[name="payment_method"]:checked').val();
        let formData = $(this).serialize();
        formData += '&discount=' + currentDiscount;
        formData += '&payment_method=' + paymentMethod;
        formData += '&shipping_fee=' + shippingFee;

        $.ajax({
            url: BASE_URL + '/checkout/process',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    if (paymentMethod === 'cash') {
                        if (window.Toast) Toast.success('Đặt hàng thành công! Mã đơn: ' + res.order_id);
                        window.location.href = BASE_URL + '/order/success?order_id=' + res.order_id;
                    } else {
                        window.location.href = BASE_URL + '/user/payment/qr?order_id=' + res.order_id;
                    }
                } else {
                    if (window.Toast) Toast.error(res.message || 'Đặt hàng thất bại');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Có lỗi xảy ra, vui lòng thử lại';
                if (window.Toast) Toast.error(msg);
            }
        });
    });

    loadAddresses();
    updateTotalDisplay();
});