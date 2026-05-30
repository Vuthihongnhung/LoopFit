// Chat Popup JavaScript
let lastMessageId = 0;
let pollingInterval = null;
let sessionId = null;

$(document).ready(function() {
    // Kiểm tra BASE_URL đã được định nghĩa chưa
    if (typeof BASE_URL === 'undefined') {
        console.error('BASE_URL is not defined. Please define BASE_URL in layout.');
        // Fallback: tự động phát hiện
        window.BASE_URL = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '');
    }

    initSession();
    initEventListeners();
    startPolling();
    loadFAQs();
    loadUserInfo();

    // Cập nhật session định kỳ
    setInterval(function() {
        if (document.body.contains(document.getElementById('chatPopup'))) {
            $.get(BASE_URL + '/index.php?url=chat/get-session', function(response) {
                if (response.session_id && response.session_id !== sessionId) {
                    sessionId = response.session_id;
                    console.log('Session updated:', sessionId);
                    loadMessages();
                }
            }).fail(function() {
                console.log('Failed to get session');
            });
        }
    }, 3000);

    // === STICKER ===
    function loadStickers() {
        $.get(BASE_URL + '/index.php?url=chat/get-stickers', function(stickers) {
            let html = '';
            stickers.forEach(function(s) {
                html += '<div class="sticker-item" data-sticker="' + escapeHtml(s.code) + '">' + escapeHtml(s.code) + '</div>';
            });
            $('#stickerPanel').html(html);

            $('.sticker-item').off('click').on('click', function() {
                const sticker = $(this).data('sticker');
                $('#chatPopupInput').val(sticker);
                sendMessage();
                $('#stickerPanel').removeClass('show');
            });
        }).fail(function() {
            console.log('Không thể tải sticker');
        });
    }

    // Gắn sự kiện cho sticker button (đảm bảo tồn tại)
    $(document).on('click', '#stickerBtn', function(e) {
        e.stopPropagation();
        $('#stickerPanel').toggleClass('show');
        loadStickers();
    });

    $(document).click(function() {
        $('#stickerPanel').removeClass('show');
    });

    $('#stickerPanel').click(function(e) {
        e.stopPropagation();
    });
});

function initSession() {
    $.ajax({
        url: BASE_URL + '/index.php?url=chat/get-session',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            sessionId = response.session_id;
            loadMessages();
        },
        error: function() {
            console.log('Init session failed');
            setTimeout(initSession, 2000);
        }
    });
}

function initEventListeners() {
    $('#chatToggleBtn').click(function() {
        $('#chatPopupBox').toggle();
        if ($('#chatPopupBox').is(':visible')) {
            loadMessages();
            scrollToBottom();
            resetNotification();
            loadFAQs();
        }
    });

    $('#chatCloseBtn').click(function() {
        $('#chatPopupBox').hide();
    });

    $('#chatPopupSend').click(sendMessage);

    $('#chatPopupInput').keypress(function(e) {
        if (e.which == 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
}

function sendMessage() {
    const message = $('#chatPopupInput').val().trim();
    if (!message) return;

    $('#chatPopupSend').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: BASE_URL + '/index.php?url=chat/send',
        method: 'POST',
        data: {
            message: message,
            session_id: sessionId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#chatPopupInput').val('');
                $('#chatPopupInput').css('height', 'auto');
                loadMessages();
            } else {
                showToast(response.error || 'Gửi thất bại');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Lỗi kết nối';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            showToast(errorMsg);
        },
        complete: function() {
            $('#chatPopupSend').prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
        }
    });
}

function loadMessages() {
    $.ajax({
        url: BASE_URL + '/index.php?url=chat/get-messages',
        method: 'GET',
        data: { last_id: lastMessageId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.messages && response.messages.length > 0) {
                displayMessages(response.messages);
                lastMessageId = response.last_id;
                scrollToBottom();

                if (!$('#chatPopupBox').is(':visible')) {
                    updateNotification(response.messages);
                }
            }
        },
        error: function() {
            console.log('Failed to load messages');
        }
    });
}

function displayMessages(messages) {
    const container = $('#chatPopupMessages');
    const welcomeDiv = container.find('.chat-welcome');
    if (welcomeDiv.length && messages.length > 0) {
        welcomeDiv.remove();
    }

    messages.forEach(function(msg) {
        const isUser = msg.sender_type === 'user';
        let time = '';
        if (msg.created_at) {
            const date = new Date(msg.created_at);
            if (!isNaN(date.getTime())) {
                time = date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            }
        }
        const messageHtml = `
            <div class="message-item ${isUser ? 'user' : 'admin'}">
                <div class="message-bubble">
                    <div class="message-text">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
        container.append(messageHtml);
    });
}

function loadFAQs() {
    $.ajax({
        url: BASE_URL + '/index.php?url=chat/search-faq',
        method: 'GET',
        data: { keyword: '' },
        dataType: 'json',
        success: function(faqs) {
            if (faqs && faqs.length > 0) {
                let faqHtml = '<div class="chat-faq-section"><div class="chat-faq-title"><i class="fas fa-question-circle"></i> Câu hỏi thường gặp</div><div class="chat-faq-list">';
                faqs.forEach(function(faq) {
                    faqHtml += `<div class="chat-faq-item" data-question="${escapeHtml(faq.question)}">❓ ${escapeHtml(faq.question)}</div>`;
                });
                faqHtml += '</div></div>';

                $('#chatPopupMessages .chat-faq-section').remove();
                if ($('#chatPopupMessages .chat-welcome').length) {
                    $('#chatPopupMessages .chat-welcome').after(faqHtml);
                } else {
                    $('#chatPopupMessages').prepend(faqHtml);
                }

                $('.chat-faq-item').off('click').on('click', function() {
                    const question = $(this).data('question');
                    $('#chatPopupInput').val(question);
                    sendMessage();

                    setTimeout(function() {
                        $.post(BASE_URL + '/index.php?url=chat/bot-reply', {
                            question: question,
                            session_id: sessionId
                        }, function(response) {
                            if (response.success) {
                                loadMessages();
                            }
                        }).fail(function() {
                            console.log('Bot reply failed');
                        });
                    }, 1000);
                });
            }
        },
        error: function() {
            console.log('Không thể tải FAQ');
        }
    });
}

function updateNotification(messages) {
    let adminMessageCount = 0;
    messages.forEach(function(msg) {
        if (msg.sender_type === 'admin') {
            adminMessageCount++;
        }
    });

    if (adminMessageCount > 0) {
        const badge = $('#chatNotification');
        const currentCount = parseInt(badge.text()) || 0;
        badge.text(currentCount + adminMessageCount);
        badge.removeClass('hide');
    }
}

function resetNotification() {
    $('#chatNotification').text('0').addClass('hide');
}

function startPolling() {
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(loadMessages, 3000);
}

function scrollToBottom() {
    const container = $('#chatPopupMessages');
    if (container.length) {
        container.scrollTop(container[0].scrollHeight);
    }
}

function showToast(message) {
    if (typeof Toast !== 'undefined' && Toast.error) {
        Toast.error(message);
    } else {
        alert(message);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function loadUserInfo() {
    $.get(BASE_URL + '/index.php?url=chat/get-user', function(user) {
        if (user.is_logged_in) {
            $('#chatUserName').text('Xin chào ' + user.name);
            $('#chatUserStatus').html('<i class="fas fa-circle" style="color: #28a745; font-size: 8px;"></i>');
        } else {
            $('#chatUserName').text('LoopFit Support');
            $('#chatUserStatus').text('Online');
        }
    }).fail(function() {
        console.log('Failed to load user info');
    });
}