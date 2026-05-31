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
    
    <!-- CSS cho thông báo -->
    <style>
        /* Notification styles */
        .notification-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            margin-right: 15px;
        }
        
        .notification-bell {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            position: relative;
            color: #333;
            transition: all 0.2s ease;
            padding: 0;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .notification-bell:hover {
            background: #f0f0f0;
            color: #718bbc;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            min-width: 18px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        .notification-panel {
            position: fixed;
            left: 0;
            top: 0;
            width: 380px;
            max-height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            z-index: 1001;
            overflow: hidden;
            display: none;
            animation: slideDown 0.2s ease;
            max-width: calc(100vw - 16px);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .notification-header {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
        }
        
        .notification-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .mark-all-read {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        
        .mark-all-read:hover {
            background: #e3f2fd;
        }
        
        .notification-list {
            max-height: 380px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }
        
        .notification-item:hover {
            background: #f8f9fa;
        }
        
        .notification-item.unread {
            background: #e3f2fd;
        }
        
        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #007bff;
        }
        
        .notification-item .title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 14px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .notification-item .title i {
            font-size: 14px;
        }
        
        .notification-item .message {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        
        .notification-item .time {
            font-size: 11px;
            color: #adb5bd;
        }
        
        .notification-footer {
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            border-top: 1px solid #e5e7eb;
            background: #f8f9fa;
        }
        
        .notification-footer .notification-view-all {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 999px;
            background: #0ea5a7;
            color: white;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid transparent;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .notification-footer .notification-view-all:hover {
            background: #0b8e88;
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(14, 128, 121, 0.14);
        }
        
        .notification-footer .notification-view-all:focus-visible {
            outline: 2px solid rgba(14, 128, 121, 0.35);
            outline-offset: 2px;
        }
        
        .notification-loading {
            text-align: center;
            padding: 30px;
            color: #adb5bd;
        }
        
        .notification-empty {
            text-align: center;
            padding: 30px;
            color: #adb5bd;
        }
        
        .notification-empty i {
            font-size: 40px;
            margin-bottom: 10px;
            opacity: 0.5;
        }
        
        .delete-notification {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #adb5bd;
            cursor: pointer;
            font-size: 14px;
            padding: 4px 6px;
            border-radius: 4px;
            display: none;
        }
        
        .notification-item:hover .delete-notification {
            display: block;
        }
        
        .delete-notification:hover {
            color: #dc3545;
            background: #fff;
        }
        
        @media (max-width: 576px) {
            .notification-panel {
                width: 320px;
                right: -60px;
            }
        }
    </style>
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
    window.userId = <?= $_SESSION['user']['id'] ?? 0 ?>;
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/notification.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/chat-popup.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/user/js/home.js"></script>
<!-- ... các script khác ... -->
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

<script>
    // Khởi tạo notification system
    if (typeof initNotificationSystem === 'function') {
        initNotificationSystem();
    }
</script>

</body>
</html>