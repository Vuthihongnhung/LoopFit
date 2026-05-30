<div class="container" style="margin-top: 100px; margin-bottom: 50px;">
    <h2>📬 Tất cả thông báo</h2>
    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">Bạn chưa có thông báo nào.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $noti): ?>
                <div class="list-group-item <?= $noti['is_read'] ? '' : 'list-group-item-primary' ?>">
                    <div class="d-flex justify-content-between">
                        <strong><?= htmlspecialchars($noti['title']) ?></strong>
                        <small><?= date('d/m/Y H:i', strtotime($noti['created_at'])) ?></small>
                    </div>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($noti['message'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>