(function() {
    if (window.UserNotification) return;

    window.UserNotification = (function() {
        const BASE = window.BASE_URL || '';
        const api = (endpoint) => BASE + '/index.php?url=' + endpoint;

        const getId = (id) => document.getElementById(id);
        const bell = () => getId('notificationBell');
        const badge = () => getId('notificationBadge');
        const panel = () => getId('notificationPanel');
        const list = () => getId('notificationList');
        const countText = () => getId('notificationCountText');
        const markAllBtn = () => getId('markAllNotificationsRead');
        const panelLoading = () => getId('notificationPanelLoading');

        let pollingTimer = null;
        let attached = false;

        const jsonFetch = (url, opts = {}) => fetch(url, opts).then(res => {
            if (!res.ok) throw new Error('Network');
            return res.json();
        });

        const formatDate = (value) => {
            if (!value) return '';
            const date = new Date(value);
            if (isNaN(date.getTime())) return value;
            return date.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
        };

        const updateBadge = (count) => {
            const el = badge();
            if (!el) return;
            if (count > 0) {
                el.textContent = count;
                el.style.display = 'inline-block';
            } else {
                el.style.display = 'none';
            }
        };

        const setCountText = (count) => {
            const el = countText();
            if (!el) return;
            el.textContent = count > 0 ? `Bạn có ${count} thông báo chưa đọc` : 'Không có thông báo mới';
        };

        const fetchUnreadCount = () => {
            return jsonFetch(api('api/notifications/unread-count'))
                .then(data => {
                    const count = Number(data.count || 0);
                    updateBadge(count);
                    setCountText(count);
                    return count;
                })
                .catch(err => {
                    console.warn('notif: unread fetch failed', err);
                    return 0;
                });
        };

        const renderNotifications = (notifications) => {
            const container = list();
            if (!container) return;

            if (!Array.isArray(notifications) || notifications.length === 0) {
                container.innerHTML = '<div style="padding:14px; text-align:center; color:#666;">Bạn chưa có thông báo</div>';
                return;
            }

            container.innerHTML = notifications.map(item => {
                const unread = item.is_read == 0;
                const title = (item.title || 'Thông báo').replace(/</g, '&lt;');
                const message = (item.message || '').replace(/</g, '&lt;').replace(/\n/g, '<br>');
                const link = item.link_url ? encodeURIComponent(item.link_url) : '';
                return `
                    <div class="notification-item" data-id="${item.id}" data-link="${link}" style="padding:12px; border-bottom:1px solid #f3f4f6; cursor:pointer; background:${unread ? '#f0f9ff' : '#fff'};">
                        <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                            <div style="min-width:0;">
                                <div style="font-weight:600; font-size:14px; color:#111;">${title}${unread ? '<span style="color:#0ea5a7; font-size:12px; margin-left:8px;">Mới</span>' : ''}</div>
                                <div style="color:#4b5563; font-size:13px; line-height:1.5; margin-top:6px;">${message}</div>
                            </div>
                            <div style="text-align:right; min-width:80px; font-size:12px; color:#6b7280;">${formatDate(item.created_at)}</div>
                        </div>
                        <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:10px; flex-wrap:wrap;">
                            ${unread ? `<button class="mark-read" data-id="${item.id}" style="border:none;background:#0ea5a7;color:#fff;padding:6px 8px;border-radius:6px;cursor:pointer">Đã đọc</button>` : ''}
                            <button class="delete-notif" data-id="${item.id}" style="border:none;background:#ef4444;color:#fff;padding:6px 8px;border-radius:6px;cursor:pointer">Xóa</button>
                        </div>
                    </div>`;
            }).join('');
        };

        const loadLatestNotifications = () => {
            const loader = panelLoading();
            if (loader) loader.style.display = 'inline';

            return jsonFetch(api('api/notifications/latest'))
                .then(data => {
                    if (loader) loader.style.display = 'none';
                    renderNotifications(data.notifications || []);
                    return data.notifications || [];
                })
                .catch(err => {
                    if (loader) loader.style.display = 'none';
                    console.error('notif: latest load failed', err);
                    const container = list();
                    if (container) container.innerHTML = '<div style="padding:14px; text-align:center; color:#b91c1c;">Không thể tải thông báo</div>';
                    return [];
                });
        };

        const markNotificationRead = (id) => jsonFetch(api('api/notifications/mark-read'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const deleteNotification = (id) => jsonFetch(api('api/notifications/delete'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const markAllRead = () => jsonFetch(api('api/notifications/mark-all-read'), { method: 'POST' });

        const openPanel = () => {
            const p = panel();
            const b = bell();
            if (!p || !b) return;

            p.style.position = 'fixed';
            p.style.right = 'auto';
            p.style.left = '0px';
            p.style.top = '0px';
            p.style.display = 'block';
            p.style.visibility = 'hidden';
            p.style.pointerEvents = 'none';

            const rect = b.getBoundingClientRect();

            const width = p.offsetWidth || 360;
            const height = p.offsetHeight || 320;
            let left = Math.min(Math.max(8, rect.left), document.documentElement.clientWidth - width - 8);
            let top = rect.bottom + 8;
            if (top + height > window.innerHeight - 8) top = rect.top - height - 8;
            if (top < 8) top = 8;

            p.style.left = `${left}px`;
            p.style.top = `${top}px`;
            p.style.visibility = 'visible';
            p.style.pointerEvents = 'auto';
            p.style.display = 'block';

            if (b) b.setAttribute('aria-expanded', 'true');
            loadLatestNotifications();
        };

        const closePanel = () => {
            const p = panel();
            const b = bell();
            if (!p) return;
            p.style.display = 'none';
            if (b) b.setAttribute('aria-expanded', 'false');
        };

        const togglePanel = () => {
            const p = panel();
            if (!p) return;
            if (p.style.display === 'block') closePanel(); else openPanel();
        };

        const attach = () => {
            if (attached) return;
            attached = true;

            const b = bell();
            const p = panel();
            const lst = list();
            const mab = markAllBtn();
            if (!b || !p || !lst) {
                console.warn('notif: missing required notification elements');
                return;
            }

            b.addEventListener('click', (event) => {
                event.stopPropagation();
                togglePanel();
            });

            lst.addEventListener('click', (event) => {
                const mark = event.target.closest('.mark-read');
                if (mark) {
                    event.stopPropagation();
                    const id = Number(mark.dataset.id || 0);
                    if (id) markNotificationRead(id).then(() => { fetchUnreadCount(); loadLatestNotifications(); });
                    return;
                }

                const del = event.target.closest('.delete-notif');
                if (del) {
                    event.stopPropagation();
                    const id = Number(del.dataset.id || 0);
                    if (id) deleteNotification(id).then(() => { fetchUnreadCount(); loadLatestNotifications(); });
                    return;
                }

                const item = event.target.closest('.notification-item');
                if (item) {
                    const link = item.dataset.link ? decodeURIComponent(item.dataset.link) : '';
                    const id = Number(item.dataset.id || 0);
                    if (id) markNotificationRead(id).finally(() => fetchUnreadCount());
                    if (link) setTimeout(() => { window.location.href = link; }, 100);
                }
            });

            if (mab) {
                mab.addEventListener('click', (event) => {
                    event.stopPropagation();
                    markAllRead().then(() => { fetchUnreadCount(); loadLatestNotifications(); });
                });
            }

            document.addEventListener('click', (event) => {
                if (!p.contains(event.target) && !b.contains(event.target)) {
                    closePanel();
                }
            });

            fetchUnreadCount();
            pollingTimer = setInterval(fetchUnreadCount, 15000);
        };

        return { attach };
    })();

    window.initNotificationSystem = function() {
        if (window.UserNotification && typeof window.UserNotification.attach === 'function') {
            window.UserNotification.attach();
        }
    };

    const start = () => {
        if (window.UserNotification && typeof window.UserNotification.attach === 'function') {
            window.UserNotification.attach();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();