<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 600px; margin: 30px auto;">
    <h2>Đăng ký</h2>

    <form action="<?= BASE_URL ?>/register" method="POST">
        <div style="margin-bottom: 15px;">
            <label>Họ tên</label><br>
            <input type="text" name="name" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Email</label><br>
            <input type="email" name="email" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Mật khẩu</label><br>
            <input type="password" name="password" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Số điện thoại</label><br>
            <input type="text" name="phone" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Địa chỉ</label><br>
            <textarea name="address" rows="4" style="width: 100%; padding: 8px;"></textarea>
        </div>

        <button type="submit" style="padding: 10px 20px;">Đăng ký</button>
    </form>

    <p style="margin-top: 15px;">
        Đã có tài khoản?
        <a href="<?= BASE_URL ?>/login">Đăng nhập</a>
    </p>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>