<?php 
$images = explode(',', $product['images'] ?? $product['image']);
$outOfStock = ($product['stock'] <= 0);
?>

<div class="product-detail">

    <!-- LEFT: IMAGE -->
    <div class="product-left">

        <div class="main-image">
            <img id="mainImg"
                 src="/LoopFit/public/uploads/products/<?= trim($images[0]) ?>">
        </div>

        <div class="thumbnail-list">
            <?php foreach ($images as $img): ?>
                <img src="/LoopFit/public/uploads/products/<?= trim($img) ?>"
                     onclick="changeImg(this)">
            <?php endforeach; ?>
        </div>

    </div>

    <!-- RIGHT: INFO -->
    <div class="product-right">

        <h1 class="product-title">
            <?= htmlspecialchars($product['name']) ?>
        </h1>

        <div class="sub-info">
            <span>Chưa có đánh giá</span> |
            <span>Đã thuê <?= (int)($product['rented_count'] ?? 0) ?></span>
        </div>

        <div class="price-box">
            <?= number_format($product['price'], 0, ',', '.') ?>đ / ngày
        </div>

        <div class="info">
            <p><b>Danh mục:</b> <?= htmlspecialchars($product['category'] ?? '') ?></p>
            <p><b>Style:</b> <?= htmlspecialchars($product['style'] ?? '') ?></p>
            <p><b>Giới tính:</b> <?= htmlspecialchars($product['gender'] ?? '') ?></p>
        </div>

        <div class="desc">
            <?= nl2br(htmlspecialchars($product['description'] ?? '')) ?>
        </div>

        <!-- SIZE -->
        <div class="size-section">
            <span style="font-weight: bold; display: block; margin-bottom: 8px;">Size</span>
            <div class="size-options">
                <?php 
                    // Lấy dữ liệu từ DB, nếu lỗi hoặc trống thì dùng mặc định S,M,L
                    $raw_sizes = !empty($product['sizes']) ? $product['sizes'] : 'S,M,L';
                    $size_array = array_filter(array_map('trim', explode(',', $raw_sizes)));
                    
                    foreach ($size_array as $s): 
                ?>
                    <button type="button" class="size-btn" data-size="<?= htmlspecialchars($s) ?>">
                        <?= htmlspecialchars($s) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- DATE RENT -->
        <div class="date-section">
            <div class="date-item">
                <label><b>Ngày thuê</b></label><br>
                <input type="date" id="rentDate">
            </div>

            <div class="date-item">
                <label><b>Ngày trả</b></label><br>
                <input type="date" id="returnDate">
            </div>
        </div>

        <!-- QUANTITY -->
        <div class="quantity">
            <span><b>Số lượng</b></span>
            <div class="qty-box">
                <button onclick="decrease()">-</button>
                <input type="text" id="qty" value="1">
                <button onclick="increase()">+</button>
            </div>

            <span class="stock">
                <?= $outOfStock ? 'Hết hàng' : 'Còn hàng' ?>
            </span>
        </div>

        <!-- BUTTON -->
        <div class="actions">
            <button class="btn-cart"
                onclick="addToCart(<?= (int)$product['id'] ?>)">
                🛒 Thêm vào giỏ
            </button>
        </div>
    </div>

</div>

