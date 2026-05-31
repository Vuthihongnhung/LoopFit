<div class="page-header">
    <h1>Thông báo quản trị viên</h1>
    <p>Quản lý thông báo hệ thống, đánh dấu đã đọc hoặc xóa khi cần.</p>
</div>

<div class="card" style="margin-bottom: 30px;">
    <div class="card-header" style="background: linear-gradient(135deg, #4a67c5, #2563eb); color: white;">
        <h3 style="color: white; margin: 0; font-size: 1.05rem;"><i class="fas fa-bell"></i> Danh sách thông báo</h3>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <strong>Có tổng cộng <?= count($notifications) ?> thông báo</strong>
            </div>
            <button id="markAllNotificationsRead" style="border:none; background:#10b981; color:white; padding:10px 14px; border-radius:8px; cursor:pointer;">Đánh dấu tất cả đã đọc</button>
        </div>
        <?php if (empty($notifications)): ?>
            <div style="padding: 20px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; text-align: center;">📭 Hiện không có thông báo</div>
        <?php else: ?>
            <div style="display: grid; gap: 14px;">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item" data-id="<?= $notification['id'] ?>" style="padding: 18px; border-radius: 14px; border: 1px solid #e2e8f0; background: <?= $notification['is_read'] ? '#ffffff' : '#eff6ff' ?>; position: relative;">
                        <div style="display:flex; justify-content: space-between; gap: 12px; align-items: flex-start;">
                            <div>
                                <div style="font-weight: 600; margin-bottom: 6px; color: #0f172a;"><i class="fas fa-bell"></i> <?= htmlspecialchars($notification['title']) ?></div>
                                <div style="color:#475569; font-size:0.95rem; line-height:1.6; white-space: pre-line;"><?= nl2br(htmlspecialchars($notification['message'])) ?></div>
                            </div>
                            <div style="text-align:right; min-width: 120px;">
                                <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 8px;"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></div>
                                <?php if (!$notification['is_read']): ?>
                                    <span style="display:inline-block; background:#2563eb; color:white; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem;">Mới</span>
                                <?php else: ?>
                                    <span style="display:inline-block; background:#e2e8f0; color:#334155; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem;">Đã đọc</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap;">
                            <?php if (!$notification['is_read']): ?>
                                <button class="mark-read-btn" data-id="<?= $notification['id'] ?>" style="border:none; background:#2563eb; color:white; padding: 8px 12px; border-radius: 8px; cursor:pointer;">Đánh dấu đã đọc</button>
                            <?php endif; ?>
                            <button class="delete-noti-btn" data-id="<?= $notification['id'] ?>" style="border:none; background:#ef4444; color:white; padding: 8px 12px; border-radius: 8px; cursor:pointer;">Xóa</button>
                            <?php if (!empty($notification['link_url'])): ?>
                                <a href="<?= htmlspecialchars($notification['link_url']) ?>" style="display:inline-block; text-decoration:none; color:#0f172a; padding: 8px 12px; border-radius:8px; border:1px solid #cbd5e1;">Xem chi tiết</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    const apiBase = window.BASE_URL + '/index.php?url=admin/api/notifications';

    function postJson(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json());
    }

    function refreshPage() {
        window.location.reload();
    }

    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            postJson(apiBase + '/mark-read', { id })
                .then(() => refreshPage())
                .catch(() => alert('Không thể đánh dấu đã đọc.'));
        });
    });

    document.querySelectorAll('.delete-noti-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!confirm('Bạn có chắc muốn xóa thông báo này không?')) return;
            const id = btn.dataset.id;
            postJson(apiBase + '/delete', { id })
                .then(() => refreshPage())
                .catch(() => alert('Không thể xóa thông báo.'));
        });
    });

    const markAllBtn = document.getElementById('markAllNotificationsRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            postJson(apiBase + '/mark-all-read', {})
                .then(() => refreshPage())
                .catch(() => alert('Không thể đánh dấu tất cả đã đọc.'));
        });
    }
})();
</script>
