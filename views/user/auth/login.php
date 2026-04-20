<div class="auth-container">
    <h1>LoopFit</h1>
    <div class="subtitle">Đăng nhập để thuê quần áo</div>

    <form id="login-form">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="example@email.com" required>
            <div class="error-message" id="error-email"></div>
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <div class="error-message" id="error-password"></div>
        </div>
        <button type="submit">Đăng nhập</button>
    </form>

    <div class="auth-link">
        Chưa có tài khoản? <a href="/LoopFit/public/register">Đăng ký ngay</a>
    </div>
</div>