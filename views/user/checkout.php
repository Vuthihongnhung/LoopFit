<?php
/**
 * @var array $items
 * @var float $total_rent
 * @var float $total_deposit
 * @var array $addresses
 * @var array $vouchers
 */
?>
<div class="checkout-wrapper">
    <div class="checkout-container">
        <h1 class="checkout-title">Thanh toán</h1>
        <form id="checkout-form" method="POST">
            <input type="hidden" name="cart_ids" value="<?= htmlspecialchars($_GET['cart_ids'] ?? '') ?>">
            <input type="hidden" id="total_rent_value" value="<?= $total_rent ?? 0 ?>">
            <input type="hidden" id="total_deposit_value" value="<?= $total_deposit ?? 0 ?>">

            <div class="checkout-grid">
                <!-- Cột trái -->
                <div class="checkout-info">
                    <!-- Địa chỉ nhận hàng -->
                    <div class="checkout-section">
                        <h3>Địa chỉ nhận hàng</h3>
                        <div id="checkout-address-list" class="address-list">
                            <div class="loading">Đang tải...</div>
                        </div>
                        <button type="button" id="checkout-add-address" class="btn-link">+ Thêm địa chỉ mới</button>
                    </div>

                    <!-- Mã giảm giá (người dùng tự nhập) -->
                    <div class="checkout-section">
                        <h3>Mã giảm giá</h3>
                        <div class="voucher-group">
                            <input type="text" id="voucher-code" class="voucher-input" placeholder="Nhập mã giảm giá">
                            <button type="button" id="apply-voucher" class="btn-apply">Áp dụng</button>
                        </div>
                        <div id="voucher-message" class="message"></div>
                    </div>

                    <!-- Phương thức thanh toán -->
                    <div class="checkout-section">
                        <h3>Phương thức thanh toán</h3>
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash" checked> Thanh toán khi nhận hàng (COD)
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="transfer"> Chuyển khoản ngân hàng
                            </label>
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    <div class="checkout-section">
                        <h3>Ghi chú</h3>
                        <textarea name="notes" rows="3" placeholder="Ghi chú cho shop..."></textarea>
                    </div>
                </div>

                <!-- Cột phải -->
                <div class="checkout-order">
                    <div class="order-summary">
                        <h3>Đơn hàng của bạn</h3>
                        <div class="order-items">
                            <?php foreach ($items as $item): ?>
                                <div class="order-item">
                                    <div class="item-info">
                                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="item-meta">Size: <?= $item['size'] ?>, Màu: <?= $item['color'] ?></div>
                                        <div class="item-days"><?= $item['days'] ?> ngày x<?= $item['quantity'] ?></div>
                                    </div>
                                    <div class="item-price"><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="order-total-line">
                            <span>Tạm tính (tiền thuê)</span>
                            <span id="total-rent"><?= number_format($total_rent, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="order-total-line">
                            <span>Tiền cọc</span>
                            <span id="total-deposit"><?= number_format($total_deposit, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="order-total-line" id="shipping-row">
                            <span>Phí vận chuyển</span>
                            <span id="shipping-fee">0đ</span>
                        </div>
                        <div class="order-total-line discount-row" style="display:none;">
                            <span>Giảm giá</span>
                            <span id="discount-amount">0đ</span>
                        </div>
                        <div class="order-total-line final-total">
                            <span>Tổng cộng</span>
                            <span id="final-total"><?= number_format($total_rent + $total_deposit, 0, ',', '.') ?>đ</span>
                        </div>
                        <button type="submit" class="btn-submit">Đặt hàng</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal thêm/sửa địa chỉ -->
<div id="address-modal" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h3 id="modal-title">Thêm địa chỉ mới</h3>
        <input type="hidden" id="edit-address-id" value="">
        <div class="form-group">
            <label>Họ tên người nhận</label>
            <input type="text" id="addr-fullname" class="form-control" placeholder="Nguyễn Văn A">
        </div>
        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" id="addr-phone" class="form-control" placeholder="0901234567">
        </div>
        <div class="form-group">
            <label>Địa chỉ chi tiết</label>
            <textarea id="addr-line" rows="3" class="form-control" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"></textarea>
        </div>
        <div class="form-group">
            <label><input type="checkbox" id="addr-default"> Đặt làm địa chỉ mặc định</label>
        </div>
        <div style="text-align:right;">
            <button id="modal-close-btn" class="btn-secondary">Hủy</button>
            <button id="modal-save" class="btn-primary">Lưu</button>
        </div>
    </div>
</div>