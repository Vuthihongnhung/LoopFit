<!-- ICON -->
<header class="navbar">
    <div class="nav-left">
        <span class="menu-icon" onclick="openMenu()">☰</span>
        <span>Danh mục</span>

        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Tìm kiếm">
        </div>
    </div>

    <!-- CENTER -->
    <div class="nav-center">
        <h1><a href="/LoopFit/public/home">LoopFit</a></h1>
    </div>

    <!-- RIGHT -->
    <div class="nav-right">
        <a href="/LoopFit/public/cart" class="cart-icon">
            <i class="bi bi-cart2"></i>
            <span class="cart-count" id="cart-count">
                <?= $shared['cartCount'] ?? 0 ?>
            </span>
        </a>

        <?php if (isset($_SESSION['user'])): ?>
            <!-- Đã đăng nhập -->
            <a href="/LoopFit/public/orders" class="truck-icon" title="Đơn hàng">
                <i class="bi bi-truck"></i>
            </a>
            <a href="/LoopFit/public/profile" class="user-icon">
                <i class="bi bi-person"></i>
            </a>
            <a href="/LoopFit/public/logout" class="logout-btn">Đăng xuất</a>
        <?php else: ?>
            <!-- Chưa đăng nhập -->
            <a href="/LoopFit/public/login" class="user-icon">
                <i class="bi bi-person"></i>
            </a>
            <a href="/LoopFit/public/register" class="join-btn">Đăng ký</a>
        <?php endif; ?>
    </div>
</header>

<!-- SIDEBAR -->
<div id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h2>LoopFit</h2>
        <span class="close-btn" onclick="closeMenu()">✕</span>
    </div>
    <div class="sidebar-banner">
        <img src="/LoopFit/public/assets/user/images/banner-home.png" alt="LoopFit Promo">
    </div>
    <ul class="sidebar-menu">
        <?php
        // Lấy danh sách categories từ database
        $categoryModel = new \App\Models\Category();
        $allCategories = $categoryModel->getAll(); // giả sử getAll() trả về mảng tất cả categories
        
        // Nhóm categories theo parent_id
        $parents = [];
        $children = [];
        foreach ($allCategories as $cat) {
            if ($cat['parent_id'] == 0) {
                $parents[] = $cat;
            } else {
                $children[$cat['parent_id']][] = $cat;
            }
        }
        
        // Hiển thị các danh mục cha
        foreach ($parents as $parent):
            $hasChildren = !empty($children[$parent['id']]);
        ?>
            <li onclick="toggleMenu(this)">
                <?= htmlspecialchars($parent['name']) ?>
                <?php if ($hasChildren): ?>
                    <span>›</span>
                <?php endif; ?>
            </li>
            <?php if ($hasChildren): ?>
                <ul class="submenu">
                    <?php foreach ($children[$parent['id']] as $child): ?>
                        <li>
                            <a href="/LoopFit/public/products?category=<?= $child['id'] ?>">
                                <?= htmlspecialchars($child['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <!-- Link đặc biệt: Thuê nhiều nhất (sắp xếp theo lượt thuê) -->
        <li>
            <a href="/LoopFit/public/products?sort=popular">Thuê nhiều nhất</a>
        </li>
    </ul>
</div>

<!-- OVERLAY -->
<div id="overlay" onclick="closeMenu()"></div>