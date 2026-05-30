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
                <!-- Dashboard  -->
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/dashboard" class="nav-item <?php echo ($activeMenu === 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>

                <!-- ===== QUẢN LÝ SẢN PHẨM ===== -->
                <div class="nav-group">
                    <div class="nav-group-header <?php echo (in_array($activeMenu, ['categories', 'products', 'reviews'])) ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> <span>QUẢN LÝ SẢN PHẨM</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-group-content">
                        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/categories" class="nav-item-child <?php echo ($activeMenu === 'categories') ? 'active' : ''; ?>">
                            <i class="fas fa-folder-tree"></i> <span>Danh mục</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/products" class="nav-item-child <?php echo ($activeMenu === 'products') ? 'active' : ''; ?>">
                            <i class="fas fa-box"></i> <span>Sản phẩm</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/reviews" class="nav-item-child <?php echo ($activeMenu === 'reviews') ? 'active' : ''; ?>">
                            <i class="fas fa-star"></i> <span>Đánh giá</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/promotions" class="nav-item-child <?php echo ($activeMenu === 'promotions') ? 'active' : ''; ?>">
                            <i class="fas fa-tags"></i> <span>Khuyến mãi</span>
                        </a>
                    </div>
                </div>

                <!-- ===== QUẢN LÝ ĐƠN HÀNG ===== -->
                <div class="nav-group">
                    <div class="nav-group-header <?php echo (in_array($activeMenu, ['orders', 'payments'])) ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> <span>QUẢN LÝ ĐƠN HÀNG</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-group-content">
                        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/orders" class="nav-item-child <?php echo ($activeMenu === 'orders') ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i> <span>Đơn hàng</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/payments" class="nav-item-child <?php echo ($activeMenu === 'payments') ? 'active' : ''; ?>">
                            <i class="fas fa-credit-card"></i> <span>Thanh toán</span>
                        </a>
                    </div>
                </div>

                <!-- ===== QUẢN LÝ NGƯỜI DÙNG ===== -->
                <div class="nav-group">
                    <div class="nav-group-header <?php echo ($activeMenu === 'users') ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> <span>QUẢN LÝ NGƯỜI DÙNG</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-group-content">
                        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/users" class="nav-item-child <?php echo ($activeMenu === 'users') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> <span>Người dùng</span>
                        </a>
                    </div>
                </div>
                 <!-- ===== CHAT HỖ TRỢ ===== -->
                <a href="<?php echo BASE_URL; ?>/index.php?url=admin/chat" class="nav-item <?php echo ($activeMenu === 'chat') ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i> <span>Chat hỗ trợ</span>
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

                <!-- Icon chuông thông báo admin -->
                <button id="adminNotificationBell" style="background: #007bff; border: none; color: white; font-size: 20px; cursor: pointer; padding: 8px 12px; border-radius: 4px; margin-left: 15px;">
                    <i class="fas fa-bell"></i> <span id="adminNotificationBadge" style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px; margin-left: 5px; display: none;">0</span>
                </button>
                <div id="adminNotificationPanel" style="display: none; position: absolute; right: 20px; top: 50px; width: 320px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 1000; color: #333;">
                    <div style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; display: flex; justify-content: space-between;">
                        <span>Thông báo hệ thống</span>
                        <button id="adminMarkAllReadBtn" style="background: none; border: none; color: #007bff; cursor: pointer;">Đánh dấu đã đọc</button>
                    </div>
                    <div id="adminNotificationList" style="max-height: 350px; overflow-y: auto; padding: 5px;"></div>
                    <div style="padding: 8px; text-align: center; border-top: 1px solid #eee;">
                        <a href="<?= BASE_URL ?>/admin/notifications" style="color: #007bff; text-decoration: none;">Xem tất cả</a>
                    </div>
                </div>

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

    <!-- Các script -->
    <script src="<?php echo BASE_URL; ?>/assets/admin/js/admin.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/admin/js/admin-notification.js"></script>
    <script>
    window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script>
        // Toggle menu cha-con
        document.querySelectorAll('.nav-group-header').forEach(header => {
            header.addEventListener('click', function(e) {
                e.preventDefault();
                this.parentElement.classList.toggle('open');
            });
        });

        document.getElementById('menuToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('open');
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body> 
</html>