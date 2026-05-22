$(document).ready(function() {
    function showToast(type, message, title) {
        let toast = $('#toast-notification');
        if (toast.length === 0) {
            $('body').append(`
                <div id="toast-notification" class="toast-notification">
                    <div class="toast-icon"></div>
                    <div class="toast-content">
                        <div class="toast-title"></div>
                        <div class="toast-message"></div>
                    </div>
                    <div class="toast-close">&times;</div>
                </div>
            `);
            toast = $('#toast-notification');
            toast.find('.toast-close').on('click', function() {
                toast.removeClass('show');
            });
        }
        toast.removeClass('toast-success toast-error');
        if (type === 'success') {
            toast.addClass('toast-success');
            toast.find('.toast-icon').html('✓');
            toast.find('.toast-title').text(title || 'Thành công');
        } else {
            toast.addClass('toast-error');
            toast.find('.toast-icon').html('✗');
            toast.find('.toast-title').text(title || 'Thất bại');
        }
        toast.find('.toast-message').text(message);
        toast.addClass('show');
        setTimeout(function() { toast.removeClass('show'); }, 3000);
    }

    // Xử lý đăng nhập
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        $('.error-message').hide();

        let email = $('#email').val().trim();
        let password = $('#password').val();
        let emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;

        if (!email) {
            $('#error-email').text('Vui lòng nhập email.').show();
            return;
        } else if (!emailRegex.test(email)) {
            $('#error-email').text('Email không đúng định dạng.').show();
            return;
        }
        if (!password) {
            $('#error-password').text('Vui lòng nhập mật khẩu.').show();
            return;
        }

        $.ajax({
            url: '/LoopFit/public/login',
            type: 'POST',
            data: { email: email, password: password },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('success', res.message, 'Đăng nhập thành công');
                    setTimeout(function() {
                        window.location.href = '/LoopFit/public/';
                    }, 1000);
                } else {
                    showToast('error', res.message, 'Lỗi đăng nhập');
                }
            },
            error: function(xhr) {
                let msg = 'Có lỗi xảy ra';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showToast('error', msg, 'Lỗi hệ thống');
            }
        });
    });

    // Xử lý đăng ký 
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        $('.error-message').hide();

        let fullname = $('#fullname').val().trim();
        let email = $('#email').val().trim();
        let password = $('#password').val();
        let confirmPwd = $('#confirm_password').val();
        let emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;

        let isValid = true;
        if (!fullname) {
            $('#error-fullname').text('Vui lòng nhập họ tên.').show();
            isValid = false;
        }
        if (!email) {
            $('#error-email').text('Vui lòng nhập email.').show();
            isValid = false;
        } else if (!emailRegex.test(email)) {
            $('#error-email').text('Email không đúng định dạng.').show();
            isValid = false;
        }
        if (!password) {
            $('#error-password').text('Vui lòng nhập mật khẩu.').show();
            isValid = false;
        } else if (password.length <= 6) {
            $('#error-password').text('Mật khẩu phải có ít nhất 6 ký tự.').show();
            isValid = false;
        }
        if (!confirmPwd) {
            $('#error-confirm').text('Vui lòng xác nhận mật khẩu.').show();
            isValid = false;
        } else if (password !== confirmPwd) {
            $('#error-confirm').text('Mật khẩu xác nhận không khớp.').show();
            isValid = false;
        }

        if (!isValid) return;

        $.ajax({
            url: '/LoopFit/public/register',
            type: 'POST',
            data: {
                name: fullname,
                email: email,
                password: password,
                phone: $('#phone').val(),
                address: $('#address').val()
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('success', res.message, 'Chúc mừng!');
                    setTimeout(function() {
                        window.location.href = '/LoopFit/public/login';
                    }, 2000);
                } else {
                    showToast('error', res.message, 'Đăng ký thất bại');
                }
            },
            error: function(xhr) {
                let msg = 'Có lỗi xảy ra';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showToast('error', msg, 'Lỗi hệ thống');
            }
        });
    });

    // Xử lý đăng ký 
    // Modal xác nhận đăng xuất
    if ($('#logoutModal').length === 0) {
        $('body').append(`
            <div id="logoutModal" class="logout-modal" style="display: none;">
                <div class="logout-modal-content">
                    <div class="logout-modal-header">
                        <h4>Xác nhận đăng xuất</h4>
                        <span class="logout-close">&times;</span>
                    </div>
                    <div class="logout-modal-body">
                        Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?
                    </div>
                    <div class="logout-modal-footer">
                        <button id="logoutCancelBtn" class="btn btn-secondary">Hủy</button>
                        <button id="logoutConfirmBtn" class="btn btn-danger">Đăng xuất</button>
                    </div>
                </div>
            </div>
        `);
        if ($('#logoutModalStyle').length === 0) {
            $('head').append(`
                <style id="logoutModalStyle">
                    .logout-modal {
                        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                        background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;
                        z-index: 9999;
                    }
                    .logout-modal-content {
                        background: white; border-radius: 12px; width: 400px; max-width: 90%;
                        box-shadow: 0 5px 20px rgba(0,0,0,0.3); animation: modalFadeIn 0.3s;
                    }
                    @keyframes modalFadeIn {
                        from { opacity: 0; transform: translateY(-50px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .logout-modal-header {
                        padding: 15px 20px; border-bottom: 1px solid #eee;
                        display: flex; justify-content: space-between; align-items: center;
                    }
                    .logout-modal-header h4 { margin: 0; font-size: 18px; }
                    .logout-close { font-size: 24px; cursor: pointer; color: #999; }
                    .logout-close:hover { color: #333; }
                    .logout-modal-body { padding: 20px; font-size: 16px; color: #555; }
                    .logout-modal-footer {
                        padding: 15px 20px; border-top: 1px solid #eee; text-align: right;
                    }
                    .logout-modal-footer button {
                        padding: 8px 20px; margin-left: 10px; border: none; border-radius: 6px;
                        cursor: pointer; font-size: 14px;
                    }
                    .btn-secondary { background: #6c757d; color: white; }
                    .btn-danger { background: #dc3545; color: white; }
                </style>
            `);
        }
    }

    $(document).on('click', '.logout-btn, #logout-btn, a[href*="logout"]', function(e) {
        e.preventDefault();
        $('#logoutModal').fadeIn();
    });

    $(document).on('click', '#logoutCancelBtn, .logout-close', function() {
        $('#logoutModal').fadeOut();
    });

    $(document).on('click', '#logoutModal', function(e) {
        if ($(e.target).is('#logoutModal')) {
            $('#logoutModal').fadeOut();
        }
    });

    $(document).on('click', '#logoutConfirmBtn', function() {
        $('#logoutModal').fadeOut();
        $.ajax({
            url: '/LoopFit/public/logout',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('success', res.message, 'Đăng xuất thành công');
                    setTimeout(function() {
                        window.location.href = '/LoopFit/public/login';
                    }, 1000);
                } else {
                    showToast('error', res.message || 'Đăng xuất thất bại', 'Lỗi');
                }
            },
            error: function() {
                window.location.href = '/LoopFit/public/logout';
            }
        });
    });
});