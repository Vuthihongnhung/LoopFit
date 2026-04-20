<?php 
$imagesRaw = $product['images'] ?? $product['image'] ?? '';
$images = array_map('trim', explode(',', $imagesRaw));

function getProductImageUrl($imgPath) {
    // Nếu đã là URL đầy đủ (http/https) hoặc đường dẫn tuyệt đối đúng (bắt đầu bằng /LoopFit/public)
    if (preg_match('#^(http|https)://#', $imgPath)) return $imgPath;
    if (strpos($imgPath, '/LoopFit/public/') === 0) return $imgPath;
    
    // Nếu chỉ là tên file (không có thư mục) hoặc bắt đầu bằng 'uploads/'
    // Đường dẫn thực tế: /LoopFit/public/uploads/ten-anh.jpg
    $base = '/LoopFit/public/uploads/';
    // Loại bỏ 'uploads/' nếu có
    $clean = preg_replace('#^uploads/#', '', $imgPath);
    return $base . $clean;
}

$firstImage = getProductImageUrl($images[0]);
$outOfStock = ($product['stock'] <= 0);
$deposit = $product['deposit_price'] ?? ($product['price'] * 0.3);
?>

<div class="product-detail-v2">
    <div class="product-detail-v2__container">
        <!-- Cột trái: Ảnh -->
        <div class="product-detail-v2__gallery">
            <div class="gallery-container">
                <button class="gallery-nav prev" onclick="prevImage()">&#10094;</button>
                <div class="main-image">
                    <img id="mainImg" src="<?= $firstImage ?>" alt="Ảnh chính">
                </div>
                <button class="gallery-nav next" onclick="nextImage()">&#10095;</button>
            </div>
            <div class="thumbnail-list">
                <?php foreach ($images as $index => $img): ?>
                    <img src="<?= getProductImageUrl($img) ?>" data-index="<?= $index ?>" onclick="currentImage(<?= $index ?>)" alt="thumbnail">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cột phải: Thông tin chính -->
        <div class="product-detail-v2__info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-rating">
                <span>⭐ 0 đánh giá</span> | 
                <span>📦 Đã thuê <?= (int)($product['rented_count'] ?? 0) ?></span>
            </div>
            <div class="product-price">
                <span class="current-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                <span class="unit">/ ngày</span>
                <div class="deposit">Tiền đặt cọc: <?= number_format($deposit, 0, ',', '.') ?>đ</div>
            </div>
            <div class="product-attributes">
                <div class="attr-item">
                    <span class="attr-label">Giới tính:</span>
                    <span class="attr-value">
                        <?php 
                        $gender = $product['gender'] ?? 'unisex';
                        if ($gender == 'male') echo 'Nam';
                        elseif ($gender == 'female') echo 'Nữ';
                        else echo 'Unisex (Cả nam và nữ)';
                        ?>
                    </span>
                </div>
            </div>

            <!-- Chọn size -->
            <div class="size-section">
                <div class="section-title">Kích thước</div>
                <div class="size-options">
                    <?php 
                        $raw_sizes = !empty($product['sizes']) ? $product['sizes'] : 'S,M,L';
                        $size_array = array_filter(array_map('trim', explode(',', $raw_sizes)));
                        foreach ($size_array as $s): 
                    ?>
                        <button type="button" class="size-btn" data-size="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- MÀU SẮC -->
            <div class="color-section">
                <span style="font-weight: bold; display: block; margin-bottom: 8px;">Màu sắc</span>
                <div class="color-options">
                    <?php 
                    $colorsRaw = $product['colors'] ?? '';
                    $colorArray = array_filter(array_map('trim', explode(',', $colorsRaw)));
                    if (empty($colorArray)) $colorArray = ['Đen', 'Trắng', 'Xám'];
                    foreach ($colorArray as $color):
                    ?>
                        <button type="button" class="color-btn" data-color="<?= htmlspecialchars($color) ?>">
                            <?= htmlspecialchars($color) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Chọn ngày thuê/trả -->
            <div class="date-section">
                <div class="date-item">
                    <label>Ngày thuê</label>
                    <input type="date" id="rentDate">
                </div>
                <div class="date-item">
                    <label>Ngày trả</label>
                    <input type="date" id="returnDate">
                </div>
            </div>

            <!-- Số lượng + nút -->
            <div class="quantity-section">
                <div class="qty-control">
                    <button class="qty-btn" onclick="decrease()">-</button>
                    <input type="text" id="qty" value="1" readonly>
                    <button class="qty-btn" onclick="increase()">+</button>
                </div>
                <div class="stock-status <?= $outOfStock ? 'out' : 'in' ?>">
                    <?= $outOfStock ? '❌ Hết hàng' : '✅ Còn hàng' ?>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn-add-to-cart" onclick="addToCart(<?= (int)$product['id'] ?>)">
                    🛒 Thêm vào giỏ hàng
                </button>
            </div>
        </div>
    </div>

    <!-- Mô tả sản phẩm (đặt cuối cùng) -->
    <div class="product-description">
        <h3>Mô tả sản phẩm</h3>
        <div class="description-content">
            <?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.')) ?>
        </div>
    </div>

    <!-- Sản phẩm tương tự -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="related-products">
        <h3 class="related-title">✨ Có thể bạn cũng thích</h3>
        <div class="related-grid">
            <?php foreach ($relatedProducts as $rel): ?>
                <div class="related-card">
                    <a href="/LoopFit/public/product/<?= $rel['id'] ?>">
                        <?php 
                            $relImg = $rel['image'] ?? '';
                            $firstImg = trim(explode(',', $relImg)[0] ?? '');
                            if (empty($firstImg)) $firstImg = 'no-image.png';
                            $relImgUrl = '/LoopFit/public/assets/uploads/products/' . $firstImg;
                        ?>
                        <img src="<?= $relImgUrl ?>" alt="<?= htmlspecialchars($rel['name']) ?>">
                        <div class="related-info">
                            <h4><?= htmlspecialchars($rel['name']) ?></h4>
                            <div class="price"><?= number_format($rel['price'], 0, ',', '.') ?>đ <span>/ ngày</span></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>