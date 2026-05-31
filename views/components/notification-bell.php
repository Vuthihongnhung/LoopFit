<?php if (isset($_SESSION['user'])): ?>
<div class="notification-wrapper" style="position:relative; display:inline-flex; align-items:center; z-index:1002;">
    <button id="notificationBell" class="notification-bell notification-button" aria-haspopup="true" aria-expanded="false" type="button">
        <i class="bi bi-bell"></i>
        <span id="notificationBadge" class="notification-badge" style="position:absolute; top:-6px; right:-8px; background:#dc3545; color:white; border-radius:50%; padding:2px 6px; font-size:11px; display:none;">0</span>
    </button>

    <div id="notificationPanel" class="notification-panel" role="dialog" aria-label="Thông báo" style="display:none; position:fixed; width:360px; max-height:60vh; overflow:hidden; background:white; border:1px solid rgba(0,0,0,0.08); border-radius:10px; box-shadow:0 8px 30px rgba(15,23,42,0.15); z-index:10005;">
        <div class="notification-header" style="padding:12px 14px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; gap:12px;">
            <div>
                <strong style="font-size:15px;">Thông báo</strong>
                <div id="notificationCountText" style="font-size:0.85rem; color:#555; margin-top:4px;">Đang tải...</div>
            </div>
            <button id="markAllNotificationsRead" title="Đánh dấu đã đọc tất cả" style="border:none; background:#0ea5a7; color:white; padding:6px 10px; border-radius:6px; font-size:0.85rem; cursor:pointer;">Đã đọc</button>
        </div>
        <div id="notificationList" class="notification-list" style="max-height:calc(60vh - 110px); overflow-y:auto; padding:8px;"></div>
        <div class="notification-footer" style="padding:10px 12px; border-top:1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <a href="<?= BASE_URL ?>/notifications" class="notification-view-all">Xem tất cả</a>
            <span id="notificationPanelLoading" style="font-size:0.85rem; color:#888; display:none;">⏳</span>
        </div>
    </div>
</div>
<?php endif; ?>