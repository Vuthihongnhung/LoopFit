<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle ?? 'Admin'; ?></title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="<?php echo BASE_URL; ?>/admin/dashboard">Dashboard</a>
        <a href="<?php echo BASE_URL; ?>/admin/products">Sản phẩm</a>
    </div>

    <!-- Content -->
    <div class="main-content">
        <?php require_once $content; ?>
    </div>

</body>
</html>