<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoopFit - Premium Clothing Rental</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/LoopFit/public/assets/user/css/style.css">
</head>
<body>

    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <main class="main-content">
        <?php 
            // Nếu dùng cách require trực tiếp file view từ Controller
            if (isset($viewContent)) {
                include $viewContent; 
            } 
        ?>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script src="/LoopFit/public/assets/js/main.js"></script>
</body>
</html>