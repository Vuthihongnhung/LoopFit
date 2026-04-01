<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

    <h2 class="logo">LoopFit</h2>

    <!-- BÁN HÀNG -->
    <p class="menu-title">BÁN HÀNG</p>
    <ul class="menu">
        <li class="<?= $current=='dashboard.php'?'active':'' ?>">
            <a href="dashboard.php">Thuê đồ (POS)</a>
        </li>
        <li class="<?= $current=='orders.php'?'active':'' ?>">
            <a href="orders.php">Đơn thuê</a>
        </li>
        <li>
            <a href="#">Hóa đơn</a>
        </li>
    </ul>

    <!-- SẢN PHẨM -->
    <p class="menu-title">SẢN PHẨM</p>
    <ul class="menu">
        <li class="<?= $current=='products.php'?'active':'' ?>">
            <a href="products.php">Quần áo</a>
        </li>
        <li>
            <a href="#">Danh mục (Nam/Nữ/Thể thao...)</a>
        </li>
        <li>
            <a href="#">Size & tồn kho</a>
        </li>
    </ul>

    <!-- KHÁCH HÀNG -->
    <p class="menu-title">KHÁCH HÀNG</p>
    <ul class="menu">
        <li class="<?= $current=='users.php'?'active':'' ?>">
            <a href="users.php">Danh sách khách</a>
        </li>
        <li>
            <a href="#">Voucher</a>
        </li>
    </ul>

    <!-- QUẢN LÝ -->
    <p class="menu-title">QUẢN LÝ</p>
    <ul class="menu">
        <li>
            <a href="#">Trả đồ / Quá hạn</a>
        </li>
        <li>
            <a href="#">Lịch sử thuê</a>
        </li>
    </ul>

    <!-- BÁO CÁO -->
    <p class="menu-title">BÁO CÁO</p>
    <ul class="menu">
        <li>
            <a href="#">Doanh thu</a>
        </li>
        <li>
            <a href="#">Thống kê đơn thuê</a>
        </li>
    </ul>

    <!-- HỆ THỐNG -->
    <p class="menu-title">HỆ THỐNG</p>
    <ul class="menu">
        <li>
            <a href="#">Cài đặt</a>
        </li>
        <li>
            <a href="#">Nhân viên</a>
        </li>
    </ul>

</div>