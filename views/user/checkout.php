<script>
window.cart = <?= json_encode($items ?? []) ?>;
</script>

<div class="checkout-container">
    <h2 class="checkout-title">Thanh toán đơn thuê</h2>

    <?php if (empty($items)): ?>
        <div style="text-align:center; padding:40px;">
            <p>🛒 Giỏ hàng trống</p>
            <a href="/LoopFit/public/products" style="color:#718BBC;">Đi thuê ngay</a>
        </div>
    <?php else: ?>

    <form method="POST" action="/LoopFit/public/checkout/process">

    <div style="display:flex; gap:30px;">

        <!-- LEFT -->
        <div style="flex:1;">

            <div class="checkout-box">

                <h3>Thông tin người thuê</h3>

                <input type="text" name="customer_name" placeholder="Họ và tên" required>
                <input type="text" name="customer_phone" placeholder="Số điện thoại" required>

                <h3>Địa chỉ nhận đồ</h3>

                <select name="address_id" required>
                    <?php foreach ($addresses as $addr): ?>
                        <option value="<?= (int)$addr['id'] ?>">
                            <?= htmlspecialchars($addr['name']) ?> - 
                            <?= htmlspecialchars($addr['phone']) ?> - 
                            <?= htmlspecialchars($addr['address']) ?>
                            <?= $addr['is_default'] ? '(Mặc định)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <a href="/LoopFit/public/profile" style="font-size:14px;">
                    + Thêm địa chỉ mới
                </a>

                <h3 style="margin-top:20px;">Mã giảm giá</h3>

                <select name="promo_code" id="promo-select">
                    <option value="">-- Không dùng mã --</option>

                    <?php foreach ($promotions as $promo): ?>
                        <option value="<?= htmlspecialchars($promo['code']) ?>"
                                data-percent="<?= (int)$promo['discount_percent'] ?>">
                            <?= htmlspecialchars($promo['code']) ?>
                            (<?= (int)$promo['discount_percent'] ?>%)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn-submit" type="submit">
                    Đặt thuê
                </button>

            </div>

        </div>

        <!-- RIGHT -->
        <div style="width:350px;">

            <div class="checkout-box">

                <h3>Đơn hàng</h3>

                <?php foreach ($items as $item): ?>
                    <div class="order-item">

                        <strong><?= htmlspecialchars($item['name']) ?></strong><br>

                        Size: <?= htmlspecialchars($item['size'] ?? 'N/A') ?><br>
                        SL: <?= (int)$item['quantity'] ?><br>

                        <?= date('d/m/Y', strtotime($item['rent_date'])) ?>
                        →
                        <?= date('d/m/Y', strtotime($item['return_date'])) ?><br>

                        <?= (int)$item['days'] ?> ngày<br>

                        <b><?= number_format($item['item_total'], 0, ',', '.') ?>đ</b>

                    </div>
                <?php endforeach; ?>

                <div class="total-box">
                    Tổng tiền:
                    <b id="cart-total"><?= $total ?></b>
                </div>

                <div class="total-box">
                    Giảm giá:
                    <b id="discount-amount">0</b>
                </div>

                <hr>

                <div class="final">
                    Thanh toán:
                    <span id="final-total"><?= $total ?></span>
                </div>

            </div>

        </div>

    </div>

    </form>

    <?php endif; ?>
</div>
