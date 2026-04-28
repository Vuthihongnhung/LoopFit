// assets/user/js/profile.js
$(document).ready(function() {
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

    // Xác định BASE_URL
    let BASE_URL = '';
    const baseMeta = $('meta[name="base-url"]').attr('content');
    if (baseMeta) {
        BASE_URL = baseMeta;
    } else {
        const path = window.location.pathname;
        if (path.includes('/LoopFit/public')) {
            BASE_URL = '/LoopFit/public';
        } else if (path.includes('/public')) {
            BASE_URL = '/public';
        } else {
            BASE_URL = '';
        }
    }

    // Kiểm tra trạng thái đăng nhập (từ layout)
    const isLoggedIn = window.isLoggedIn === true;

    // Biến lưu danh sách địa chỉ toàn cục
    let addresses = [];

    // ========== CẬP NHẬT PROFILE ==========
    $('#update-profile-form').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            name: $('input[name="name"]').val(),
            phone: $('input[name="phone"]').val(),
            address: $('input[name="address"]').val()
        };
        $.ajax({
            url: BASE_URL + '/api/user/update-profile',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Toast.success(res.message, 'Cập nhật thành công');
                    $('.user-avatar h3').text(formData.name);
                    $('.user-avatar p').text(formData.phone);
                } else {
                    Toast.error(res.message, 'Lỗi cập nhật');
                }
            },
            error: function() { Toast.error('Lỗi hệ thống, vui lòng thử lại', 'Lỗi'); }
        });
    });

    // ========== ĐỔI MẬT KHẨU ==========
    $('#change-password-form').on('submit', function(e) {
        e.preventDefault();
        const current = $('input[name="current_password"]').val();
        const newPwd = $('input[name="new_password"]').val();
        const confirm = $('input[name="confirm_password"]').val();
        if (newPwd.length < 6) {
            Toast.warning('Mật khẩu mới phải có ít nhất 6 ký tự', 'Lỗi');
            return;
        }
        if (newPwd !== confirm) {
            Toast.warning('Xác nhận mật khẩu không khớp', 'Lỗi');
            return;
        }
        $.ajax({
            url: BASE_URL + '/api/user/change-password',
            type: 'POST',
            data: { current_password: current, new_password: newPwd, confirm_password: confirm },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Toast.success(res.message, 'Thành công');
                    $('#change-password-form')[0].reset();
                } else {
                    Toast.error(res.message, 'Lỗi');
                }
            },
            error: function() { Toast.error('Lỗi hệ thống, vui lòng thử lại', 'Lỗi'); }
        });
    });

    // ========== TAB SWITCHING ==========
    $('.menu-item').on('click', function() {
        const tabId = $(this).data('tab');
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
        if (!isLoggedIn) {
            $('#address-list').html('<p>Vui lòng <a href="' + BASE_URL + '/login">đăng nhập</a> để xem và quản lý địa chỉ.</p>');
            return;
        }
        $('#address-list').html('<div>Đang tải...</div>');
        $.ajax({
            url: BASE_URL + '/api/addresses',
            type: 'GET',
            dataType: 'json',
            timeout: 10000,
            success: function(res) {
                if (res.success) {
                    addresses = res.data || res.addresses || [];
                    renderAddressList(addresses);
                } else {
                    $('#address-list').html('<p class="error">' + (res.message || 'Lỗi tải địa chỉ') + '</p>');
                    Toast.error(res.message || 'Lỗi tải địa chỉ', 'Lỗi');
                }
            },
            error: function() {
                $('#address-list').html('<p class="error">Lỗi kết nối server. Vui lòng thử lại.</p>');
                Toast.error('Lỗi kết nối server', 'Lỗi');
            }
        });
    }

    function renderAddressList(addresses) {
        if (!addresses.length) {
            $('#address-list').html('<p>Chưa có địa chỉ nào. Nhấn "Thêm địa chỉ mới" để tạo.</p>');
            return;
        }
        let html = '';
        addresses.forEach(addr => {
            html += `
                <div class="address-item ${addr.is_default ? 'default' : ''}" data-id="${addr.id}">
                    <div><strong>${escapeHtml(addr.full_name)}</strong> - ${escapeHtml(addr.phone)}</div>
                    <div>${escapeHtml(addr.address_line)}</div>
                    ${addr.is_default ? '<span class="default-badge">(Mặc định)</span>' : ''}
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

    // Modal handlers
    const $modal = $('#address-modal');
    const $modalTitle = $('#modal-title');
    const $editId = $('#edit-address-id');
    const $addrFullname = $('#addr-fullname');
    const $addrPhone = $('#addr-phone');
    const $addrLine = $('#addr-line');
    const $addrDefault = $('#addr-default');

    function openModal(addressId = null) {
        $modalTitle.text(addressId ? 'Sửa địa chỉ' : 'Thêm địa chỉ mới');
        $editId.val(addressId || '');
        if (addressId) {
            const addr = addresses.find(a => a.id == addressId);
            if (addr) {
                $addrFullname.val(addr.full_name);
                $addrPhone.val(addr.phone);
                $addrLine.val(addr.address_line);
                $addrDefault.prop('checked', addr.is_default == 1);
            } else {
                Toast.error('Không tìm thấy địa chỉ', 'Lỗi');
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

    // Xóa địa chỉ
    $(document).on('click', '.delete-address', function() {
        const id = $(this).data('id');
        if (confirm('Bạn có chắc muốn xóa địa chỉ này?')) {
            $.ajax({
                url: BASE_URL + '/api/address/delete',
                type: 'POST',
                data: JSON.stringify({ id: id }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Toast.success('Xóa địa chỉ thành công', 'Thành công');
                        loadAddresses();
                    } else {
                        Toast.error(res.message || 'Xóa thất bại', 'Lỗi');
                    }
                },
                error: function() { Toast.error('Lỗi xóa địa chỉ', 'Lỗi'); }
            });
        }
    });

    // Đặt làm mặc định
    $(document).on('click', '.set-default-address', function() {
        const id = $(this).data('id');
        $.ajax({
            url: BASE_URL + '/api/address/set-default',
            type: 'POST',
            data: JSON.stringify({ id: id }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Toast.success('Đặt địa chỉ mặc định thành công', 'Thành công');
                    loadAddresses();
                } else {
                    Toast.error(res.message || 'Thất bại', 'Lỗi');
                }
            },
            error: function() { Toast.error('Lỗi đặt mặc định', 'Lỗi'); }
        });
    });

    // Lưu modal (thêm/sửa)
    $('#modal-save').on('click', function() {
        const id = $editId.val();
        const data = {
            full_name: $addrFullname.val().trim(),
            phone: $addrPhone.val().trim(),
            address_line: $addrLine.val().trim(),
            is_default: $addrDefault.is(':checked') ? 1 : 0
        };
        if (!data.full_name || !data.phone || !data.address_line) {
            Toast.warning('Vui lòng nhập đầy đủ thông tin', 'Thiếu thông tin');
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
                    Toast.success(res.message || (id ? 'Cập nhật thành công' : 'Thêm địa chỉ thành công'), 'Thành công');
                    closeModal();
                    loadAddresses();
                } else {
                    Toast.error(res.message || 'Lưu thất bại', 'Lỗi');
                }
            },
            error: function() { Toast.error('Lỗi lưu địa chỉ', 'Lỗi'); }
        });
    });

    $('.modal-close, #modal-close-btn').on('click', closeModal);
    $(window).on('click', function(e) { if ($(e.target).is($modal)) closeModal(); });

    // Logout
    $('#confirm-logout').on('click', function() {
        if (confirm('Bạn chắc chắn muốn đăng xuất?')) {
            Toast.info('Đang đăng xuất...', 'Thông báo');
            setTimeout(function() {
                window.location.href = BASE_URL + '/logout';
            }, 500);
        }
    });

    // Nếu tab address đang active lúc load trang
    if ($('.menu-item[data-tab="address"]').hasClass('active')) {
        loadAddresses();
    }
});