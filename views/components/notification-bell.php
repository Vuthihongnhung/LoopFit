<?php if (isset($_SESSION['user'])): ?>
<div class="notification-wrapper" style="position:relative; display:inline-block;">
    <button id="notificationBell" style="background:none; border:none; font-size:24px; cursor:pointer;">🔔</button>
    <span id="notificationBadge" style="position:absolute; top:-5px; right:-10px; background:red; color:white; border-radius:50%; padding:2px 6px; font-size:12px; display:none;">0</span>
    <div id="notificationPanel" style="display:none; position:absolute; right:0; top:35px; width:320px; background:white; border:1px solid #ccc; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); z-index:1000;">
        <div style="padding:8px 12px; border-bottom:1px solid #eee; font-weight:bold;">Thông báo</div>
        <div id="notificationList" style="max-height:300px; overflow-y:auto;"></div>
        <div style="padding:8px; text-align:center; border-top:1px solid #eee;">
            <a href="<?= BASE_URL ?>/notifications" style="text-decoration:none;">Xem tất cả</a>
        </div>
    </div>
</div>
<?php endif; ?>