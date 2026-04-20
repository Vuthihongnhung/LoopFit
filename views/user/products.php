<?php if (!empty($breadcrumb)): ?>
<div class="breadcrumb-wrapper">
    <div class="breadcrumb">
        <a href="/LoopFit/public/home">Trang chủ</a>
        <?php foreach ($breadcrumb as $index => $crumb): ?>
            <span class="separator">›</span>
            <?php if ($index === array_key_last($breadcrumb)): ?>
                <span class="current"><?= htmlspecialchars($crumb['name']) ?></span>
            <?php else: ?>
                <a href="/LoopFit/public/products?category=<?= $crumb['id'] ?>"><?= htmlspecialchars($crumb['name']) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="filter-wrapper">
    <div class="filter-header">
        <span class="filter-title">Bộ lọc</span>
        <button type="button" id="clear-filters" class="clear-btn">Xóa tất cả</button>
    </div>

    <div class="filter-row">
        <!-- Giới tính -->
        <div class="filter-col">
            <div class="filter-label">Giới tính</div>
            <div class="filter-chips" data-filter="gender">
                <button type="button" data-value="all" class="chip active">Tất cả</button>
                <button type="button" data-value="male" class="chip">Nam</button>
                <button type="button" data-value="female" class="chip">Nữ</button>
            </div>
        </div>

        <!-- Khoảng giá -->
        <div class="filter-col">
            <div class="filter-label">Khoảng giá</div>
            <div class="filter-chips" data-filter="price">
                <button type="button" data-value="all" class="chip active">Tất cả</button>
                <button type="button" data-value="0-100000" class="chip">Dưới 100.000đ</button>
                <button type="button" data-value="100000-300000" class="chip">100.000đ - 300.000đ</button>
                <button type="button" data-value="300000-up" class="chip">Trên 300.000đ</button>
            </div>
        </div>
    </div>
    <input type="hidden" id="filter-category" value="<?= htmlspecialchars($_GET['category'] ?? '') ?>">
</div>

<div class="container" style="padding: 30px;">
    <div class="product-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
        <?php foreach ($products as $product): ?>
            <?php include __DIR__ . '/../components/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>