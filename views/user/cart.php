

<div class="container" style="padding:30px;">
    <h2>Giỏ hàng</h2>

    <div class="cart-container">

        <!-- HEADER -->
        <div class="cart-header">
            <div></div>
            <div>Sản phẩm</div>
            <div>Đơn giá</div>
            <div>Số lượng</div>
            <div>Thành tiền</div>
            <div></div>
        </div>

        <!-- ITEMS -->
        <?php if (!empty($cart)): ?>
            <?php foreach ($cart as $item): ?>
            <div class="cart-item" 
                id="cart-item-<?= $item['id'] ?>" 
                data-price="<?= $item['price'] ?>">

                <!-- IMAGE -->
                <div class="cart-img">
                    <img src="/LoopFit/public/uploads/products/<?= $item['image'] ?>">
                </div>

                <!-- NAME -->
                <div class="cart-name">
                    <?= htmlspecialchars($item['name']) ?>
                    <br>
                    <small>Size: <?= $item['size'] ?? 'N/A' ?></small>
                    <br>
                    <small>
                        <?= $item['rent_date'] ?? '' ?> → <?= $item['return_date'] ?? '' ?>
                    </small>
                </div>

                <!-- PRICE -->
                <div class="cart-price">
                    <?= number_format($item['price'],0,',','.') ?>đ
                </div>

                <!-- QUANTITY -->
                <div class="qty-box">
                    <button onclick="updateQuantity(<?= $item['id'] ?>, -1)">-</button>
                    <span id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                    <button onclick="updateQuantity(<?= $item['id'] ?>, 1)">+</button>
                </div>

                <!-- TOTAL -->
                <div class="cart-total" id="total-<?= $item['id'] ?>">
                    <?= number_format($item['price'] * $item['quantity'],0,',','.') ?>đ
                </div>

                <!-- REMOVE -->
                <div class="remove-btn" onclick="removeFromCart(<?= $item['id'] ?>)">
                    Xóa
                </div>

            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Giỏ hàng trống</p>
        <?php endif; ?>

    </div>

    <!-- TOTAL -->
    <div class="cart-summary">
        <h3>Tổng: <span id="cart-total">
            <?php
                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
                echo number_format($total,0,',','.') . 'đ';
            ?>
        </span></h3>

        <button class="checkout-btn">Thanh toán</button>
    </div>
</div>