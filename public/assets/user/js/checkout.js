$(document).ready(function() {
    // ========== BIẾN TOÀN CỤC ==========
    let totalRent = parseFloat($('#total_rent_value').val()) || 0;
    let totalDeposit = parseFloat($('#total_deposit_value').val()) || 0;
    let originalTotal = totalRent + totalDeposit;
    let currentDiscount = 0;
    let addresses = []; // lưu danh sách địa chỉ để dùng cho modal sửa

    // Helper format tiền
    function formatMoney(amount) {
        return amount.toLocaleString('vi-VN') + 'đ';
    }

    // Cập nhật tổng tiền sau discount
    function updateTotalDisplay() {
        let finalTotal = originalTotal - currentDiscount;
        $('#final-total').text(formatMoney(finalTotal));
        $('#discount-amount').text(formatMoney(currentDiscount));
        if (currentDiscount > 0) {
            $('.discount-row').show();
        } else {
            $('.discount-row').hide();
        }
    }

    // Escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // ========== QUẢN LÝ ĐỊA CHỈ ==========
    // Load danh sách địa chỉ từ API
    function loadAddresses() {
        $('#checkout-address-list').html('<div class="loading">Đang tải...</div>');
        $.ajax({
            url: '/LoopFit/public/api/addresses',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    addresses = res.addresses;
                    renderAddressList(addresses);
                } else {
                    $('#checkout-address-list').html('<p class="error">Lỗi tải địa chỉ</p>');
                }
            },
            error: function() {
                $('#checkout-address-list').html('<p class="error">Lỗi kết nối</p>');
            }
        });
    }

    // Render danh sách địa chỉ dạng radio
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
            let checkedAttr = (index === 0 || isDefault) ? 'checked' : ''; // ưu tiên mặc định hoặc đầu tiên
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
        // Gán lại sự kiện click cho các nút (do render lại)
        bindAddressEvents();
        // Highlight selected (nếu có radio checked)
        $('.address-item input:checked').closest('.address-item').addClass('selected');
    }

    // Sự kiện cho các nút trong danh sách địa chỉ
    function bindAddressEvents() {
        // Khi click vào item (trừ nút) thì chọn radio
        $('.address-item').off('click').on('click', function(e) {
            if ($(e.target).is('button')) return; // không can thiệp khi click vào nút
            $(this).find('input[type="radio"]').prop('checked', true);
            $('.address-item').removeClass('selected');
            $(this).addClass('selected');
        });
        // Nút sửa
        $('.edit-address-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            let id = $(this).data('id');
            openAddressModal(id);
        });
        // Nút xóa
        $('.delete-address-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            let id = $(this).data('id');
            if (confirm('Bạn có chắc muốn xóa địa chỉ này?')) {
                $.ajax({
                    url: '/LoopFit/public/api/address/delete',
                    type: 'POST',
                    data: JSON.stringify({ id: id }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            loadAddresses();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function() { alert('Lỗi xóa địa chỉ'); }
                });
            }
        });
        // Nút đặt mặc định
        $('.set-default-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            let id = $(this).data('id');
            $.ajax({
                url: '/LoopFit/public/api/address/set-default',
                type: 'POST',
                data: JSON.stringify({ id: id }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        loadAddresses();
                    } else {
                        alert(res.message);
                    }
                },
                error: function() { alert('Lỗi đặt mặc định'); }
            });
        });
    }

    // ========== MODAL THÊM/SỬA ĐỊA CHỈ ==========
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
            // Lấy thông tin từ mảng addresses đã có
            let addr = addresses.find(a => a.id == addressId);
            if (addr) {
                $addrFullname.val(addr.full_name);
                $addrPhone.val(addr.phone);
                $addrLine.val(addr.address_line);
                $addrDefault.prop('checked', addr.is_default == 1);
            } else {
                alert('Không tìm thấy địa chỉ');
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

    // Lưu địa chỉ (thêm hoặc sửa)
    $('#modal-save').on('click', function() {
        let id = $editId.val();
        let data = {
            full_name: $addrFullname.val().trim(),
            phone: $addrPhone.val().trim(),
            address_line: $addrLine.val().trim(),
            is_default: $addrDefault.is(':checked') ? 1 : 0
        };
        if (!data.full_name || !data.phone || !data.address_line) {
            alert('Vui lòng nhập đầy đủ thông tin');
            return;
        }
        let url = id ? '/LoopFit/public/api/address/update' : '/LoopFit/public/api/address/add';
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
                    loadAddresses(); // reload danh sách
                } else {
                    alert(res.message);
                }
            },
            error: function() { alert('Lỗi lưu địa chỉ'); }
        });
    });

    // ========== XỬ LÝ VOUCHER ==========
    $('#apply-voucher').click(function() {
        let code = $('#voucher-select').val();
        if (!code) {
            $('#voucher-message').text('Vui lòng chọn mã giảm giá').css('color', 'red');
            return;
        }
        $.ajax({
            url: '/LoopFit/public/checkout/apply-voucher',
            type: 'POST',
            data: { code: code, total_amount: originalTotal },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    currentDiscount = parseFloat(res.discount);
                    updateTotalDisplay();
                    $('#voucher-message').text('Áp dụng thành công! Đã giảm ' + formatMoney(currentDiscount)).css('color', 'green');
                } else {
                    $('#voucher-message').text(res.message).css('color', 'red');
                    currentDiscount = 0;
                    updateTotalDisplay();
                }
            },
            error: function() {
                $('#voucher-message').text('Lỗi kết nối, vui lòng thử lại').css('color', 'red');
            }
        });
    });

    $('#voucher-select').change(function() {
        $('#apply-voucher').click();
    });

    // ========== ĐẶT HÀNG ==========
    $('#checkout-form').submit(function(e) {
        e.preventDefault();
        if ($('input[name="address_id"]:checked').length === 0) {
            alert('Vui lòng chọn địa chỉ nhận hàng');
            return;
        }
        let formData = $(this).serialize();
        formData += '&discount=' + currentDiscount;
        $.ajax({
            url: '/LoopFit/public/checkout/process',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('Đặt hàng thành công! Mã đơn: ' + res.order_id);
                    window.location.href = '/LoopFit/public/orders';
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra, vui lòng thử lại sau');
            }
        });
    });

    // Khởi tạo: load địa chỉ
    loadAddresses();
});