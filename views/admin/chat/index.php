<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>
<style>
.main-content {
    margin-top: -660px !important;
    padding-top: -160px !important;
}
.content-wrapper {
    padding: 0 !important;
}
</style>
<div class="chat-admin-container">
    <!-- Session List -->
    <div class="session-list">
        <div class="session-header">
            <h3><i class="fas fa-comments"></i> Cuộc trò chuyện</h3>
            <p><?= count($sessions) ?> cuộc đang hoạt động</p>
        </div>
        <div class="session-items" id="sessionList">
            <?php foreach ($sessions as $session): ?>
            <div class="session-item" data-session="<?= htmlspecialchars($session['session_id']) ?>">
                <div class="session-info" style="display: flex; justify-content: space-between; align-items: center;">
    <span class="session-id">
        <?php if (!empty($session['user_name'])): ?>
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($session['user_name']) ?>
        <?php else: ?>
            <i class="fas fa-user-secret"></i> Khách (<?= substr($session['session_id'], 0, 15) ?>...)
        <?php endif; ?>
    </span>
    <div style="display: flex; align-items: center; gap: 8px;">
        <?php if ($session['unread_count'] > 0): ?>
        <span class="unread-badge"><?= $session['unread_count'] ?></span>
        <?php endif; ?>
        <button class="delete-session-btn" data-session="<?= htmlspecialchars($session['session_id']) ?>" style="background: none; border: none; color: #dc3545; cursor: pointer;">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>
</div>
<?php if (!empty($session['user_email'])): ?>
<div class="session-email" style="font-size: 10px; color: #6c757d; margin-top: 4px;">
    <i class="fas fa-envelope"></i> <?= htmlspecialchars($session['user_email']) ?>
</div>
<?php endif; ?>
                <div class="session-time"><?= date('d/m/Y H:i', strtotime($session['last_message'])) ?></div>
                <div class="session-preview">Tin nhắn: <?= $session['total_messages'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="chat-area">
        <div class="chat-area-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h4 id="chatTitle">LoopFit Support</h4>
        <p id="chatSubtitle">Chọn một cuộc trò chuyện để bắt đầu</p>
    </div>
    <button id="resetChatBtn" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
        <i class="fas fa-trash-alt"></i> Xóa chat
    </button>
</div>
        
        <div class="chat-messages-admin" id="chatMessages">
            <div class="empty-chat">
                <i class="fas fa-inbox"></i>
                <p>Chọn cuộc trò chuyện từ danh sách bên trái</p>
            </div>
        </div>
        
        <div class="chat-input-area-admin" style="position: relative;">
    <button class="sticker-btn" id="stickerBtn" style="background: none; border: none; font-size: 24px; cursor: pointer;">😊</button>
    <textarea class="chat-input-admin" id="adminMessage" rows="1" placeholder="Nhập phản hồi..." disabled style="flex: 1; margin-left: 10px;"></textarea>
    <button class="send-btn-admin" id="adminSendBtn" disabled>
        <i class="fas fa-paper-plane"></i>
    </button>
    <div class="sticker-panel" id="stickerPanel" style="position: absolute; bottom: 60px; left: 0; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); padding: 12px; width: 280px; display: none; grid-template-columns: repeat(4, 1fr); gap: 8px; z-index: 1000;"></div>
</div>
    </div>
    <script src="<?= BASE_URL ?>/assets/admin/js/admin-chat.js"></script>
<script>
    console.log('BASE_URL from PHP:', '<?= BASE_URL ?>');
</script>
</div>

