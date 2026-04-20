$(document).ready(function() {
    // Helper hiển thị toast (nếu cần)
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

    // Xử lý đăng ký (giữ nguyên nếu cần)
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
});