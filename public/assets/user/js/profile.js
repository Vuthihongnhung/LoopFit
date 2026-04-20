$(document).ready(function() {
    // Helper message
    function showMessage(element, message, isError) {
        element.html(message).removeClass('success error').addClass(isError ? 'error' : 'success');
        setTimeout(function() { element.html('').removeClass('success error'); }, 3000);
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

    // Biến lưu danh sách địa chỉ toàn cục
    let addresses = [];

    // ========== CẬP NHẬT PROFILE ==========
    $('#update-profile-form').on('submit', function(e) {
        e.preventDefault();
        var formData = {
            name: $('input[name="name"]').val(),
            phone: $('input[name="phone"]').val(),
            address: $('input[name="address"]').val()
        };
        $.ajax({
            url: '/LoopFit/public/api/user/update-profile',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                showMessage($('#profile-message'), res.message, !res.success);
                if (res.success) {
                    $('.user-avatar h3').text(formData.name);
                    $('.user-avatar p').text(formData.phone);
                }
            },
            error: function() { showMessage($('#profile-message'), 'Lỗi hệ thống', true); }
        });
    });

    // ========== ĐỔI MẬT KHẨU ==========
    $('#change-password-form').on('submit', function(e) {
        e.preventDefault();
        var current = $('input[name="current_password"]').val();
        var newPwd = $('input[name="new_password"]').val();
        var confirm = $('input[name="confirm_password"]').val();
        if (newPwd.length < 6) {
            showMessage($('#password-message'), 'Mật khẩu mới phải >=6 ký tự', true);
            return;
        }
        if (newPwd !== confirm) {
            showMessage($('#password-message'), 'Xác nhận mật khẩu không khớp', true);
            return;
        }
        $.ajax({
            url: '/LoopFit/public/api/user/change-password',
            type: 'POST',
            data: { current_password: current, new_password: newPwd, confirm_password: confirm },
            dataType: 'json',
            success: function(res) {
                showMessage($('#password-message'), res.message, !res.success);
                if (res.success) $('#change-password-form')[0].reset();
            },
            error: function() { showMessage($('#password-message'), 'Lỗi hệ thống', true); }
        });
    });

    // ========== TAB SWITCHING ==========
    $('.menu-item').on('click', function() {
        var tabId = $(this).data('tab');
        $('.menu-item').removeClass('active');
        $(this).addClass('active');
        $('.tab-pane').removeClass('active');
        $('#tab-' + tabId).addClass('active');
        if (tabId === 'address') {
            loadAddresses();
        }
    });

    // ========== QUẢN LÝ ĐỊA CHỈ ==========
    function loadAddresses() {
        $('#address-list').html('<div class="loading">Đang tải...</div>');
        $.ajax({
            url: '/LoopFit/public/api/addresses',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    addresses = res.addresses;
                    renderAddressList(addresses);
                } else {
                    $('#address-list').html('<p>Lỗi tải địa chỉ</p>');
                }
            },
            error: function() { $('#address-list').html('<p>Lỗi kết nối</p>'); }
        });
    }

    function renderAddressList(addresses) {
        if (!addresses.length) {
            $('#address-list').html('<p>Chưa có địa chỉ nào. Nhấn "Thêm địa chỉ mới" để tạo.</p>');
            return;
        }
        var html = '';
        addresses.forEach(addr => {
            html += `
                <div class="address-item ${addr.is_default ? 'default' : ''}" data-id="${addr.id}">
                    <div><strong>${escapeHtml(addr.full_name)}</strong> - ${escapeHtml(addr.phone)}</div>
                    <div>${escapeHtml(addr.address_line)}</div>
                    ${addr.is_default ? '<span style="color:#27ae60; font-size:12px;">(Mặc định)</span>' : ''}
                    <div class="address-actions">
                        <button class="edit-address" data-id="${addr.id}">Sửa</button>
                        <button class="delete-address" data-id="${addr.id}">Xóa</button>
                        ${!addr.is_default ? `<button class="set-default-address" data-id="${addr.id}">Đặt mặc định</button>` : ''}
                    </div>
                </div>
            `;
        });
        $('#address-list').html(html);
    }

    // Modal handlers (dùng modal có sẵn trong profile.php)
    var $modal = $('#address-modal');
    var $modalTitle = $('#modal-title');
    var $editId = $('#edit-address-id');
    var $addrFullname = $('#addr-fullname');
    var $addrPhone = $('#addr-phone');
    var $addrLine = $('#addr-line');
    var $addrDefault = $('#addr-default');

    function openModal(addressId = null) {
        $modalTitle.text(addressId ? 'Sửa địa chỉ' : 'Thêm địa chỉ mới');
        $editId.val(addressId || '');
        if (addressId) {
            // Lấy dữ liệu từ mảng addresses đã có (không cần gọi API riêng)
            var addr = addresses.find(a => a.id == addressId);
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

    $('#add-address-btn').on('click', function() { openModal(); });
    $(document).on('click', '.edit-address', function() { openModal($(this).data('id')); });
    $(document).on('click', '.delete-address', function() {
        var id = $(this).data('id');
        if (confirm('Bạn có chắc muốn xóa địa chỉ này?')) {
            $.ajax({
                url: '/LoopFit/public/api/address/delete',
                type: 'POST',
                data: { address_id: id },
                dataType: 'json',
                success: function(res) {
                    if (res.success) loadAddresses();
                    else alert(res.message);
                },
                error: function() { alert('Lỗi xóa địa chỉ'); }
            });
        }
    });
    $(document).on('click', '.set-default-address', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/LoopFit/public/api/address/set-default',
            type: 'POST',
            data: { address_id: id },
            dataType: 'json',
            success: function(res) {
                if (res.success) loadAddresses();
                else alert(res.message);
            },
            error: function() { alert('Lỗi đặt mặc định'); }
        });
    });
    $('#modal-save').on('click', function() {
        var id = $editId.val();
        var data = {
            full_name: $addrFullname.val().trim(),
            phone: $addrPhone.val().trim(),
            address_line: $addrLine.val().trim(),
            is_default: $addrDefault.is(':checked') ? 1 : 0
        };
        if (!data.full_name || !data.phone || !data.address_line) {
            alert('Vui lòng nhập đầy đủ thông tin');
            return;
        }
        var url = id ? '/LoopFit/public/api/address/update' : '/LoopFit/public/api/address/add';
        if (id) data.address_id = id;
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    closeModal();
                    loadAddresses();
                } else {
                    alert(res.message);
                }
            },
            error: function() { alert('Lỗi lưu địa chỉ'); }
        });
    });
    $('.modal-close, #modal-close-btn').on('click', closeModal);
    $(window).on('click', function(e) { if ($(e.target).is($modal)) closeModal(); });

    // Logout
    $('#confirm-logout').on('click', function() {
        if (confirm('Bạn chắc chắn muốn đăng xuất?')) {
            window.location.href = '/LoopFit/public/logout';
        }
    });

    // Nếu tab address đang active lúc load trang
    if ($('.menu-item[data-tab="address"]').hasClass('active')) {
        loadAddresses();
    }
});