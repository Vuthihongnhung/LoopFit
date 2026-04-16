<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 500px; margin: 30px auto;">
    <h2>Đăng nhập</h2>

    <form action="<?= BASE_URL ?>/login" method="POST">
        <div style="margin-bottom: 15px;">
            <label>Email</label><br>
            <input type="email" name="email" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Mật khẩu</label><br>
            <input type="password" name="password" required style="width: 100%; padding: 8px;">
        </div>

        <button type="submit" style="padding: 10px 20px;">Đăng nhập</button>
    </form>

    <p style="margin-top: 15px;">
        Chưa có tài khoản?
        <a href="<?= BASE_URL ?>/register">Đăng ký</a>
    </p>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>