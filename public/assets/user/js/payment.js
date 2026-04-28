// assets/user/js/payment.js
$(document).ready(function() {
    const $btn = $('#confirmPaymentBtn');
    const $messageDiv = $('#paymentMessage');
    const orderId = window.paymentData?.orderId;
    let countdownInterval;
    let timeLeft = 0;

    // Lấy BASE_URL từ meta tag
    let BASE_URL = $('meta[name="base-url"]').attr('content') || '';
    if (!BASE_URL) {
        const path = window.location.pathname;
        if (path.includes('/LoopFit/public')) BASE_URL = '/LoopFit/public';
        else if (path.includes('/public')) BASE_URL = '/public';
        else BASE_URL = '';
    }

    // Tính thời gian còn lại từ expiryTimestamp
    if (orderId && window.paymentData?.expiryTimestamp) {
        const now = Date.now();
        const expiry = window.paymentData.expiryTimestamp;
        timeLeft = Math.max(0, Math.floor((expiry - now) / 1000));
    } else {
        timeLeft = 15 * 60; // fallback
    }

    if (!orderId) {
        console.error('Không tìm thấy mã đơn hàng');
        $messageDiv.html('<span style="color:red;">Lỗi: thiếu thông tin đơn hàng</span>');
        return;
    }

    function updateCountdownDisplay() {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        $('#countdown-timer').text(`${minutes}:${seconds < 10 ? '0' + seconds : seconds}`);
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            $('#countdown-timer').text('0:00');
            $btn.prop('disabled', true).text('Đã hết hạn');
            $messageDiv.html('<span style="color:red;">Đơn hàng đã hết thời gian thanh toán. Vui lòng đặt lại.</span>');
        }
    }

    function startCountdown() {
        if (timeLeft <= 0) {
            updateCountdownDisplay();
            return;
        }
        updateCountdownDisplay();
        countdownInterval = setInterval(function() {
            if (timeLeft > 0) {
                timeLeft--;
                updateCountdownDisplay();
            } else {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    $btn.on('click', function() {
        if ($btn.prop('disabled')) return;

        $btn.prop('disabled', true);
        $btn.html('<span class="loading"></span> Đang xử lý...');
        $messageDiv.removeClass('success-msg error-msg').html('');

        $.ajax({
            url: BASE_URL + '/user/payment/confirm',
            method: 'POST',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    clearInterval(countdownInterval);
                    $messageDiv.addClass('success-msg')
                        .html('✅ Xác nhận thành công! Cảm ơn bạn. Chuyển hướng đến đơn hàng...');
                    setTimeout(function() {
                        window.location.href = BASE_URL + '/orders';
                    }, 2000);
                } else {
                    $btn.prop('disabled', false);
                    $btn.html('Tôi đã chuyển khoản');
                    $messageDiv.addClass('error-msg')
                        .html('❌ ' + (res.message || 'Có lỗi xảy ra, vui lòng thử lại.'));
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                $btn.html('Tôi đã chuyển khoản');
                let errorMsg = 'Lỗi kết nối hoặc server. Vui lòng thử lại.';
                if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                $messageDiv.addClass('error-msg').html('❌ ' + errorMsg);
            }
        });
    });

    startCountdown();
});