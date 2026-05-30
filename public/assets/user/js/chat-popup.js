// Chat Popup JavaScript
let lastMessageId = 0;
let pollingInterval = null;
let sessionId = null;

$(document).ready(function() {
    initSession();
    initEventListeners();
    startPolling();
    loadFAQs(); 
    loadUserInfo();
     setInterval(function() {
        if (document.body.contains(document.getElementById('chatPopup'))) {
            $.get(BASE_URL + '/chat/get-session', function(response) {
                if (response.session_id && response.session_id !== sessionId) {
                    sessionId = response.session_id;
                    console.log('Session updated:', sessionId);
                    loadMessages();
                }
            });
        }
    }, 3000);
    // === STICKER ===
function loadStickers() {
    $.get(BASE_URL + '/chat/get-stickers', function(stickers) {
        let html = '';
        stickers.forEach(s => {
            html += `<div class="sticker-item" data-sticker="${s.code}">${s.code}</div>`;
        });
        $('#stickerPanel').html(html);
        
        $('.sticker-item').click(function() {
            const sticker = $(this).data('sticker');
            $('#chatPopupInput').val(sticker);
            sendMessage();
            $('#stickerPanel').removeClass('show');
        });
    });
}

$('#stickerBtn').click(function(e) {
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
        url: BASE_URL + '/chat/get-session',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            sessionId = response.session_id;
            loadMessages();
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
            loadFAQs(); // Load FAQ khi mở chat
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
        url: BASE_URL + '/chat/send',
        method: 'POST',
        data: { 
            message: message,
            session_id: sessionId  // THÊM DÒNG NÀY
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
        error: function() {
            showToast('Lỗi kết nối');
        },
        complete: function() {
            $('#chatPopupSend').prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
        }
    });
}

function loadMessages() {
    $.ajax({
        url: BASE_URL + '/chat/get-messages',
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
        const time = new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        
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
        url: BASE_URL + '/chat/search-faq',
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
                
                // Xóa section cũ nếu có
                $('#chatPopupMessages .chat-faq-section').remove();
                // Chèn vào sau chat-welcome
                if ($('#chatPopupMessages .chat-welcome').length) {
                    $('#chatPopupMessages .chat-welcome').after(faqHtml);
                } else {
                    $('#chatPopupMessages').prepend(faqHtml);
                }
                
                // Gắn sự kiện click
                $('.chat-faq-item').off('click').on('click', function() {
                const question = $(this).data('question');
                $('#chatPopupInput').val(question);
                sendMessage();
                
                setTimeout(function() {
    $.post(BASE_URL + '/chat/bot-reply', { 
        question: question, 
        session_id: sessionId 
    }, function(response) {
        if (response.success) {
            loadMessages();
        }
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
    container.scrollTop(container[0].scrollHeight);
}

function showToast(message) {
    if (typeof Toast !== 'undefined') {
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
    $.get(BASE_URL + '/chat/get-user', function(user) {
        if (user.is_logged_in) {
            $('#chatUserName').text('Xin chào ' + user.name);
            $('#chatUserStatus').html('<i class="fas fa-circle" style="color: #28a745; font-size: 8px;"></i>');
        } else {
            $('#chatUserName').text('LoopFit Support');
            $('#chatUserStatus').text('Online');
        }
    });
}