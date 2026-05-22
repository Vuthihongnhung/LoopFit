<?php
$featuredProducts = $featuredProducts ?? [];
$banners = $banners ?? [
    ['image' => '/assets/user/images/banner1.png'],
    ['image' => '/assets/user/images/banner2.png'],
    ['image' => '/assets/user/images/banner3.png'],
    ['image' => '/assets/user/images/banner4.png']
];

// Dữ liệu cho 4 hình nhỏ (promo) 
$promoItems = [
    ['image' => '/assets/user/images/promo1.png', 'link' => '/products?promo=1'],
    ['image' => '/assets/user/images/promo2.png', 'link' => '/products?promo=2'],
    ['image' => '/assets/user/images/promo3.png', 'link' => '/products?promo=3'],
    ['image' => '/assets/user/images/promo4.png', 'link' => '/products?promo=4']
];

// 4 mã giảm giá (voucher)
$vouchers = [
    ['code' => 'SALE30K', 'discount' => '30.000đ', 'condition' => 'Đơn đầu tiên', 'expiry' => '30/06/2025', 'image' => '/assets/user/images/voucher1.png'],
    ['code' => 'SALE50K', 'discount' => '50.000đ', 'condition' => 'Đơn tối thiểu 499.000đ', 'expiry' => '30/06/2025', 'image' => '/assets/user/images/voucher2.png'],
    ['code' => 'SALE100K', 'discount' => '100.000đ', 'condition' => 'Đơn tối thiểu 899.000đ', 'expiry' => '30/06/2025', 'image' => '/assets/user/images/voucher3.png'],
    ['code' => 'SALE5%', 'discount' => '5%', 'condition' => 'Đơn bất kỳ', 'expiry' => '30/06/2025', 'image' => '/assets/user/images/voucher4.png']
];
?>
<main>
    <!-- Banner Slider -->
    <div class="banner-slider">
        <div class="slider-container">
            <div class="slider-track" id="sliderTrack">
                <?php foreach ($banners as $index => $b): ?>
                <div class="slide" data-index="<?= $index ?>">
                    <div class="slide-content">
                        <div class="slide-img">
                            <img src="<?= BASE_URL . $b['image'] ?>" alt="banner">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="slider-btn prev" id="prevBtn">&#10094;</button>
        <button class="slider-btn next" id="nextBtn">&#10095;</button>
        <div class="slider-dots" id="sliderDots"></div>
    </div>

    <!-- Features -->
    <section class="features">
        <div class="feature-item">
            <i class="bi bi-truck"></i>
            <div>
                <h4>GIAO & THU HỒI TẬN NƠI</h4>
                <p>Nhanh chóng – tiện lợi – đúng hẹn</p>
            </div>
        </div>
        <div class="feature-item">
            <i class="bi bi-box-seam"></i>
            <div>
                <h4>KIỂM TRA HÀNG KHI<br>THANH TOÁN</h4>
                <p>Nhận hàng, kiểm tra ưng ý mới thanh toán</p>
            </div>
        </div>
        <div class="feature-item">
            <i class="bi bi-patch-check"></i>
            <div>
                <h4>ĐỔI SIZE MIỄN PHÍ</h4>
                <p>Hỗ trợ đổi nếu không vừa hoặc không ưng ý</p>
            </div>
        </div>
        <div class="feature-item">
            <i class="bi bi-headset"></i>
            <div>
                <h4>TƯ VẤN NHANH CHÓNG</h4>
                <p>Hỗ trợ 24/7 qua hotline: 0888.566.599</p>
            </div>
        </div>
    </section>

    <br>

    <!-- 4 hình nhỏ quảng cáo -->
    <section class="promo-grid">
        <div class="container">
            <div class="promo-items">
                <?php foreach ($promoItems as $item): ?>
                <a href="<?= BASE_URL . $item['link'] ?>" class="promo-card">
                    <img src="<?= BASE_URL . $item['image'] ?>" alt="promo">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 4 mã giảm giá (voucher) -->
    <section class="voucher-grid">
        <div class="container">
            <h2 class="title">Mã giảm giá hot</h2>
            <div class="voucher-items">
                <?php foreach ($vouchers as $v): ?>
                <div class="voucher-card">
                    <img src="<?= BASE_URL . $v['image'] ?>" alt="voucher" class="voucher-img">
                    <div class="voucher-code"><?= htmlspecialchars($v['code']) ?></div>
                    <div class="voucher-discount">Giảm <?= htmlspecialchars($v['discount']) ?></div>
                    <div class="voucher-condition"><?= htmlspecialchars($v['condition']) ?></div>
                    <div class="voucher-expiry">HSD: <?= htmlspecialchars($v['expiry']) ?></div>
                    <button class="copy-code-btn" data-code="<?= htmlspecialchars($v['code']) ?>">Sao chép</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Sản phẩm nổi bật -->
    <section class="products">
        <h2 style="text-align: center; color:#718BBC">★ SẢN PHẨM NỔI BẬT ★</h2>
        <br>
        <div class="product-grid">
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <?php 
                        $name = $product['name'];
                        $image = $product['image'];
                        $price = $product['price'];
                        include __DIR__ . '/../components/product-card.php';
                    ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Chưa có sản phẩm nổi bật.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- POPUP VOUCHER DÙNG ẢNH CÓ SẴN (CHỈ HIỆN TRANG CHỦ) -->
    <div id="voucher-popup" class="popup-overlay" style="display: none;">
        <div class="popup-container popup-img-wrapper">
            <button class="popup-close">&times;</button>
            <img src="<?= BASE_URL ?>/assets/user/images/popup.png" alt="Voucher 50K LoopFit" class="popup-img">
            <div class="popup-buttons">
                <button class="btn-copy" data-code="CHAOHE50">Sao chép mã</button>
                <a href="<?= BASE_URL ?>/products" class="btn-shop">Nhận ngay</a>
            </div>
        </div>
    </div>
</main>

<script>
    window.bannerData = {
        total: <?= count($banners) ?>,
        baseUrl: <?= json_encode(BASE_URL) ?>
    };
</script>
<script src="<?= BASE_URL ?>/assets/user/js/home.js"></script>
