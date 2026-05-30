$(document).ready(function() {
    // ========== LẤY BASE_URL ==========
    let BASE_URL = '';
    const baseMeta = document.querySelector('meta[name="base-url"]');
    if (baseMeta) BASE_URL = baseMeta.getAttribute('content');
    else if (typeof window.BASE_URL !== 'undefined') BASE_URL = window.BASE_URL;
    else {
        const path = window.location.pathname;
        if (path.includes('/LoopFit/public')) BASE_URL = '/LoopFit/public';
        else if (path.includes('/public')) BASE_URL = '/public';
        else BASE_URL = '';
    }
    console.log('BASE_URL:', BASE_URL);

    // ========== LẤY PRODUCT ID TỪ CONTAINER ==========
    const container = document.querySelector('.product-detail-v2');
    if (!container) return;
    const productId = parseInt(container.dataset.productId);
    if (!productId) {
        console.error('Không tìm thấy productId');
        return;
    }
    console.log('productId:', productId);

    // ========== BIẾN TOÀN CỤC ==========
    let currentAvailable = 0;
    let currentIndex = 0;
    let images = [];
    const mainImg = document.getElementById('mainImg');
    // variant map từ template: { size: { color: available, ... }, ... }
    const variantMap = container && container.dataset && container.dataset.variants ? JSON.parse(container.dataset.variants) : {};
    const rentDateInput = document.getElementById('rentDate');
    const returnDateInput = document.getElementById('returnDate');
    const qtyInput = document.getElementById('qty');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const stockStatusText = document.getElementById('stockStatusText');
    const stockExtraInfo = document.getElementById('stockExtraInfo');
    
    // Lấy danh sách ảnh từ thumbnails
    const $thumbs = $('.thumbnail-list img');
    images = $thumbs.map(function() { return $(this).attr('src'); }).get();

    // ========== TOAST (nếu có) ==========
    function showToastMessage(message, type = 'success') {
        if (typeof Toast !== 'undefined') {
            if (type === 'success') Toast.success(message);
            else if (type === 'error') Toast.error(message);
            else if (type === 'warning') Toast.warning(message);
            else Toast.info(message);
        } else {
            alert(message);
        }
    }

    // ========== 1. GALLERY ==========
    if (images.length > 0) {
        function updateMainImage(index) {
            if (index >= 0 && index < images.length) {
                var newImg = new Image();
                newImg.onload = function() {
                    $(mainImg).fadeOut(150, function() {
                        $(mainImg).attr('src', images[index]).fadeIn(150);
                    });
                };
                newImg.src = images[index];
                currentIndex = index;
                $thumbs.removeClass('active-thumb');
                $thumbs.eq(index).addClass('active-thumb');
            }
        }
        $thumbs.on('click', function() { updateMainImage($thumbs.index(this)); });
        $('.gallery-nav.prev').on('click', function() {
            updateMainImage((currentIndex - 1 + images.length) % images.length);
        });
        $('.gallery-nav.next').on('click', function() {
            updateMainImage((currentIndex + 1) % images.length);
        });
        updateMainImage(0);
    }

    // ========== 2. HIỂN THỊ TRẠNG THÁI TỒN KHO ==========
    function updateStockDisplay(available, requestedQty) {
        if (available === null) {
            $(stockStatusText).html(`
                <span style="color: #f59e0b; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-regular fa-hourglass-half"></i> Vui lòng chọn ngày và phân loại
                </span>
            `);
            $(stockExtraInfo).text('');
            // Cho phép người dùng bấm nút để hiển thị thông báo nếu thông tin chưa đầy đủ
            if (addToCartBtn) $(addToCartBtn).prop('disabled', false);
            return;
        }
        if (available < 1) {
            $(stockStatusText).html(`
                <span style="color: #dc3545; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-xmark"></i> Hết hàng
                </span>
            `);
            $(stockExtraInfo).text('(Không có sản phẩm khả dụng trong khoảng thời gian này)');
            $(addToCartBtn).prop('disabled', true);
        } else if (available < requestedQty) {
            $(stockStatusText).html(`
                <span style="color: #f59e0b; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Không đủ số lượng
                </span>
            `);
            $(stockExtraInfo).text(`(Chỉ còn ${available} sản phẩm)`);
            $(addToCartBtn).prop('disabled', true);
        } else {
            $(stockStatusText).html(`
                <span style="color: #198754; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-check"></i> Còn hàng
                </span>
            `);
            $(stockExtraInfo).text(`(Còn ${available} sản phẩm có sẵn)`);
            $(addToCartBtn).prop('disabled', false);
        }
    }

    // ========== 3. KIỂM TRA TỒN KHO THEO THỜI GIAN (AJAX) ==========
    function checkStock() {
        const sizeBtn = $('.size-btn.active');
        const colorBtn = $('.color-btn.active');
        const size = sizeBtn.length ? sizeBtn.data('size') : null;
        const color = colorBtn.length ? colorBtn.data('color') : null;
        const rentDate = rentDateInput ? rentDateInput.value : '';
        const returnDate = returnDateInput ? returnDateInput.value : '';
        const quantity = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

        if (!size || !color || !rentDate || !returnDate) {
            updateStockDisplay(null, quantity);
            return;
        }
        if (new Date(rentDate) >= new Date(returnDate)) {
            $(stockStatusText).html(`<span style="color: #f59e0b;">⚠️ Ngày trả phải sau ngày thuê</span>`);
            $(stockExtraInfo).text('');
            $(addToCartBtn).prop('disabled', true);
            return;
        }

        fetch(`${BASE_URL}/check-stock?product_id=${productId}&rent_date=${rentDate}&return_date=${returnDate}&quantity=${quantity}&size=${encodeURIComponent(size)}&color=${encodeURIComponent(color)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentAvailable = data.available;
                    updateStockDisplay(currentAvailable, quantity);
                } else {
                    $(stockStatusText).html(`<span style="color:#dc3545;">❌ Lỗi kiểm tra tồn kho</span>`);
                    $(addToCartBtn).prop('disabled', true);
                }
            })
            .catch(() => {
                $(stockStatusText).html(`<span style="color:#dc3545;">⚠️ Lỗi kết nối</span>`);
                $(addToCartBtn).prop('disabled', true);
            });
    }

    // ========== 4. SỰ KIỆN CHỌN SIZE, MÀU, NGÀY, SỐ LƯỢNG ==========
    $(document).on('click', '.size-btn', function() {
        $('.size-btn').removeClass('active');
        $(this).addClass('active');
        // Khi chọn size, cập nhật trạng thái màu tương ứng
        const selectedSize = $(this).data('size');
        updateColorAvailability(selectedSize);
        checkStock();
    });
    $(document).on('click', '.color-btn', function() {
        // không cho chọn nếu đang disabled
        if ($(this).prop('disabled')) return;
        $('.color-btn').removeClass('active');
        $(this).addClass('active');
        checkStock();
    });
    if (rentDateInput) $(rentDateInput).on('change', checkStock);
    if (returnDateInput) $(returnDateInput).on('change', checkStock);
    if (decreaseBtn) {
        $(decreaseBtn).on('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) { qtyInput.value = val - 1; checkStock(); }
        });
    }
    if (increaseBtn) {
        $(increaseBtn).on('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val < 10) { qtyInput.value = val + 1; checkStock(); }
        });
    }

    // ========== 5. THÊM VÀO GIỎ HÀNG (AJAX) ==========
    function addToCartHandler() {
        if ($(addToCartBtn).prop('disabled')) return;
        $(addToCartBtn).prop('disabled', true);

        const sizeBtn = $('.size-btn.active');
        const colorBtn = $('.color-btn.active');
        if (!sizeBtn.length || !colorBtn.length) {
            showToastMessage('Vui lòng chọn kích thước và màu sắc', 'error');
            $(addToCartBtn).prop('disabled', false);
            return;
        }
        const size = sizeBtn.data('size');
        const color = colorBtn.data('color');
        const rentDate = rentDateInput ? rentDateInput.value : '';
        const returnDate = returnDateInput ? returnDateInput.value : '';
        const quantity = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

        if (!rentDate || !returnDate) {
            showToastMessage('Vui lòng chọn ngày thuê và ngày trả', 'error');
            $(addToCartBtn).prop('disabled', false);
            return;
        }
        if (new Date(rentDate) >= new Date(returnDate)) {
            showToastMessage('Ngày trả phải sau ngày thuê', 'error');
            $(addToCartBtn).prop('disabled', false);
            return;
        }
        if (currentAvailable < quantity) {
            showToastMessage('Số lượng yêu cầu vượt quá số lượng khả dụng', 'error');
            $(addToCartBtn).prop('disabled', false);
            return;
        }

        const requestData = {
            product_id: productId,
            size: size,
            color: color,
            rent_date: rentDate,
            return_date: returnDate,
            quantity: quantity
        };
        console.log('Sending add to cart request:', requestData);

        $.ajax({
            url: `${BASE_URL}/api/cart/add`,
            type: 'POST',
            data: requestData,
            dataType: 'json',
            success: function(res) {
                console.log('Add to cart response:', res);
                if (res.success) {
                    showToastMessage('Đã thêm vào giỏ hàng', 'success');
                    if (typeof updateCartCount === 'function') updateCartCount(res.cartCount);
                    else if ($('#cart-count').length) $('#cart-count').text(res.cartCount);
                } else {
                    showToastMessage(res.message || 'Thêm thất bại', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                console.error('Response text:', xhr.responseText);
                showToastMessage('Lỗi kết nối, vui lòng thử lại', 'error');
            },
            complete: function() {
                $(addToCartBtn).prop('disabled', false);
            }
        });
    }

    if (addToCartBtn) {
        $(addToCartBtn).off('click').on('click', addToCartHandler);
    }

    // Cập nhật khả năng chọn màu theo size
    function updateColorAvailability(size) {
        if (!size) {
            $('.color-btn').each(function() { $(this).prop('disabled', false).removeClass('disabled'); });
            return;
        }
        $('.color-btn').each(function() {
            const color = $(this).data('color');
            const avail = variantMap[size] && typeof variantMap[size][color] !== 'undefined' ? parseInt(variantMap[size][color]) : 0;
            if (avail > 0) {
                $(this).prop('disabled', false).removeClass('disabled');
            } else {
                // nếu đang active thì bỏ chọn
                if ($(this).hasClass('active')) $(this).removeClass('active');
                $(this).prop('disabled', true).addClass('disabled');
            }
        });
    }

    // Khởi tạo trạng thái màu khi load trang nếu cần
    (function initVariantUI(){
        const activeSize = $('.size-btn.active').data('size');
        if (activeSize) updateColorAvailability(activeSize);
    })();

    // ========== 6. ĐÁNH GIÁ (REVIEW) ==========
    $('.rating-input i').on('click', function() {
        let rating = $(this).data('rating');
        $('#ratingValue').val(rating);
        $('.rating-input i').removeClass('active');
        $(this).addClass('active');
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) $('.rating-input i').eq(i-1).removeClass('far').addClass('fas');
            else $('.rating-input i').eq(i-1).removeClass('fas').addClass('far');
        }
    });

    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        let ratingVal = $('#ratingValue').val();
        if (!ratingVal || ratingVal == 0) {
            showToastMessage('Vui lòng chọn số sao đánh giá', 'warning');
            return;
        }
        $.ajax({
            url: '/api/reviews/add',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToastMessage(res.message || 'Cảm ơn bạn đã đánh giá!', 'success');
                    $('#reviewForm')[0].reset();
                    $('.rating-input i').removeClass('fas active').addClass('far');
                    $('#ratingValue').val('');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToastMessage(res.message || 'Gửi đánh giá thất bại', 'error');
                }
            },
            error: function() {
                showToastMessage('Lỗi kết nối, vui lòng thử lại', 'error');
            }
        });
    });

});