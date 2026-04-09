<?php
$name  = $product['name'] ?? '';
$price = $product['price'] ?? 0;
$image = $product['image'] ?? '';
?>

<div class="product-card">
    <div class="cart-icon-overlay">
        <i class="fas fa-cart-plus"></i>
    </div>

    <div class="product-image">
        <img 
            src="/LoopFit/public/assets/user/images/<?= htmlspecialchars($image) ?>" 
            alt="<?= htmlspecialchars($name) ?>"
        >
    </div>

    <div class="product-info">
        <h3><?= htmlspecialchars($name) ?></h3>

        <p class="price">
            <?= number_format($price, 0, ',', '.') ?>đ <span>/ ngày</span>
        </p>

        <?php if (empty($hideButton)) : ?>
            <button class="rent-btn">
                Thuê ngay
            </button>
        <?php endif; ?>
    </div>
</div>