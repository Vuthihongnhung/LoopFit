<div class="container notification-page" style="margin-top: 100px; margin-bottom: 50px;">
    <h2 class="notification-page-title">📬 Tất cả thông báo</h2>
    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">Bạn chưa có thông báo nào.</div>
    <?php else: ?>
        <div id="notificationPageList" class="list-group notification-page-list">
            <?php foreach ($notifications as $noti): ?>
                <div class="notification-item-page list-group-item <?= $noti['is_read'] ? '' : 'notification-unread' ?>" data-id="<?= $noti['id'] ?>" data-link="<?= htmlspecialchars($noti['link_url'] ?? '') ?>">
                    <div class="notification-item-header">
                        <div class="notification-item-main">
                            <div class="notification-item-title">
                                <?= htmlspecialchars($noti['title']) ?>
                                <?php if (!$noti['is_read']): ?>
                                    <span class="notification-item-badge">Mới</span>
                                <?php endif; ?>
                            </div>
                            <div class="notification-item-message"><?= nl2br(htmlspecialchars($noti['message'])) ?></div>
                        </div>
                        <small class="notification-item-date">
                            <?= date('d/m/Y H:i', strtotime($noti['created_at'])) ?>
                        </small>
                    </div>
                    <div class="notification-item-actions">
                        <?php if (!$noti['is_read']): ?>
                            <button class="notification-btn notification-btn-primary mark-read-page" data-id="<?= $noti['id'] ?>">Đánh dấu đã đọc</button>
                        <?php endif; ?>
                        <button class="notification-btn notification-btn-danger delete-page" data-id="<?= $noti['id'] ?>">Xóa</button>
                        <?php if (!empty($noti['link_url'])): ?>
                            <a href="<?= htmlspecialchars($noti['link_url']) ?>" class="notification-btn notification-btn-secondary">Xem chi tiết</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>