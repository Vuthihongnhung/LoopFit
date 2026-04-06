<?php 
include dirname(__DIR__) . '/components/header.php'; 
include dirname(__DIR__) . '/components/navbar.php'; 
?>

<main class="products-main"> 
    <div class="loopfit-filter-row">
        <form method="GET" action="/LoopFit/public/products" id="filterForm" class="filter-flex-wrapper">
            <span class="filter-text">Bộ lọc:</span>

            <div class="filter-box">
                <select name="gender" onchange="this.form.submit()">
                    <option value="all">Tất cả giới tính</option>
                    <option value="male" <?= ($current_gender == 'male') ? 'selected' : '' ?>>Nam</option>
                    <option value="female" <?= ($current_gender == 'female') ? 'selected' : '' ?>>Nữ</option>
                </select>
            </div>

            <div class="filter-box">
                <select name="price" onchange="this.form.submit()">
                    <option value="all">Tất cả các mức giá</option>
                    <option value="0-100000" <?= ($current_price == '0-100000') ? 'selected' : '' ?>>Dưới 100k</option>
                    <option value="100000-300000" <?= ($current_price == '100000-300000') ? 'selected' : '' ?>>100k - 300k</option>
                    <option value="300000-up" <?= ($current_price == '300000-up') ? 'selected' : '' ?>>Trên 300k</option>
                </select>
            </div>

            <a href="/LoopFit/public/products" class="filter-reset">Xóa lọc</a>
        </form>
    </div>

    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <?php 
                    // Chuẩn bị dữ liệu cho file product-card.php
                    $name = htmlspecialchars($item['name']);
                    $price = number_format($item['price'], 0, ',', '.');
                    $image = !empty($item['image']) ? '/LoopFit/public/images/' . $item['image'] : 'https://via.placeholder.com/250x300?text=LoopFit';
                    
                    // Gọi file card sản phẩm (Điều chỉnh đường dẫn nếu cần)
                    include dirname(__DIR__) . '/components/product-card.php'; 
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; grid-column: 1/-1;">Không tìm thấy sản phẩm nào phù hợp.</p>
        <?php endif; ?>
    </div>
</main>

<?php include dirname(__DIR__) . '/components/footer.php'; ?>