(function() {
    if (window._adminNotifLoaded) return;
    window._adminNotifLoaded = true;

    const BASE = window.BASE_URL || (() => {
        const origin = window.location.origin;
        const path = window.location.pathname.replace(/\/index\.php.*$/, '');
        return origin + path;
    })();

    function apiUrl(endpoint) {
        return BASE + '/index.php?url=' + endpoint;
    }

    function loadNotifications() {
        const list = document.getElementById('adminNotificationList');
        if (!list) {
            console.error('Không tìm thấy #adminNotificationList');
            return;
        }
        list.innerHTML = '⏳ Đang tải...';

        fetch(apiUrl('admin/api/notifications/latest'))
            .then(res => res.json())
            .then(data => {
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '<div style="padding: 14px; color: #475569;">📭 Không có thông báo mới</div>';
                    return;
                }
                let html = '<div style="display:flex; flex-direction:column; gap:10px; padding: 8px;">';
                data.notifications.forEach(n => {
                    const unreadClass = n.is_read ? '' : 'background: #eff6ff; border-color: #bfdbfe;';
                    const readButton = n.is_read ? '' : `<button class="admin-noti-action" data-action="mark-read" data-id="${n.id}" style="border:none; background:#2563eb; color:white; padding:6px 10px; border-radius:8px; cursor:pointer;">Đánh dấu</button>`;
                    html += `<div style="padding:12px; border:1px solid #e2e8f0; border-radius: 14px; ${unreadClass}">
                                <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                    <div style="min-width:0;">
                                        <strong style="display:block; font-size:0.95rem; color:#0f172a;">${escapeHtml(n.title)}</strong>
                                        <div style="font-size:0.87rem; color:#475569; margin-top:4px; white-space:pre-wrap;">${escapeHtml(n.message)}</div>
                                        <div style="margin-top:8px; font-size:0.8rem; color:#64748b;">${timeAgo(n.created_at)}</div>
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                                        ${readButton}
                                        <button class="admin-noti-action" data-action="delete" data-id="${n.id}" style="border:none; background:#ef4444; color:white; padding:6px 10px; border-radius:8px; cursor:pointer;">Xóa</button>
                                    </div>
                                </div>
                            </div>`;
                });
                html += '</div>';
                list.innerHTML = html;
            })
            .catch(e => {
                console.error('Lỗi fetch:', e);
                list.innerHTML = '⚠️ Lỗi tải dữ liệu';
            });
    }

    function updateBadge() {
        fetch(apiUrl('admin/api/notifications/unread-count'))
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('adminNotificationBadge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(e => console.error('Lỗi badge:', e));
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
    }

    function timeAgo(dateStr) {
        const seconds = Math.floor((new Date() - new Date(dateStr)) / 1000);
        if (seconds < 60) return 'Vừa xong';
        if (seconds < 3600) return Math.floor(seconds/60) + ' phút trước';
        if (seconds < 86400) return Math.floor(seconds/3600) + ' giờ trước';
        return Math.floor(seconds/86400) + ' ngày trước';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const bell = document.getElementById('adminNotificationBell');
        const panel = document.getElementById('adminNotificationPanel');
        if (!bell || !panel) {
            console.error('Không tìm thấy bell hoặc panel');
            return;
        }
        
        bell.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
            if (panel.style.display === 'block') {
                loadNotifications();
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!panel.contains(e.target) && e.target !== bell) {
                panel.style.display = 'none';
            }
        });

        panel.addEventListener('click', (e) => {
            const actionBtn = e.target.closest('.admin-noti-action');
            if (!actionBtn) return;
            e.stopPropagation();

            const action = actionBtn.dataset.action;
            const id = actionBtn.dataset.id;
            if (!action || !id) return;

            if (action === 'mark-read') {
                fetch(apiUrl('admin/api/notifications/mark-read'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parseInt(id, 10) })
                }).then(() => {
                    loadNotifications();
                    updateBadge();
                }).catch(err => console.error(err));
            }
            if (action === 'delete') {
                if (!confirm('Bạn có chắc muốn xóa thông báo này không?')) return;
                fetch(apiUrl('admin/api/notifications/delete'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parseInt(id, 10) })
                }).then(() => {
                    loadNotifications();
                    updateBadge();
                }).catch(err => console.error(err));
            }
        });

        const markAllBtn = document.getElementById('adminMarkAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', () => {
                fetch(apiUrl('admin/api/notifications/mark-all-read'), {
                    method: 'POST'
                }).then(() => {
                    loadNotifications();
                    updateBadge();
                }).catch(err => console.error(err));
            });
        }
        
        updateBadge();
        setInterval(updateBadge, 30000);
    });
})();