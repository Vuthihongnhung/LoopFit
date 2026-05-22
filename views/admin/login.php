<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Đăng nhập Admin | LoopFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(145deg, #F0F0ED 0%, #e2e1db 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        .login-card {
            max-width: 480px;
            width: 100%;
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .card-header {
            background: #718BBC;
            padding: 32px 28px;
            text-align: center;
        }
        .card-header .icon {
            font-size: 52px;
            color: #F0F0ED;
            margin-bottom: 12px;
        }
        .card-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }
        .card-header p {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
        }
        .card-body {
            padding: 36px 32px 40px;
        }
        .input-field {
            margin-bottom: 24px;
        }
        .input-field label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #1e2a3e;
            margin-bottom: 8px;
        }
        .input-field label i {
            margin-right: 6px;
            color: #718BBC;
        }
        .input-field input {
            width: 100%;
            padding: 14px 16px;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            border: 1.5px solid #e0e4e8;
            border-radius: 40px;
            background: #fefefe;
            transition: all 0.2s;
            outline: none;
        }
        .input-field input:focus {
            border-color: #718BBC;
            box-shadow: 0 0 0 3px rgba(113, 139, 188, 0.2);
        }
        .btn-login {
            width: 100%;
            background: #718BBC;
            border: none;
            padding: 14px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: white;
            border-radius: 40px;
            cursor: pointer;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-login:hover {
            background: #5a6f9e;
        }
        .alert-error {
            background: #fee9e6;
            border-left: 4px solid #e5484d;
            color: #a31b1f;
            padding: 12px 16px;
            border-radius: 28px;
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .back-link {
            text-align: center;
            margin-top: 28px;
            font-size: 13px;
        }
        .back-link a {
            color: #718BBC;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 500px) {
            .card-body { padding: 28px 24px; }
            .card-header { padding: 28px 20px; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <div class="icon"><i class="fas fa-tshirt"></i></div>
            <h1>LoopFit Admin</h1>
            <p>Đăng nhập vào khu vực quản trị</p>
        </div>
        <div class="card-body">
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
            <?php elseif (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <form method="POST" action="<?= defined('BASE_URL') ? BASE_URL : '/LoopFit/public' ?>/admin/login">
                <div class="input-field">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="admin@loopfit.com" required autofocus>
                </div>
                <div class="input-field">
                    <label><i class="fas fa-lock"></i> Mật khẩu</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-arrow-right-to-bracket"></i> Đăng nhập</button>
            </form>
            <div class="back-link"><a href="<?= defined('BASE_URL') ? BASE_URL : '/LoopFit/public' ?>/home"><i class="fas fa-home"></i> Quay lại trang chủ</a></div>
        </div>
    </div>
</body>
</html>