<?php 
$imagesRaw = $product['images'] ?? $product['image'] ?? '';
$images = array_map('trim', explode(',', $imagesRaw));

function getProductImageUrl($imgPath) {
    if (preg_match('#^(http|https)://#', $imgPath)) return $imgPath;
    if (strpos($imgPath, '/LoopFit/public/') === 0) return $imgPath;
    $base = '/LoopFit/public/uploads/';
    $clean = preg_replace('#^uploads/#', '', $imgPath);
    return $base . $clean;
}

$firstImage = getProductImageUrl($images[0]);
$deposit = $product['deposit_price'] ?? ($product['price'] * 0.3);
$hasDiscount = ($product['discount_percent'] ?? 0) > 0;
$discountPercent = (int)($product['discount_percent'] ?? 0);

// Chuẩn bị mảng ảnh URL đầy đủ để truyền sang JS
$imageUrls = array_map('getProductImageUrl', $images);
?>

<div class="product-detail-v2" data-product-id="<?= (int)$product['id'] ?>" data-images='<?= json_encode($imageUrls) ?>'>
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
                <?php foreach ($imageUrls as $index => $imgUrl): ?>
                    <img src="<?= $imgUrl ?>" data-index="<?= $index ?>" onclick="currentImage(<?= $index ?>)" alt="thumbnail">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cột phải: Thông tin chính -->
        <div class="product-detail-v2__info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-rating">
                <div class="stars">
                    <?php
                    $avgRating = round($product['avg_rating'] ?? 0, 1);
                    $fullStars = floor($avgRating);
                    $halfStar = ($avgRating - $fullStars) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $fullStars) echo '<i class="fas fa-star"></i>';
                        elseif ($halfStar && $i == $fullStars + 1) echo '<i class="fas fa-star-half-alt"></i>';
                        else echo '<i class="far fa-star"></i>';
                    }
                    ?>
                </div>
                <span class="rating-count"><?= (int)($product['total_reviews'] ?? 0) ?> đánh giá | </span>
                <span class="sold-count">Đã thuê <?= number_format($product['rented_count'] ?? 0) ?> lượt</span>
            </div>
            <div class="product-price">
    <span class="current-price"><?= number_format($currentPrice, 0, ',', '.') ?>đ</span>
    <span class="unit">/ ngày</span>
    
    <?php if ($hasBulkDiscount ?? false): ?>
        <del class="old-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</del>
        <span class="discount-badge bulk">🔥 Giảm hàng loạt -<?= $bulkDiscountPercent ?>%</span>
    <?php elseif (($product['discount_percent'] ?? 0) > 0): ?>
        <del class="old-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</del>
        <span class="discount-badge">-<?= (int)($product['discount_percent'] ?? 0) ?>%</span>
    <?php endif; ?>
    
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
                        else echo 'Cả nam và nữ';
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

            <!-- Màu sắc -->
            <div class="color-section">
                <span class="color-title">Màu sắc</span>
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

            <!-- Số lượng -->
            <div class="quantity-section">
                <div class="qty-control">
                    <button class="qty-btn" id="decreaseQty">-</button>
                    <input type="text" id="qty" value="1" readonly>
                    <button class="qty-btn" id="increaseQty">+</button>
                </div>
                <div class="stock-status-detail" id="stockStatusDetail">
                    <span id="stockStatusText"></span>
                    <span id="stockExtraInfo"></span>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn-add-to-cart" id="addToCartBtn" disabled>
                    Thêm vào giỏ hàng
                </button>
            </div>
        </div>
    </div>

    <!-- Mô tả sản phẩm -->
    <div class="product-description">
        <h3>Mô tả sản phẩm</h3>
        <div class="description-content">
            <?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.')) ?>
        </div>
    </div>

    <!-- Đánh giá -->
    <div class="reviews-section">
        <h3>Đánh giá sản phẩm</h3>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $rv): ?>
                <div class="review-item">
                    <strong><?= htmlspecialchars($rv['user_name']) ?></strong>
                    <div class="rating-stars">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <?= $i <= $rv['rating'] ? '★' : '☆' ?>
                        <?php endfor; ?>
                    </div>
                    <p><?= nl2br(htmlspecialchars($rv['comment'])) ?></p>
                    <small><?= date('d/m/Y', strtotime($rv['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
        <?php endif; ?>
    </div>

    <!-- Sản phẩm tương tự -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="related-products">
        <h3 class="related-title">✨ Có thể bạn cũng thích</h3>
        <div class="related-grid">
            <?php foreach ($relatedProducts as $rel): ?>
                <?php
                    $relPrice = $rel['price'];
                    $relDiscount = (int)($rel['discount_percent'] ?? 0);
                    if ($relDiscount > 0) $relPrice = $rel['price'] * (100 - $relDiscount) / 100;
                    $relImg = $rel['image'] ?? '';
                    $firstImg = trim(explode(',', $relImg)[0] ?? '');
                    if (empty($firstImg)) $firstImg = 'no-image.png';
                    $relImgUrl = '/LoopFit/public/uploads/' . $firstImg;
                ?>
                <div class="related-card">
                    <a href="/LoopFit/public/product/<?= $rel['id'] ?>">
                        <img src="<?= $relImgUrl ?>" alt="<?= htmlspecialchars($rel['name']) ?>" onerror="this.src='/LoopFit/public/uploads/no-image.png'">
                        <div class="related-info">
                            <h4><?= htmlspecialchars($rel['name']) ?></h4>
                            <div class="price">
                                <?= number_format($relPrice, 0, ',', '.') ?>đ <span>/ ngày</span>
                                <?php if ($relDiscount > 0): ?>
                                    <del><?= number_format($rel['price'], 0, ',', '.') ?>đ</del>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

