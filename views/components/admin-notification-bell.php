<?php if (isset($_SESSION['admin_id']) || isset($_SESSION['user_id'])): ?>
<div class="admin-notification-wrapper" style="position:relative; display:inline-block; margin-right:15px;">
    <button id="adminNotificationBell" style="background:none; border:none; font-size:20px; cursor:pointer; color:#fff;">
        <i class="fas fa-bell"></i>
    </button>
    <span id="adminNotificationBadge" style="position:absolute; top:-5px; right:-8px; background:red; color:white; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; display:none;">0</span>
    <div id="adminNotificationPanel" style="display:none; position:absolute; right:0; top:35px; width:320px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.15); z-index:1000; color:#333;">
        <div style="padding:10px; border-bottom:1px solid #eee; font-weight:bold; display:flex; justify-content:space-between;">
            <span>Thông báo hệ thống</span>
            <button id="adminMarkAllReadBtn" style="background:none; border:none; color:#007bff; cursor:pointer;">Đánh dấu đã đọc</button>
        </div>
        <div id="adminNotificationList" style="max-height:350px; overflow-y:auto;"></div>
        <div style="padding:8px; text-align:center; border-top:1px solid #eee;">
            <a href="<?= BASE_URL ?>/admin/notifications" style="color:#007bff; text-decoration:none;">Xem tất cả</a>
        </div>
    </div>
</div>
<?php endif; ?>