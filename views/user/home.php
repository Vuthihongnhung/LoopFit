<main>

<!-- HERO -->
<section class="hero">
    <img src="/LoopFit/public/assets/user/images/banner-home.png" alt="banner">
</section>

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

<?php
// MOCK DATA (frontend)
$products = [
    ['name'=>'Áo gym nam','image'=>'/assets/user/images/banner-home.png','price'=>'120.000'],
    ['name'=>'Quần short','image'=>'/assets/user/images/demo2.jpg','price'=>'90.000'],
    ['name'=>'Set yoga','image'=>'/assets/user/images/demo3.jpg','price'=>'150.000'],
];
?>

<!-- SẢN PHẨM -->
<section class="products">
    <h2>Sản phẩm nổi bật</h2>
    <br>

    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <?php 
                $name = $p['name'];
                $image = $p['image'];
                $price = $p['price'];
                include __DIR__ . '/../components/product-card.php';
            ?>
        <?php endforeach; ?>
    </div>
</section>

</main>

