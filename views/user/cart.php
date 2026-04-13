<?php if (!isset($cartItems)) $cartItems = []; ?>

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
        <?php if (!empty($cartItems)): ?>
            <?php foreach ($cartItems as $item): ?>

                <?php $cartId = (int)$item['cart_id']; ?>

                <div class="cart-item"
                     id="cart-item-<?= $cartId ?>"
                     data-price="<?= (float)$item['price'] ?>">

                    <!-- IMAGE -->
                    <div class="cart-img">
                        <img src="/LoopFit/public/uploads/products/<?= htmlspecialchars($item['image']) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>"
                             style="width:80px;">
                    </div>

                    <!-- NAME -->
                    <div class="cart-name">
                        <b><?= htmlspecialchars($item['name']) ?></b>
                        <br>
                        <small>Size: <?= htmlspecialchars($item['size'] ?? 'N/A') ?></small>
                        <br>
                        <small>Màu: <?= htmlspecialchars($item['color'] ?? '') ?></small>
                        <br>

                        <small>
                            <?php if (!empty($item['rent_date']) && !empty($item['return_date'])): ?>
                                <?= date('d/m/Y', strtotime($item['rent_date'])) ?>
                                →
                                <?= date('d/m/Y', strtotime($item['return_date'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>

                    <!-- PRICE -->
                    <div class="cart-price">
                        <?= number_format((float)$item['price'], 0, ',', '.') ?>đ
                    </div>

                    <!-- QUANTITY -->
                    <div class="qty-box">
                        <button onclick="updateQuantity(<?= $cartId ?>, -1)">-</button>

                        <span id="qty-<?= $cartId ?>">
                            <?= (int)$item['quantity'] ?>
                        </span>

                        <button onclick="updateQuantity(<?= $cartId ?>, 1)">+</button>
                    </div>

                    <!-- TOTAL -->
                    <div class="cart-total" id="total-<?= $cartId ?>">
                        <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                    </div>

                    <!-- REMOVE -->
                    <div class="remove-btn"
                         onclick="removeFromCart(<?= $cartId ?>)">
                        ❌ Xóa
                    </div>

                </div>

            <?php endforeach; ?>
        <?php else: ?>

            <!-- EMPTY -->
            <div class="empty-cart" style="text-align:center; padding:40px;">
                <p style="font-size:18px;">🛒 Giỏ hàng của bạn đang trống</p>
                <a href="/LoopFit/public/products"
                   style="color:#718BBC; text-decoration:none; font-weight:bold;">
                    Đi thuê ngay
                </a>
            </div>

        <?php endif; ?>

    </div>

    <!-- TOTAL -->
    <div class="cart-summary" style="margin-top:30px;">
        <h3>
            Tổng:
            <span id="cart-total">
                <?php
                    $total = 0;

                    if (!empty($cartItems)) {
                        foreach ($cartItems as $item) {
                            $total += (float)$item['price'] * (int)$item['quantity'];
                        }
                    }

                    echo number_format($total, 0, ',', '.') . 'đ';
                ?>
            </span>
        </h3>

        <?php if (!empty($cartItems)): ?>
            <a href="/LoopFit/public/checkout">
                <button class="checkout-btn">Thanh toán</button>
            </a>
        <?php endif; ?>
    </div>
</div>