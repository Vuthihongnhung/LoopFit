<?php
// views/user/profile.php
// Dữ liệu: $user, $orders, $addresses (nếu có)
?>
<div class="profile-wrapper">
    <div class="profile-sidebar">
        <div class="user-avatar">
            <div class="avatar-placeholder">
                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <h3><?= htmlspecialchars($user['name'] ?? 'Khách hàng') ?></h3>
            <p><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></p>
        </div>
        <ul class="profile-menu">
            <li class="menu-item active" data-tab="account">Thông tin tài khoản</li>
            <li class="menu-item" data-tab="voucher">Mã ưu đãi</li>
            <li class="menu-item" data-tab="orders">Đơn hàng</li>
            <li class="menu-item" data-tab="address">Sổ địa chỉ</li>
            <li class="menu-item" data-tab="logout">Đăng xuất</li>
        </ul>
    </div>

    <div class="profile-content">
        <!-- TAB THÔNG TIN TÀI KHOẢN -->
        <div class="tab-pane active" id="tab-account">
            <div class="profile-card">
                <h3>Thông tin tài khoản</h3>
                <form id="update-profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Họ và tên</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled readonly>
                            <small>Email không thể thay đổi</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Địa chỉ</label>
                            <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Cập nhật thông tin</button>
                    <div id="profile-message" class="form-message"></div>
                </form>
            </div>

            <div class="profile-card">
                <h3>Đổi mật khẩu</h3>
                <form id="change-password-form">
                    <div class="form-group">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" required>
                        <small>Tối thiểu 6 ký tự</small>
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-primary">Đổi mật khẩu</button>
                    <div id="password-message" class="form-message"></div>
                </form>
            </div>
        </div>

        <!-- TAB MÃ ƯU ĐÃI -->
        <div class="tab-pane" id="tab-voucher">
            <div class="profile-card">
                <h3>Mã ưu đãi của bạn</h3>
                <p>Hiện tại chưa có mã ưu đãi nào.</p>
            </div>
        </div>

        <!-- TAB ĐƠN HÀNG -->
        <div class="tab-pane" id="tab-orders">
            <div class="profile-card">
                <h3>Lịch sử đơn hàng</h3>
                <?php if (empty($orders)): ?>
                    <p>Bạn chưa có đơn hàng nào.</p>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <div class="order-header">
                                    <span class="order-id">#<?= $order['id'] ?></span>
                                    <span class="order-date"><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                                    <span class="order-status status-<?= strtolower($order['status']) ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </div>
                                <div class="order-body">
                                    <div><strong>Ngày thuê:</strong> <?= date('d/m/Y', strtotime($order['rent_date'])) ?></div>
                                    <div><strong>Ngày trả dự kiến:</strong> <?= date('d/m/Y', strtotime($order['return_date'])) ?></div>
                                    <?php if ($order['actual_return_date']): ?>
                                        <div><strong>Ngày trả thực tế:</strong> <?= date('d/m/Y', strtotime($order['actual_return_date'])) ?></div>
                                    <?php endif; ?>
                                    <div><strong>Tổng tiền:</strong> <?= number_format($order['total_price'], 0, ',', '.') ?>đ</div>
                                    <?php if ($order['late_fee'] > 0): ?>
                                        <div><strong>Phí trễ:</strong> <?= number_format($order['late_fee'], 0, ',', '.') ?>đ</div>
                                    <?php endif; ?>
                                </div>
                                <div class="order-footer">
                                    <a href="/LoopFit/public/order/<?= $order['id'] ?>" class="order-detail-link">Xem chi tiết</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB SỔ ĐỊA CHỈ -->
        <div class="tab-pane" id="tab-address">
            <div class="profile-card">
                <h3>Sổ địa chỉ <button id="add-address-btn" class="btn-primary" style="float:right;">+ Thêm địa chỉ mới</button></h3>
                <div id="address-list" class="address-list">
                    <div class="loading">Đang tải...</div>
                </div>
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

        <!-- TAB ĐĂNG XUẤT -->
        <div class="tab-pane" id="tab-logout">
            <div class="profile-card">
                <h3>Đăng xuất</h3>
                <p>Bạn có chắc chắn muốn đăng xuất?</p>
                <button id="confirm-logout" class="btn-primary">Đăng xuất ngay</button>
            </div>
        </div>
    </div>
</div>

