(function() {
    if (window._adminNotifLoaded) return;
    window._adminNotifLoaded = true;

    const BASE = window.BASE_URL || '';

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
        
        fetch(BASE_URL + '/api_admin_noti.php')
            .then(res => res.json())
            .then(data => {
                console.log('Dữ liệu nhận được:', data);
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '📭 Không có thông báo';
                    return;
                }
                let html = '<ul style="margin:0; padding-left:15px;">';
                data.notifications.forEach(n => {
                    html += `<li style="margin-bottom:8px;">
                                <strong>${escapeHtml(n.title)}</strong><br>
                                <span style="font-size:12px; color:#555;">${escapeHtml(n.message)}</span><br>
                                <span style="font-size:10px; color:#999;">${timeAgo(n.created_at)}</span>
                            </li>`;
                });
                html += '</ul>';
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
        
        updateBadge();
        setInterval(updateBadge, 30000);
    });
})();