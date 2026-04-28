<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title>LoopFit</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/user/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main-content">
    <?= $content ?>
</main>
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
<?php include __DIR__ . '/../components/footer.php'; ?>

<script>
    window.BASE_URL = '<?= BASE_URL ?>';
    window.isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/home.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/search.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/cart.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/product.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/checkout.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/filter.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/auth.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/profile.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/payment.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/orders.js"></script>

</body>
</html>