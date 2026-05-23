<?php
$pageTitle = 'Đánh giá sản phẩm - LoopFit';
?>
<div class="review-container">
    <h1>Đánh giá sản phẩm</h1>
    
    <div class="product-info">
        <?php 
        // Xử lý ảnh sản phẩm 
        $imageRaw = $product_image ?? '';
        $firstImage = trim(explode(',', $imageRaw)[0] ?? '');
        if (empty($firstImage)) {
            $firstImage = 'default-product.png';
        }
        $imgUrl = BASE_URL . '/uploads/' . ltrim($firstImage, '/');
        ?>
        <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($product_name) ?>">
        <div><strong><?= htmlspecialchars($product_name) ?></strong></div>
    </div>

    <form id="reviewForm">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        
        <div class="rating-area">
            <div class="stars" id="starRating">
                <i class="fa-regular fa-star" data-value="1"></i>
                <i class="fa-regular fa-star" data-value="2"></i>
                <i class="fa-regular fa-star" data-value="3"></i>
                <i class="fa-regular fa-star" data-value="4"></i>
                <i class="fa-regular fa-star" data-value="5"></i>
            </div>
            <input type="hidden" name="rating" id="ratingValue" required>
        </div>

        <textarea name="comment" rows="5" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." required></textarea>
        
        <div id="formMessage" class="error-msg"></div>
        
        <button type="submit" id="submitBtn">Gửi đánh giá</button>
    </form>
</div>

<style>
    .review-container {
        max-width: 700px;
        margin: 40px auto;
        background: white;
        border-radius: 28px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .review-container h1 {
        font-family: 'Playfair Display', serif;
        color: #718BBC;
        text-align: center;
        margin-bottom: 25px;
        font-size: 28px;
    }
    .product-info {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #ecf0f1;
    }
    .product-info img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 16px;
        background: #F0F0ED;
    }
    .product-info strong {
        font-size: 18px;
        color: #2c3e66;
    }
    .rating-area {
        text-align: center;
        margin: 25px 0;
    }
    .stars {
        display: flex;
        justify-content: center;
        gap: 15px;
        font-size: 36px;
        cursor: pointer;
    }
    .stars i {
        color: #e2e8f0;
        transition: all 0.2s;
    }
    .stars i.selected, .stars i:hover {
        color: #f4c542;
    }
    textarea {
        width: 100%;
        padding: 14px;
        border-radius: 20px;
        border: 1.5px solid #e2e8f0;
        font-family: 'Montserrat', sans-serif;
        font-size: 15px;
        resize: vertical;
        transition: 0.2s;
    }
    textarea:focus {
        border-color: #718BBC;
        outline: none;
        box-shadow: 0 0 0 3px rgba(113,139,188,0.1);
    }
    button {
        background: #718BBC;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 40px;
        width: 100%;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 10px;
    }
    button:hover:not(:disabled) {
        background: #5a6f9e;
    }
    button:disabled {
        background: #b0c4de;
        cursor: not-allowed;
    }
    .error-msg {
        color: #e74c3c;
        margin-top: 12px;
        text-align: center;
        font-size: 14px;
    }
    @media (max-width: 640px) {
        .review-container {
            padding: 20px;
            margin: 20px;
        }
        .product-info strong {
            font-size: 15px;
        }
        .stars {
            font-size: 28px;
            gap: 10px;
        }
    }
</style>

<script>
    if (typeof BASE_URL === 'undefined') {
        var BASE_URL = window.location.origin + '/LoopFit/public';
    }

    // Xử lý chọn sao
    const stars = document.querySelectorAll('#starRating i');
    const ratingInput = document.getElementById('ratingValue');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            let value = this.dataset.value;
            ratingInput.value = value;
            stars.forEach(s => {
                if (s.dataset.value <= value) {
                    s.classList.remove('fa-regular');
                    s.classList.add('fa-solid', 'selected');
                } else {
                    s.classList.remove('fa-solid', 'selected');
                    s.classList.add('fa-regular');
                }
            });
        });
    });

    // Submit form
    const form = document.getElementById('reviewForm');
    const submitBtn = document.getElementById('submitBtn');
    const messageDiv = document.getElementById('formMessage');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        messageDiv.innerText = '';
        messageDiv.style.color = '#e74c3c';

        // Kiểm tra chọn sao
        if (!ratingInput.value) {
            messageDiv.innerText = 'Vui lòng chọn số sao đánh giá.';
            return;
        }

        const formData = new FormData(form);
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang gửi...';

        try {
            // ĐÚNG ENDPOINT: /review/submit (không có /api)
            const response = await fetch(BASE_URL + '/review/submit', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                if (typeof Toast !== 'undefined') {
                    Toast.success(result.message, 'Thành công');
                    setTimeout(() => {
                        window.location.href = BASE_URL + '/order/' + formData.get('order_id');
                    }, 1500);
                } else {
                    alert(result.message);
                    window.location.href = BASE_URL + '/order/' + formData.get('order_id');
                }
            } else {
                messageDiv.innerText = result.error || 'Có lỗi xảy ra khi gửi đánh giá.';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Gửi đánh giá';
            }
        } catch (err) {
            console.error('Fetch error:', err);
            messageDiv.innerText = 'Lỗi kết nối máy chủ, vui lòng thử lại.';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Gửi đánh giá';
        }
    });
</script>