<!-- NAVBAR -->
<header class="navbar">

    <!-- LEFT -->
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
        <h1><a href="/LoopFit/index.php">LoopFit</a></h1>
    </div>

    <!-- RIGHT -->
    <div class="nav-right">
        <i class="bi bi-cart2"></i>
        <i class="bi bi-person"></i>
        <button class="join-btn">Join Now</button>
    </div>

</header>


<!-- SIDEBAR (đặt ngoài header) -->
<div id="sidebar" class="sidebar">

    <div class="sidebar-header">
        <h2>LoopFit</h2>
        <span class="close-btn" onclick="closeMenu()">✕</span>
    </div>

    <div class="sidebar-banner">
        <img src="/LoopFit/images/bannerdemo.jpg">
    </div>

   <ul class="sidebar-menu">

    <li>
        <a href="/LoopFit/pages/trending.php">🔥 Trending </a>
    </li>

    <li>
        <a href="/LoopFit/pages/bestseller.php">⭐ Best seller </a>
    </li>

    <li>
        <a href="/LoopFit/pages/new.php">New </a>
    </li>

    <li>
        <a href="/LoopFit/pages/set.php">Set outfit </a>
    </li>

    <li>
        <a href="/LoopFit/pages/sale.php">Ưu đãi </a>
    </li>

</ul>

</div>

<!-- OVERLAY -->
<div id="overlay" onclick="closeMenu()"></div>