$(document).ready(function() {
    // ========== 1. GALLERY ==========
    let currentIndex = 0;
    let $mainImg = $('#mainImg');
    let $thumbs = $('.thumbnail-list img');
    let imageUrls = $thumbs.map(function() { return $(this).attr('src'); }).get();

    if (imageUrls.length > 0) {
        function updateMainImage(index) {
            if (index >= 0 && index < imageUrls.length) {
                var newImg = new Image();
                newImg.onload = function() {
                    $mainImg.fadeOut(150, function() {
                        $mainImg.attr('src', imageUrls[index]).fadeIn(150);
                    });
                };
                newImg.src = imageUrls[index];
                currentIndex = index;
                $thumbs.removeClass('active-thumb');
                $thumbs.eq(index).addClass('active-thumb');
            }
        }

        $thumbs.on('click', function() {
            let idx = $thumbs.index($(this));
            updateMainImage(idx);
        });

        $('.gallery-nav.prev').on('click', function() {
            let newIndex = (currentIndex - 1 + imageUrls.length) % imageUrls.length;
            updateMainImage(newIndex);
        });
        $('.gallery-nav.next').on('click', function() {
            let newIndex = (currentIndex + 1) % imageUrls.length;
            updateMainImage(newIndex);
        });

        updateMainImage(0);
    }

    // ========== 2. QUANTITY BUTTONS ==========
    window.decrease = function() {
        let qty = $('#qty');
        let val = parseInt(qty.val());
        if (val > 1) qty.val(val - 1);
    };
    window.increase = function() {
        let qty = $('#qty');
        qty.val(parseInt(qty.val()) + 1);
    };

    // ========== 3. COLOR & SIZE SELECTION ==========
    $(document).on('click', '.color-btn', function() {
        $('.color-btn').removeClass('active');
        $(this).addClass('active');
    });
    $(document).on('click', '.size-btn', function() {
        $('.size-btn').removeClass('active');
        $(this).addClass('active');
    });

    // ========== 4. ADD TO CART (thay alert bằng Toast) ==========
    window.addToCart = function(productId) {
        let size = $('.size-btn.active').data('size') || '';
        let color = $('.color-btn.active').data('color') || '';
        let rentDate = $('#rentDate').val();
        let returnDate = $('#returnDate').val();
        let quantity = $('#qty').val();

        // Kiểm tra dữ liệu nhập
        if (!size) {
            Toast.warning('Vui lòng chọn size', 'Thiếu thông tin');
            return;
        }
        if (!color) {
            Toast.warning('Vui lòng chọn màu sắc', 'Thiếu thông tin');
            return;
        }
        if (!rentDate || !returnDate) {
            Toast.warning('Vui lòng chọn ngày thuê và ngày trả', 'Thiếu thông tin');
            return;
        }

        // Gửi AJAX
        $.ajax({
            url: '/LoopFit/public/api/cart/add',
            type: 'POST',
            data: {
                product_id: productId,
                size: size,
                color: color,
                rent_date: rentDate,
                return_date: returnDate,
                quantity: quantity
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Toast.success('Đã thêm sản phẩm vào giỏ hàng!', 'Thành công');
                    // Cập nhật số lượng giỏ hàng nếu có hàm updateCartCount
                    if (typeof updateCartCount === 'function') {
                        updateCartCount(res.cartCount);
                    } else if ($('#cart-count').length) {
                        $('#cart-count').text(res.cartCount);
                    }
                } else {
                    Toast.error(res.message || 'Không thể thêm sản phẩm. Vui lòng thử lại.', 'Lỗi');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                Toast.error('Lỗi kết nối đến máy chủ. Vui lòng kiểm tra mạng.', 'Lỗi hệ thống');
            }
        });
    };

    // Hàm tương tự cho nút "Mua nhanh" (nếu có)
    window.addToCartQuick = window.addToCart;

    // ========== 5. RATING & REVIEW (thay alert bằng Toast) ==========
    // Xử lý click chọn sao
    $('.rating-input i').on('click', function() {
        let rating = $(this).data('rating');
        $('#ratingValue').val(rating);
        $('.rating-input i').removeClass('active');
        $(this).addClass('active');
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                $('.rating-input i').eq(i-1).removeClass('far').addClass('fas');
            } else {
                $('.rating-input i').eq(i-1).removeClass('fas').addClass('far');
            }
        }
    });

    // Gửi review qua AJAX
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        
        // Kiểm tra rating đã chọn chưa
        let ratingVal = $('#ratingValue').val();
        if (!ratingVal || ratingVal == 0) {
            Toast.warning('Vui lòng chọn số sao đánh giá', 'Thiếu đánh giá');
            return;
        }

        $.ajax({
            url: '/api/reviews/add',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Toast.success(res.message || 'Cảm ơn bạn đã đánh giá sản phẩm!', 'Đánh giá thành công');
                    $('#reviewForm')[0].reset();
                    $('.rating-input i').removeClass('fas active').addClass('far');
                    $('#ratingValue').val('');
                    // Tự động tải lại trang để hiển thị review mới (hoặc có thể thêm DOM động)
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    Toast.error(res.message || 'Không thể gửi đánh giá. Vui lòng thử lại.', 'Lỗi');
                }
            },
            error: function() {
                Toast.error('Đã xảy ra lỗi, vui lòng thử lại sau.', 'Lỗi hệ thống');
            }
        });
    });
});