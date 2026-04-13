<?php
$id    = $product['id'] ?? 0;
$name  = $product['name'] ?? '';
$price = $product['price'] ?? 0;
$image = $product['image'] ?? '';
?>

<div class="product-card">

    <!-- CLICK VÀO CARD -->
    <a href="/LoopFit/public/product/<?= $id ?>" style="text-decoration:none; color:inherit;">

        <div class="cart-icon-overlay">
            <i class="fas fa-cart-plus"></i>
        </div>

        <div class="product-image">
            <img 
                src="/LoopFit/public/uploads/products/<?= htmlspecialchars($image) ?>" 
                alt="<?= htmlspecialchars($name) ?>"
            >
        </div>

        <div class="product-info">
            <h3><?= htmlspecialchars($name) ?></h3>

            <p class="price">
                <?= number_format($price, 0, ',', '.') ?>đ 
                <span>/ ngày</span>
            </p>
        </div>

    </a>

    <?php if (empty($hideButton)) : ?>
        <button class="rent-btn"
            onclick="addToCartQuick(<?= $id ?>)">
            Thuê ngay
        </button>
    <?php endif; ?>

</div>