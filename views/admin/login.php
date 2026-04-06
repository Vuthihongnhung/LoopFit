<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin | LoopFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header i {
            font-size: 48px;
            color: #ff6b6b;
        }
        .login-header h1 {
            color: white;
            font-size: 28px;
            margin-top: 12px;
        }
        .login-header p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            color: white;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .input-group input {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            background: rgba(255,255,255,0.9);
            font-size: 16px;
            transition: all 0.3s;
        }
        .input-group input:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(255,107,107,0.3);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #ff6b6b;
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #ff5252;
        }
        .error {
            background: rgba(255,0,0,0.8);
            color: white;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-dumbbell"></i>
            <h1>LoopFit Admin</h1>
            <p>Đăng nhập để quản trị</p>
        </div>
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> Sai email hoặc mật khẩu
            </div>
        <?php endif; ?>
        <form method="POST" action="?url=admin/login">
            <div class="input-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="admin@loopfit.com" required>
            </div>
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Mật khẩu</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit"><i class="fas fa-arrow-right"></i> Đăng nhập</button>
        </form>
    </div>
</body>
</html>