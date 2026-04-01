<?php
include "config/database.php";
include "includes/header.php";
include "includes/navbar.php";
?>

<!-- HERO BANNER -->
<section class="hero">
    <img src="images/bannerdemo.jpg" alt="banner">
</section>

<!-- FEATURE -->
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

<!-- SẢN PHẨM -->
<section class="products">
    <h2>Sản phẩm nổi bật</h2>

    <div class="product-grid">
        <?php
        // Lấy sản phẩm từ database
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $p_name = $row['name'];
                $p_image = $row['image'];
                $p_price = $row['price'];

                include "components/product-card.php";
            }
        } else {
            echo "<p>Không có sản phẩm nào.</p>";
        }
        ?>
    </div>
</section>

<?php include "includes/footer.php"; ?>