(function() {
    if (window._notifFixed) return;
    window._notifFixed = true;

    function loadNoti() {
        const container = document.getElementById('notificationList');
        if (!container) return;
        container.innerHTML = '<div style="padding:10px;">⏳ Đang tải...</div>';
        fetch(window.BASE_URL + '/api_notifications.php')
            .then(r => r.json())
            .then(data => {
                console.log('Dữ liệu:', data);
                if (!data.notifications || data.notifications.length === 0) {
                    container.innerHTML = '<div style="padding:10px;">📭 Không có thông báo</div>';
                    return;
                }
                let html = '';
                data.notifications.forEach(n => {
                    html += `<div style="padding:8px; border-bottom:1px solid #eee; cursor:pointer;" data-id="${n.id}">
                                <div><strong>${escapeHtml(n.title)}</strong></div>
                                <div>${escapeHtml(n.message)}</div>
                                <div style="font-size:10px;">${new Date(n.created_at).toLocaleString()}</div>
                            </div>`;
                });
                container.innerHTML = html;
            })
            .catch(e => {
                console.error(e);
                container.innerHTML = '<div style="padding:10px;">⚠️ Lỗi tải</div>';
            });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const bell = document.getElementById('notificationBell');
        const panel = document.getElementById('notificationPanel');
        if (!bell || !panel) return;
        bell.onclick = (e) => {
            e.stopPropagation();
            panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
            if (panel.style.display === 'block') loadNoti();
        };
        document.onclick = () => panel.style.display = 'none';
    });
})();