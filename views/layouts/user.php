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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/user/css/chat-popup.css"> 
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
<script src="<?= BASE_URL ?>/assets/user/js/chat-popup.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/home.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/search.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/orders.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/cart.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/product.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/checkout.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/filter.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/auth.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/profile.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/payment.js"></script>

<!-- Chat Popup -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/user/css/chat-popup.css">
<div id="chatPopup">
    <div class="chat-popup-btn" id="chatToggleBtn">
        <i class="fas fa-headset"></i>
        <span class="chat-notification" id="chatNotification">0</span>
    </div>
    <div class="chat-popup-box" id="chatPopupBox" style="display: none;">
        <div class="chat-popup-header">
            <div class="chat-popup-info">
    <i class="fas fa-headset"></i>
    <div>
        <h4 id="chatUserName">LoopFit Support</h4>
        <p id="chatUserStatus">Online <i class="fas fa-circle"></i></p>
    </div>
</div>
            <button id="chatCloseBtn"><i class="fas fa-times"></i></button>
        </div>
        <div class="chat-popup-messages" id="chatPopupMessages">
            <div class="chat-welcome">
                <i class="fas fa-comments"></i>
                <p>Chào bạn! Hỗ trợ viên sẽ giúp bạn ngay</p>
            </div>
            <div id="faqList"></div>
        </div>
        <div class="chat-popup-input" style="position: relative;">
    <button class="sticker-btn" id="stickerBtn" style="position: absolute; left: 5px; top: 8px;">😊</button>
    <textarea id="chatPopupInput" rows="1" placeholder="Nhập tin nhắn..." style="padding-left: 45px;"></textarea>
    <button id="chatPopupSend"><i class="fas fa-paper-plane"></i></button>
    <div class="sticker-panel" id="stickerPanel"></div>
</div>
    </div>
</div>

</body>
</html>