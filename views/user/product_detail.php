<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<div class="product-detail">

    <!-- LEFT: IMAGE -->
    <div class="product-left">
        <div class="main-image">
            <img id="mainImg" src="/LoopFit/public/assets/user/images/banner-home.png">
        </div>

        <div class="thumbnail-list">
            <img src="/LoopFit/public/assets/user/images/banner-home.png" onclick="changeImg(this)">
            <img src="/LoopFit/public/assets/user/images/stylehan_nu2.jpg" onclick="changeImg(this)">
            <img src="/LoopFit/public/assets/user/images/banner-home.png" onclick="changeImg(this)">
        </div>
    </div>

    <!-- RIGHT: INFO -->
    <div class="product-right">

        <h1 class="product-title">
            Áo Hoodie Basic form rộng
        </h1>

        <div class="sub-info">
            <span>Chưa có đánh giá</span> |
            <span>Đã thuê 0</span>
        </div>

        <div class="price-box">
            120.000đ / ngày
        </div>

        <div class="info">
            <p><b>Danh mục:</b> Áo</p>
            <p><b>Style:</b> Street</p>
            <p><b>Giới tính:</b> Unisex</p>
        </div>

        <div class="desc">
            Áo hoodie form rộng, chất nỉ dày, phù hợp đi chơi, đi học.
        </div>

        <div class="desc">
            Áo hoodie form rộng, chất nỉ dày, phù hợp đi chơi, đi học.
        </div>

        <!-- 👇 CHÈN Ở ĐÂY -->
        <!-- SIZE -->
        <div class="size-section">
            <span>Size</span>
            <div class="size-options">
                <button class="size-btn active">S</button>
                <button class="size-btn">M</button>
                <button class="size-btn">L</button>
            </div>
        </div>

        <!-- DATE RENT -->
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

        <!-- SỐ LƯỢNG -->
        <div class="quantity">
            <span>Số lượng</span>
            <div class="qty-box">
                <button onclick="decrease()">-</button>
                <input type="text" id="qty" value="1">
                <button onclick="increase()">+</button>
            </div>
            <span class="stock">Còn hàng</span>
        </div>

        <!-- BUTTON -->
        <div class="actions">
            <button class="btn-cart">
                🛒 Thêm vào giỏ
            </button>

            <button class="btn-buy">
                Thuê ngay
            </button>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
