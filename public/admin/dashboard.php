<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>
    <h1>Xin chào, <?= htmlspecialchars($_SESSION['admin_name']) ?></h1>
    <ul>
        <li><a href="products.php">Quản lý sản phẩm</a></li>
        <li><a href="orders.php">Quản lý đơn hàng</a></li>
        <li><a href="users.php">Quản lý người dùng</a></li>
        <li><a href="promotions.php">Quản lý khuyến mãi</a></li>
        <li><a href="logout.php">Đăng xuất</a></li>
    </ul>
</body>
</html>