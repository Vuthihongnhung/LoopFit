<div class="loopfit-filter-row">
    <form method="GET" action="/LoopFit/public/products" class="filter-flex-wrapper">

        <span class="filter-text">Bộ lọc:</span>

        <div class="filter-box">
            <select name="gender" id="filter-gender">
                <option value="all">Tất cả giới tính</option>
                <option value="male">Nam</option>
                <option value="female">Nữ</option>
            </select>
        </div>

        <div class="filter-box">
            <select name="price" id="filter-price">
                <option value="all">Tất cả giá</option>
                <option value="0-100000">Dưới 100k</option>
                <option value="100000-300000">100k - 300k</option>
                <option value="300000-up">Trên 300k</option>
            </select>
        </div>

        <a href="/LoopFit/public/products" class="filter-reset">Xóa lọc</a>

    </form>
</div>

<div class="container" style="padding: 30px;">

    <h2>Sản phẩm</h2>

    <?php if (!empty($products)): ?>

        <div class="product-list" style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        ">

            <?php foreach ($products as $product): ?>

                <?php
                    $id    = $product['id'] ?? 0;
                    $name  = $product['name'] ?? 'Sản phẩm';
                    $price = $product['price'] ?? 0;
                    $image = $product['image'] ?? 'default.jpg';
                ?>

                <div class="product-card">

                    <!-- CLICK VÀO DETAIL -->
                    <a href="/LoopFit/public/product/<?= $id ?>" style="text-decoration:none; color:inherit;">

                        <div class="product-image">
                            <img 
                                src="/LoopFit/public/uploads/products/<?= htmlspecialchars($image) ?>" 
                                alt="<?= htmlspecialchars($name) ?>"
                                style="width:100%; height:250px; object-fit:cover;"
                            >
                        </div>

                        <div class="product-info">
                            <h3><?= htmlspecialchars($name) ?></h3>

                            <p class="price">
                                <?= number_format((float)$price, 0, ',', '.') ?>đ 
                                <span>/ ngày</span>
                            </p>
                        </div>

                    </a>

                    <!-- BUTTON -->
                    <button class="rent-btn"
                        onclick="goToDetail(<?= $id ?>)">
                        Thuê ngay
                    </button>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


