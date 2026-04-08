<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | LoopFit Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="http://localhost:8080/LoopFit/public/assets/admin/css/admin.css">
</head>
<body>
    <div class="app">
        <!-- Sidebar đơn giản tạm thời -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-dumbbell"></i>
                <span>LoopFit Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php?url=admin/dashboard" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="index.php?url=admin/products" class="nav-item">
                    <i class="fas fa-box"></i> <span>Sản phẩm</span>
                </a>
                <a href="index.php?url=admin/orders" class="nav-item">
                    <i class="fas fa-shopping-cart"></i> <span>Đơn hàng</span>
                </a>
                <a href="index.php?url=admin/users" class="nav-item">
                    <i class="fas fa-users"></i> <span>Người dùng</span>
                </a>
                <a href="index.php?url=admin/promotions" class="nav-item">
                    <i class="fas fa-tags"></i> <span>Khuyến mãi</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="index.php?url=admin/logout" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <header class="top-bar">
                <div class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></div>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                </div>
            </header>
            <div class="content-wrapper">
                <!-- Nội dung dashboard -->
                <div class="page-header">
                    <h1>Tổng quan</h1>
                    <p>Chào mừng trở lại, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>!</p>
                </div>
                <div class="stats-grid" id="statsGrid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-box"></i></div>
                        <div class="stat-info">
                            <h3>Sản phẩm</h3>
                            <p class="stat-number" id="statProducts">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                        <div class="stat-info">
                            <h3>Đơn hàng</h3>
                            <p class="stat-number" id="statOrders">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3>Người dùng</h3>
                            <p class="stat-number" id="statUsers">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-info">
                            <h3>Doanh thu</h3>
                            <p class="stat-number" id="statRevenue">0đ</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const BASE_URL = 'http://localhost:8080/LoopFit/public';
        fetch(BASE_URL + '/index.php?url=admin/api/stats')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('statProducts').innerText = data.total_products || 0;
                    document.getElementById('statOrders').innerText = data.total_orders || 0;
                    document.getElementById('statUsers').innerText = data.total_users || 0;
                    document.getElementById('statRevenue').innerText = new Intl.NumberFormat('vi-VN').format(data.revenue || 0) + 'đ';
                }
            });
    </script>
</body>
</html>