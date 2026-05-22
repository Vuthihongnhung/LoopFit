<div class="auth-container">
    <h1>LoopFit</h1>
    <div class="subtitle">Tạo tài khoản để trải nghiệm thuê quần áo</div>

    <div id="alert-message" class="alert"></div>

    <form id="register-form" method = "POST">
        <div class="form-group">
            <label for="fullname">Họ và tên</label>
            <input type="text" id="fullname" name="name" placeholder="Nguyễn Văn A" required>
            <div class="error-message" id="error-fullname"></div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="example@email.com" required>
            <div class="error-message" id="error-email"></div>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" placeholder="Tối thiểu 6 ký tự" required>
            <div class="password-hint">Mật khẩu phải có ít nhất 6 ký tự</div>
            <div class="error-message" id="error-password"></div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            <div class="error-message" id="error-confirm"></div>
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="phone" placeholder="0901234567">
        </div>

        <div class="form-group">
            <label for="address">Địa chỉ</label>
            <textarea id="address" name="address" rows="2" placeholder="Địa chỉ giao hàng"></textarea>
        </div>

        <button type="submit">Đăng ký</button>
    </form>

    <div class="auth-link">
        Đã có tài khoản? <a href="/LoopFit/public/login">Đăng nhập</a>
    </div>
</div>