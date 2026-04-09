<div class="container" style="padding: 30px;">
    <h2>Thanh toán</h2>

    <div style="display: flex; gap: 30px;">

        <!-- FORM -->
        <div style="flex: 1;">
            <h3>Thông tin nhận đồ</h3>

            <form method="POST" action="/LoopFit/public/checkout/process">

                <label>Họ tên</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Số điện thoại</label>
                <input type="text" name="phone" required>

                <label>Địa chỉ nhận đồ</label>
                <textarea name="address" rows="3" required></textarea>

                <label>Mã khuyến mãi</label>
                <input type="text" name="promo_code" placeholder="Nhập mã nếu có">

                <button type="submit" style="margin-top: 15px;">
                    Đặt thuê
                </button>
            </form>
        </div>

        <!-- SUMMARY -->
        <div style="width: 350px;">
            <h3>Đơn hàng</h3>

            <?php foreach ($cart as $item): ?>

            <div>
                <?= $item['name'] ?><br>

                Size: <?= $item['size'] ?><br>

                SL: <?= $item['quantity'] ?><br>

                Ngày: <?= $item['rent_date'] ?> → <?= $item['return_date'] ?><br>

                Số ngày: <?= $item['days'] ?><br>

                Thành tiền: <?= number_format($item['item_total']) ?>đ
            </div>

            <?php endforeach; ?>

            <h3>Tổng: <?= number_format($total) ?>đ</h3>
        </div>
</div>