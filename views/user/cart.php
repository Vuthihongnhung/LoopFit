<div class="cart-wrapper">
    <div class="cart-title">Giỏ hàng của bạn</div>
    <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <p>Giỏ hàng trống. <a href="<?= BASE_URL ?>/products">Mua ngay</a></p>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <div class="cart-header">
                    <div class="col-checkbox"><input type="checkbox" id="select-all" checked></div>
                    <div class="col-product">Sản phẩm</div>
                    <div class="col-price">Đơn giá</div>
                    <div class="col-qty">Số lượng</div>
                    <div class="col-total">Thành tiền</div>
                    <div class="col-action"></div>
                </div>
                <?php 
                $totalAll = 0;
                $totalDeposit = 0;
                foreach ($cart as $item):
                    $cartId = $item['cart_id'];
                    $name = $item['name'];
                    $price = (float)$item['price'];
                    $depositPerItem = (float)($item['deposit_price'] ?? $price * 0.3);
                    $quantity = (int)$item['quantity'];
                    $days = (int)($item['days'] ?? 1);
                    $size = $item['size'];
                    $color = $item['color'] ?? '';
                    $rentDate = $item['rent_date'];
                    $returnDate = $item['return_date'];
                    // Xử lý ảnh
                    $imageRaw = $item['image'];
                    $firstImage = trim(explode(',', $imageRaw)[0] ?? '');
                    if (empty($firstImage)) {
                        $firstImage = 'default-product.png';
                    }
                    $imgUrl = BASE_URL . '/uploads/' . $firstImage;
                    
                    $subtotal = $price * $quantity * $days;
                    $depositTotal = $depositPerItem * $quantity;
                    $totalAll += $subtotal;
                    $totalDeposit += $depositTotal;
                ?>
                    <div class="cart-item" 
                         data-cart-id="<?= $cartId ?>"
                         data-price="<?= $price ?>"
                         data-days="<?= $days ?>"
                         data-deposit-price="<?= $depositPerItem ?>"
                         data-rent-date="<?= htmlspecialchars($rentDate) ?>"
                         data-return-date="<?= htmlspecialchars($returnDate) ?>"
                         data-product-id="<?= $item['product_id'] ?? 0 ?>">
                         
                        <div class="col-checkbox">
                            <input type="checkbox" class="item-select" data-cart-id="<?= $cartId ?>" checked>
                        </div>
                        
                        <div class="col-product">
                            <div class="cart-product">
                                <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($name) ?>">
                                <div class="cart-info">
                                    <div class="cart-name"><i class="fas fa-edit edit-item-btn" style="cursor:pointer; margin-right:8px; color:#718BBC;"></i><?= htmlspecialchars($name) ?></div>
                                    <div class="cart-attr product-size-color">Size: <?= htmlspecialchars($size) ?> | Màu: <?= htmlspecialchars($color) ?></div>
                                    <div class="cart-attr product-rent-date">Ngày thuê: <?= date('d/m/Y', strtotime($rentDate)) ?></div>
                                    <div class="cart-attr product-return-date">Ngày trả: <?= date('d/m/Y', strtotime($returnDate)) ?></div>
                                    <div class="cart-attr product-days">Số ngày: <?= $days ?></div>
                                    <div class="cart-deposit">
                                        Cọc: <?= number_format($depositTotal, 0, ',', '.') ?>đ 
                                        <small>(<?= number_format($depositPerItem, 0, ',', '.') ?>đ / sp)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-price"><?= number_format($price, 0, ',', '.') ?>đ <small>/ngày</small></div>
                        
                        <div class="col-qty">
                            <div class="cart-quantity">
                                <button class="qty-btn" onclick="updateQuantity(<?= $cartId ?>, -1)">-</button>
                                <span id="qty-<?= $cartId ?>"><?= $quantity ?></span>
                                <button class="qty-btn" onclick="updateQuantity(<?= $cartId ?>, 1)">+</button>
                            </div>
                        </div>
                        
                        <div class="col-total" id="total-<?= $cartId ?>"><?= number_format($subtotal, 0, ',', '.') ?>đ</div>
                        
                        <div class="col-action">
                            <button class="cart-remove" onclick="removeFromCart(<?= $cartId ?>)" title="Xóa">✕</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="summary-title">Thông tin đơn hàng</div>
                <div class="summary-row">
                    <span>Tạm tính (tiền thuê)</span>
                    <span id="cart-total-temp"><?= number_format($totalAll, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row">
                    <span>Tiền cọc</span>
                    <span id="cart-deposit-total"><?= number_format($totalDeposit, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-total">
                    <span>Tổng cộng</span>
                    <span id="cart-total-all"><?= number_format($totalAll + $totalDeposit, 0, ',', '.') ?>đ</span>
                </div>
                <button class="btn-checkout" id="checkout-selected">Đặt hàng (đã chọn)</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.edit-item-btn {
    cursor: pointer;
    margin-right: 5px;
}
.edit-item-btn:hover {
    color: #4a6a9e;
}
.cart-name {
    display: inline-flex;
    align-items: center;
}
</style>