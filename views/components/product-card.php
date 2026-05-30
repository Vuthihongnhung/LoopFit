<?php
/**
 * @var array $product (chứa id, name, image, price, discount_percent, rented_count, avg_rating, total_reviews, created_at)
 */
$id = $product['id'] ?? 0;
$name = $product['name'] ?? '';
$price = (float)($product['price'] ?? 0);
$discountPercent = isset($product['discount_percent']) ? (int)$product['discount_percent'] : 0;
$rentedCount = (int)($product['rented_count'] ?? 0);
$avgRating = round($product['avg_rating'] ?? 0, 1);
$totalReviews = (int)($product['total_reviews'] ?? 0);
$createdAt = $product['created_at'] ?? '';

// Xử lý ảnh
$imagesStr = $product['image'] ?? '';
$firstImage = trim(explode(',', $imagesStr)[0] ?? '');
if (empty($firstImage)) {
    $firstImage = 'default-product.png';
}
$imageUrl = BASE_URL . '/uploads/' . $firstImage;

// Giá sau giảm (ưu tiên bulk promotion)
$bulkFinalPrice = $product['final_price'] ?? null;
$hasBulkDiscount = ($bulkFinalPrice !== null && $bulkFinalPrice != $price);

if ($hasBulkDiscount) {
    $finalPrice = $bulkFinalPrice;
    $oldPriceHtml = '<span class="old-price">' . number_format($price) . 'đ</span>';
    $bulkDiscountPercent = round((1 - $finalPrice / $price) * 100);
} elseif ($discountPercent > 0) {
    $finalPrice = $price * (100 - $discountPercent) / 100;
    $oldPriceHtml = '<span class="old-price">' . number_format($price) . 'đ</span>';
} else {
    $finalPrice = $price;
    $oldPriceHtml = '';
}
$formattedFinalPrice = number_format($finalPrice) . 'đ';

// Kiểm tra sản phẩm mới (trong vòng 7 ngày)
$isNew = false;
if ($createdAt) {
    $createdTimestamp = strtotime($createdAt);
    $weekAgo = strtotime('-7 days');
    $isNew = ($createdTimestamp > $weekAgo);
}
?>

<div class="product-card">
    <a href="<?= BASE_URL ?>/product/<?= $id ?>" class="product-link">
        <div class="product-image">
            <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($name) ?>">
            <?php if ($hasBulkDiscount): ?>
    <span class="discount-badge bulk">🔥 -<?= $bulkDiscountPercent ?>%</span>
<?php elseif ($discountPercent > 0): ?>
    <span class="discount-badge">-<?= $discountPercent ?>%</span>
<?php endif; ?>
            <?php if ($isNew): ?>
                <span class="new-badge">🔥 Mới</span>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <h3 class="product-name"><?= htmlspecialchars($name) ?></h3>
            <div class="product-rating">
                <div class="stars">
                    <?php
                    $fullStars = floor($avgRating);
                    $halfStar = ($avgRating - $fullStars) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $fullStars) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($halfStar && $i == $fullStars + 1) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </div>
                <span class="rating-count">(<?= $totalReviews ?>)</span>
                <span class="sold-count">Đã thuê <?= number_format($rentedCount) ?></span>
            </div>
            <div class="product-price">
                <span class="current-price"><?= $formattedFinalPrice ?></span>
                <?= $oldPriceHtml ?>
                <span class="unit">/ngày</span>
            </div>
        </div>
    </a>
</div>