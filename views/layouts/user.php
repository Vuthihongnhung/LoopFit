<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoopFit</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/LoopFit/public/assets/user/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main-content">
    <?= $content ?>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/LoopFit/public/assets/user/js/main.js"></script>
<script src="/LoopFit/public/assets/user/js/cart.js"></script>
<script src="/LoopFit/public/assets/user/js/product.js"></script>
<script src="/LoopFit/public/assets/user/js/checkout.js"></script>
<script src="/LoopFit/public/assets/user/js/filter.js"></script>

</body>

</html>