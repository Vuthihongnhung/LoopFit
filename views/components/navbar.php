<!-- ICON -->
<header class="navbar">
<div class="nav-left">
        <span class="menu-icon" onclick="openMenu()">☰</span>
        <span>Browse</span>

        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search">
        </div>
    </div>

<!-- CENTER -->
    <div class="nav-center">
        <h1><a href="/LoopFit/public/home">LoopFit</a></h1>
    </div>

    <!-- RIGHT -->
    <div class="nav-right">
        <i class="bi bi-cart2"></i>
        <i class="bi bi-person"></i>
        <button class="join-btn">Join Now</button>
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

        <!-- TRENDING -->
        <li onclick="toggleMenu(this)">
            Trending <span>›</span>
        </li>
        <ul class="submenu">
            <li><a href="/LoopFit/public/products?category=street">Street Style</a></li>
            <li><a href="/LoopFit/public/products?category=minimal">Tối Giản</a></li>
            <li><a href="/LoopFit/public/products?category=y2k">Y2K</a></li>
            <li><a href="/LoopFit/public/products?category=korean">Style Hàn</a></li>
        </ul>

        <!-- Thuê nhiều nhất -->
        <a href="/LoopFit/public/products?sort=popular">Thuê nhiều nhất</a>
        
        <!-- NEW -->
        <li onclick="toggleMenu(this)">
            New <span>›</span>
        </li>
        <ul class="submenu">
            <li><a href="/LoopFit/public/products?tag=new">Vừa Ra Mắt</a></li>
            <li><a href="/LoopFit/public/products?tag=limited">Giới Hạn</a></li>
        </ul>

        <!-- SET -->
        <li onclick="toggleMenu(this)">
            Set Outfit <span>›</span>
        </li>
        <ul class="submenu">
            <li><a href="/LoopFit/public/products?style=dynamic">Năng Động</a></li>
            <li><a href="/LoopFit/public/products?style=elegant">Thanh Lịch</a></li>
            <li><a href="/LoopFit/public/products?style=cool">Cá Tính</a></li>
            <li><a href="/LoopFit/public/products?style=sexy">Quyến Rũ</a></li>
            <li><a href="/LoopFit/public/products?style=cute">Dễ Thương</a></li>
        </ul>

        <!-- SALE -->
        <li onclick="toggleMenu(this)">
            Ưu đãi <span>›</span>
        </li>
        <ul class="submenu">
            <li><a href="/LoopFit/public/products?offer=combo">Combo Tiết Kiệm</a></li>
            <li><a href="/LoopFit/public/products?offer=sale">Mã Giảm Giá</a></li>
            <li><a href="/LoopFit/public/products?offer=newuser">Ưu Đãi Khách Mới</a></li>
        </ul>

    </ul>
</div>

<!-- OVERLAY -->
<div id="overlay" onclick="closeMenu()"></div>