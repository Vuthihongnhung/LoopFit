<div class="cart-wrapper">
    <div class="cart-title">Giỏ hàng của bạn</div>
    <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <p>Giỏ hàng trống. <a href="/LoopFit/public/products">Mua ngay</a></p>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <div class="cart-header">
                    <div class="col-checkbox"><input type="checkbox" id="select-all"></div>
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
                    $price = (float)$item['price'];                           // giá thuê 1 ngày
                    $depositPerItem = (float)($item['deposit_price'] ?? $price * 0.3); // tiền cọc cho 1 sản phẩm
                    $quantity = (int)$item['quantity'];
                    $days = (int)($item['days'] ?? 1);                       // số ngày thuê
                    $size = $item['size'];
                    $color = $item['color'] ?? '';
                    $rentDate = $item['rent_date'];
                    $returnDate = $item['return_date'];
                      // Xử lý ảnh - sửa đường dẫn đúng
                    $imageRaw = $item['image'];
                    $firstImage = trim(explode(',', $imageRaw)[0] ?? '');
                    if (empty($firstImage)) {
                        $firstImage = 'default-product.png';
                    }
                    $imgUrl = BASE_URL . '/uploads/' . $firstImage;
                    
                    $subtotal = $price * $quantity * $days;                  // tổng tiền thuê dòng
                    $depositTotal = $depositPerItem * $quantity;             // tổng tiền cọc dòng
                    $totalAll += $subtotal;
                    $totalDeposit += $depositTotal;
                ?>
                    <div class="cart-item" 
                         data-cart-id="<?= $cartId ?>" 
                         data-price="<?= $price ?>" 
                         data-days="<?= $days ?>" 
                         data-deposit-price="<?= $depositPerItem ?>">
                         
                        <div class="col-checkbox">
                            <input type="checkbox" class="item-select" data-cart-id="<?= $cartId ?>" checked>
                        </div>
                        
                        <div class="col-product">
                            <div class="cart-product">
                                <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($name) ?>">
                                <div class="cart-info">
                                    <div class="cart-name"><?= htmlspecialchars($name) ?></div>
                                    <div class="cart-attr">Size: <?= htmlspecialchars($size) ?> | Màu: <?= htmlspecialchars($color) ?></div>
                                    <div class="cart-attr">Ngày thuê: <?= htmlspecialchars($rentDate) ?></div>
                                    <div class="cart-attr">Ngày trả: <?= htmlspecialchars($returnDate) ?></div>
                                    <div class="cart-attr">Số ngày: <?= $days ?></div>
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