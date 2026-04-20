$(document).ready(function() {
    // Gallery
    let currentIndex = 0;
    let $mainImg = $('#mainImg');
    let $thumbs = $('.thumbnail-list img');
    let imageUrls = $thumbs.map(function() { return $(this).attr('src'); }).get();

    if (imageUrls.length === 0) return;

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

    // Quantity buttons
    window.decrease = function() {
        let qty = $('#qty');
        let val = parseInt(qty.val());
        if (val > 1) qty.val(val - 1);
    };
    window.increase = function() {
        let qty = $('#qty');
        qty.val(parseInt(qty.val()) + 1);
    };

    // Color selection
    $(document).on('click', '.color-btn', function() {
        $('.color-btn').removeClass('active');
        $(this).addClass('active');
    });

    // Size selection
    $(document).on('click', '.size-btn', function() {
        $('.size-btn').removeClass('active');
        $(this).addClass('active');
    });

    // Add to cart function (global)
    window.addToCart = function(productId) {
        let size = $('.size-btn.active').data('size') || '';
        let color = $('.color-btn.active').data('color') || '';
        let rentDate = $('#rentDate').val();
        let returnDate = $('#returnDate').val();
        let quantity = $('#qty').val();

        if (!size) { alert('Chọn size'); return; }
        if (!color) { alert('Chọn màu'); return; }
        if (!rentDate || !returnDate) { alert('Chọn ngày'); return; }

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
                    alert('Đã thêm vào giỏ');
                    $('#cart-count').text(res.cartCount);
                } else {
                    alert(res.message);
                }
            },
            error: function() { alert('Lỗi kết nối'); }
        });
    };

    // Nếu có nút gọi addToCartQuick (từ product-card), cũng gắn vào
    window.addToCartQuick = window.addToCart;
});