<div class="product-card">
    <div class="cart-icon-overlay">
        <i class="fas fa-cart-plus"></i>
    </div>

    <div class="product-image">
        <img 
            src="<?= $image ?>" 
            alt="<?= $name ?>"
        >
    </div>

    <div class="product-info">
        <h3><?= $name ?></h3>

        <p class="price">
            <?= number_format($price, 0, ',', '.') ?>đ <span>/ ngày</span>
        </p>

        <button class="rent-btn">
            Thuê ngay
        </button>
    </div>
</div>