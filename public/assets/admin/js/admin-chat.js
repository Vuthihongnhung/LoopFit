// public/assets/admin/js/admin-chat.js
(function() {
    'use strict';
    
    let currentSession = null;
    let pollingInterval = null;
    let lastMessageId = 0;

    document.addEventListener('DOMContentLoaded', function() {
        
        console.log('Admin chat loaded, BASE_URL:', window.BASE_URL);
        
        const sessionItems = document.querySelectorAll('.session-item');
        sessionItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                sessionItems.forEach(function(si) {
                    si.classList.remove('active');
                });
                this.classList.add('active');
                
                currentSession = this.dataset.session;
                window.currentSession = currentSession;
                const userName = this.dataset.userName && this.dataset.userName.trim() ? this.dataset.userName.trim() : '';
                const userId = this.dataset.userId && this.dataset.userId.trim() ? this.dataset.userId.trim() : '';
                const titleText = userName ? 'Chat với: ' + userName : (userId ? 'Chat với: user_' + userId : 'Chat với: ' + currentSession.substring(0, 30) + '...');
                document.getElementById('chatTitle').textContent = titleText;
                document.getElementById('chatSubtitle').textContent = 'Đang trò chuyện';
                
                document.getElementById('adminMessage').disabled = false;
                document.getElementById('adminSendBtn').disabled = false;
                
                loadSessionMessages();
                startPollingForSession();
            });
        });
        
        const sendBtn = document.getElementById('adminSendBtn');
        if (sendBtn) {
            sendBtn.addEventListener('click', sendAdminMessage);
        }
        
        const messageInput = document.getElementById('adminMessage');
        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendAdminMessage();
                }
            });
        }

        const openSessionFromUrl = () => {
            const params = new URLSearchParams(window.location.search);
            const sessionId = params.get('session_id');
            if (!sessionId) return;
            const target = Array.from(sessionItems).find(item => item.dataset.session === sessionId);
            if (target) {
                target.click();
            }
        };

        openSessionFromUrl();
        
        // Xử lý nút reset chat
        const resetBtn = document.getElementById('resetChatBtn');
        if (resetBtn) {
            resetBtn.onclick = function() {
                if (!currentSession) {
                    alert('Chưa chọn cuộc trò chuyện');
                    return;
                }
                
                if (confirm('Bạn có chắc muốn xóa toàn bộ tin nhắn trong cuộc trò chuyện này?')) {
                    fetch(window.BASE_URL + '/index.php?url=admin/chat/reset', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'session_id=' + encodeURIComponent(window.currentSession)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const container = document.getElementById('chatMessages');
                            if (container) {
                                container.innerHTML = '<div class="empty-chat"><i class="fas fa-inbox"></i><p>Đã xóa toàn bộ tin nhắn</p></div>';
                            }
                            loadSessionMessages();
                        } else {
                            alert('Xóa thất bại');
                        }
                    })
                    .catch(error => {
                        console.error('Error resetting chat:', error);
                        alert('Lỗi kết nối');
                    });
                }
            };
        }
        
        // Xóa cuộc trò chuyện
        document.querySelectorAll('.delete-session-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const sessionId = this.dataset.session;
                
                if (confirm('Bạn có chắc muốn xóa toàn bộ cuộc trò chuyện này?')) {
                    fetch(window.BASE_URL + '/index.php?url=admin/chat/delete-session', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'session_id=' + encodeURIComponent(sessionId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Xóa thất bại');
                        }
                    });
                }
            });
        });
        
        // ===== STICKER =====
        function loadStickers() {
            fetch(window.BASE_URL + '/chat/get-stickers')
                .then(res => res.json())
                .then(stickers => {
                    let html = '';
                    stickers.forEach(s => {
                        html += `<div class="sticker-item" data-sticker="${s.code}">${s.code}</div>`;
                    });
                    const panel = document.getElementById('stickerPanel');
                    if (panel) panel.innerHTML = html;
                    document.querySelectorAll('.sticker-item').forEach(item => {
                        item.addEventListener('click', function() {
                            document.getElementById('adminMessage').value = this.dataset.sticker;
                            sendAdminMessage();
                            const panel = document.getElementById('stickerPanel');
                            if (panel) panel.classList.remove('show');
                        });
                    });
                });
        }
        
        setTimeout(function() {
            const stickerBtn = document.getElementById('stickerBtn');
            if (stickerBtn) {
                stickerBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const panel = document.getElementById('stickerPanel');
                    if (panel) {
                        panel.classList.toggle('show');
                        loadStickers();
                    }
                });
            }
        }, 500);
        
        document.addEventListener('click', function() {
            const panel = document.getElementById('stickerPanel');
            if (panel) panel.classList.remove('show');
        });
    });

    function loadSessionMessages() {
        if (!currentSession) return;
        
        fetch(window.BASE_URL + '/index.php?url=admin/chat/session-messages&session_id=' + encodeURIComponent(currentSession), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAdminMessages(data.messages);
                if (data.messages.length > 0) {
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
            }
        })
        .catch(error => console.error('Error loading messages:', error));
    }

    function displayAdminMessages(messages) {
        const container = document.getElementById('chatMessages');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (messages.length === 0) {
            container.innerHTML = '<div class="empty-chat"><i class="fas fa-inbox"></i><p>Chưa có tin nhắn</p></div>';
            return;
        }
        
        messages.forEach(function(msg) {
            const isAdmin = msg.sender_type === 'admin';
            const time = new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-item ' + (isAdmin ? 'admin' : 'user');
            messageDiv.innerHTML = `
                <div class="message-bubble">
                    <div class="message-text">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
            container.appendChild(messageDiv);
        });
        
        scrollToBottomAdmin();
    }

    function sendAdminMessage() {
        const messageInput = document.getElementById('adminMessage');
        const message = messageInput.value.trim();
        
        if (!message || !currentSession) return;
        
        const sendBtn = document.getElementById('adminSendBtn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const sessionItems = document.querySelectorAll('.session-item');
        let userId = '';
        sessionItems.forEach(function(item) {
            if (item.dataset.session === currentSession) {
                userId = item.dataset.userId || '';
            }
        });

        let body = 'session_id=' + encodeURIComponent(currentSession) + '&message=' + encodeURIComponent(message);
        if (userId) {
            body += '&user_id=' + encodeURIComponent(userId);
        }

        fetch(window.BASE_URL + '/index.php?url=admin/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                messageInput.style.height = 'auto';
                loadSessionMessages();
            } else {
                alert('Gửi tin nhắn thất bại');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Lỗi kết nối');
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
    }

    function startPollingForSession() {
        if (pollingInterval) clearInterval(pollingInterval);
        
        pollingInterval = setInterval(function() {
            if (currentSession) {
                loadSessionMessages();
                updateUnreadCounts();
            }
        }, 3000);
    }

    function updateUnreadCounts() {
        fetch(window.BASE_URL + '/index.php?url=admin/chat/unread-counts', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(counts => {
            document.querySelectorAll('.session-item').forEach(function(item) {
                const sessionId = item.dataset.session;
                const unreadCount = counts[sessionId] || 0;
                
                let badge = item.querySelector('.unread-badge');
                if (unreadCount > 0) {
                    if (badge) {
                        badge.textContent = unreadCount;
                    } else {
                        const sessionInfo = item.querySelector('.session-info');
                        if (sessionInfo) {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'unread-badge';
                            newBadge.textContent = unreadCount;
                            sessionInfo.appendChild(newBadge);
                        }
                    }
                } else if (badge) {
                    badge.remove();
                }
            });
        })
        .catch(error => console.error('Error updating unread counts:', error));
    }

    function scrollToBottomAdmin() {
        const container = document.getElementById('chatMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
})();