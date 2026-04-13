<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> | LoopFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/admin/css/admin.css">
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-dumbbell"></i>
                <span>LoopFit Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/dashboard" class="nav-item <?php echo ($activeMenu === 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/products" class="nav-item <?php echo ($activeMenu === 'products') ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> <span>Sản phẩm</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/orders" class="nav-item <?php echo ($activeMenu === 'orders') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> <span>Đơn hàng</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/users" class="nav-item <?php echo ($activeMenu === 'users') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> <span>Người dùng</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/promotions" class="nav-item <?php echo ($activeMenu === 'promotions') ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i> <span>Khuyến mãi</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/logout" class="nav-item">
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
                    <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <a href="<?php echo BASE_URL; ?>/index.php?url=admin/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>
            <div class="content-wrapper">
                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>
    <script src="<?php echo BASE_URL; ?>/assets/admin/js/admin.js"></script>
    <script>
        document.getElementById('menuToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('open');
        });
    </script>
</body>
</html>