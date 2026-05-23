<?php 
// Xác định tiêu đề và trạng thái
$isDiscount = $isDiscountPage ?? false;
$isNew = $isNewPage ?? false;
$isNormal = !$isDiscount && !$isNew;

if ($isDiscount) {
    $pageTitle = 'Sản phẩm ưu đãi - LoopFit';
} elseif ($isNew) {
    $pageTitle = 'Hàng mới về - LoopFit';
} else {
    $pageTitle = 'Sản phẩm nổi bật - LoopFit';
}
?>

<?php if (!empty($breadcrumb) && $isNormal): ?>
<div class="breadcrumb-wrapper">
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/home">Trang chủ</a>
        <?php foreach ($breadcrumb as $index => $crumb): ?>
            <span class="separator">›</span>
            <?php if ($index === array_key_last($breadcrumb)): ?>
                <span class="current"><?= htmlspecialchars($crumb['name']) ?></span>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/products?category=<?= $crumb['id'] ?>"><?= htmlspecialchars($crumb['name']) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

    <?php if ($isNormal): ?>
    <div class="filter-wrapper">
        <div class="filter-header">
            <span class="filter-title">Bộ lọc</span>
            <button type="button" id="clear-filters" class="clear-btn">Xóa tất cả</button>
        </div>

    <div class="filter-row">
        <!-- Bộ lọc Giới tính -->
        <div class="filter-col">
            <div class="filter-label">Giới tính</div>
            <div class="filter-chips" data-filter="gender">
                <button type="button" data-value="all" class="chip <?= ($current_gender ?? 'all') === 'all' ? 'active' : '' ?>">Tất cả</button>
                <button type="button" data-value="male" class="chip <?= ($current_gender ?? 'all') === 'male' ? 'active' : '' ?>">Nam</button>
                <button type="button" data-value="female" class="chip <?= ($current_gender ?? 'all') === 'female' ? 'active' : '' ?>">Nữ</button>
            </div>
        </div>

        <!-- Bộ lọc Khoảng giá -->
        <div class="filter-col">
            <div class="filter-label">Khoảng giá</div>
            <div class="filter-chips" data-filter="price">
                <button type="button" data-value="all" class="chip <?= ($current_price ?? 'all') === 'all' ? 'active' : '' ?>">Tất cả</button>
                <button type="button" data-value="0-100000" class="chip <?= ($current_price ?? 'all') === '0-100000' ? 'active' : '' ?>">Dưới 100.000đ</button>
                <button type="button" data-value="100000-300000" class="chip <?= ($current_price ?? 'all') === '100000-300000' ? 'active' : '' ?>">100.000đ - 300.000đ</button>
                <button type="button" data-value="300000-up" class="chip <?= ($current_price ?? 'all') === '300000-up' ? 'active' : '' ?>">Trên 300.000đ</button>
            </div>
        </div>
    </div>
    <input type="hidden" id="filter-category" value="<?= htmlspecialchars($current_cat ?? '') ?>">
</div>
<?php elseif ($isDiscount): ?>
    <div class="special-header discount-header">
        <h1>🔥 Sản phẩm ưu đãi</h1>
        <p class="subtitle">Thuê outfit yêu thích với mức giá tốt hơn ngay hôm nay</p>
    </div>
<?php elseif ($isNew): ?>
    <div class="special-header new-header">
        <h1>Hàng mới về</h1>
        <p class="subtitle">Outfit mới cập nhật mỗi tuần, luôn có lựa chọn mới cho bạn</p>
    </div>
<?php endif; ?>

<div class="container" style="padding: 30px;">
    <?php if (empty($products)): ?>
        <div class="empty-products">
            <i class="fas fa-tags"></i>
            <p>
                <?php if ($isDiscount): ?>
                    Hiện chưa có sản phẩm nào được giảm giá.
                <?php elseif ($isNew): ?>
                    Chưa có sản phẩm mới nào.
                <?php else: ?>
                    Không tìm thấy sản phẩm phù hợp.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="product-list">
            <?php foreach ($products as $product): ?>
                <?php include __DIR__ . '/../components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

