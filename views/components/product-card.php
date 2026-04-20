<?php
$id    = $product['id'] ?? 0;
$name  = $product['name'] ?? '';
$price = $product['price'] ?? 0;

// Lấy chuỗi ảnh từ cột image (có thể nhiều ảnh cách dấu phẩy)
$imagesStr = $product['image'] ?? '';
// Lấy ảnh đầu tiên làm ảnh đại diện
$firstImage = trim(explode(',', $imagesStr)[0] ?? '');
if (empty($firstImage)) {
    $firstImage = 'no-image.png';
}

// Đường dẫn cơ sở (chứa ảnh sản phẩm)
$baseUrl = '/LoopFit/public/uploads/';   // Đã sửa đúng đường dẫn
$imgUrl = $baseUrl . $firstImage;
?>

<div class="product-card">
    <a href="/LoopFit/public/product/<?= $id ?>" class="product-link">
        <div class="product-image">
            <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($name) ?>" 
                 onerror="this.onerror=null; this.src='/LoopFit/public/assets/user/images/no-image.png'">
        </div>
        <div class="product-info">
            <h3><?= htmlspecialchars($name) ?></h3>
            <p class="price"><?= number_format($price, 0, ',', '.') ?>đ <span class="unit">/ ngày</span></p>
        </div>
    </a>
</div>