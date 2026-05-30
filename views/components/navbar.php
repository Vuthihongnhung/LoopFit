<!-- ICON -->
<header class="navbar">
    <div class="nav-left">
        <span class="menu-icon" onclick="openMenu()">☰</span>
        <span>Danh mục</span>

        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="search-input" placeholder="Tìm kiếm">
            <div id="search-results" class="search-results-dropdown"></div>
        </div>
    </div>

    <!-- CENTER -->
    <div class="nav-center">
        <h1><a href="<?= BASE_URL ?>/home">LoopFit</a></h1>
    </div>

    <!-- RIGHT -->
    <div class="nav-right">
        <a href="<?= BASE_URL ?>/cart" class="cart-icon">
            <i class="bi bi-cart2"></i>
            <span class="cart-count" id="cart-count">
                <?= $shared['cartCount'] ?? 0 ?>
            </span>
        </a>

        <?php if (isset($_SESSION['user'])): ?>
            <a href="<?= BASE_URL ?>/orders" class="truck-icon" title="Đơn hàng">
                <i class="bi bi-truck"></i>
            </a>
            <?php if (isset($_SESSION['user'])): ?>
    <?php include __DIR__ . '/notification-bell.php'; ?>
<?php endif; ?>
            <a href="<?= BASE_URL ?>/profile" class="user-icon">
                <i class="bi bi-person"></i>
            </a>
            <a href="<?= BASE_URL ?>/logout" class="logout-btn">Đăng xuất</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login" class="user-icon">
                <i class="bi bi-person"></i>
            </a>
            <a href="<?= BASE_URL ?>/register" class="join-btn">Đăng ký</a>
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
        <img src="<?= BASE_URL ?>/assets/user/images/banner-home.png" alt="LoopFit Promo">
    </div>
    <ul class="sidebar-menu">
        <?php
        $categoryModel = new \App\Models\Category();
        $allCategories = $categoryModel->getAll();
        
        $parents = [];
        $children = [];
        foreach ($allCategories as $cat) {
            if ($cat['parent_id'] == 0) {
                $parents[] = $cat;
            } else {
                $children[$cat['parent_id']][] = $cat;
            }
        }
        
        foreach ($parents as $parent):
            $hasChildren = !empty($children[$parent['id']]);
        ?>
            <li class="<?= $hasChildren ? 'has-submenu' : '' ?>">
                <div class="sidebar-item-wrapper">
                    <a href="<?= BASE_URL ?>/products?category=<?= $parent['id'] ?>" class="sidebar-item-link">
                        <?= htmlspecialchars($parent['name']) ?>
                    </a>
                    <?php if ($hasChildren): ?>
                        <span class="arrow-icon" onclick="toggleSubmenu(this)">▼</span>
                    <?php endif; ?>
                </div>
                <?php if ($hasChildren): ?>
                    <ul class="submenu">
                        <?php foreach ($children[$parent['id']] as $child): ?>
                            <li>
                                <a href="<?= BASE_URL ?>/products?category=<?= $child['id'] ?>">
                                    <?= htmlspecialchars($child['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        
        <li>
            <div class="sidebar-item-wrapper">
                <a href="<?= BASE_URL ?>/products?sort=popular&gender=all&price=all&category=" class="sidebar-item-link">⭐ Thuê nhiều nhất</a>
            </div>
            <div class="sidebar-item-wrapper">
                <a href="<?= BASE_URL ?>/products?filter=discount">🔥 Ưu đãi</a>
            </div>
            <div class="sidebar-item-wrapper">
                <a href="<?= BASE_URL ?>/products?filter=new">✨ Hàng mới</a>
            </div>
        </li>
    </ul>
</div>

<div id="overlay" onclick="closeMenu()"></div>

<script>
function toggleSubmenu(element) {
    const li = element.closest('li');
    if (!li) return;
    const submenu = li.querySelector('.submenu');
    if (!submenu) return;
    submenu.classList.toggle('active');
    element.classList.toggle('active');
}
</script>