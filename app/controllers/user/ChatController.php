<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\ChatMessage;
use App\Models\FAQ;

class ChatController extends Controller
{
    private $chatModel;
    private $faqModel;
    
    public function __construct()
    {
        $this->chatModel = new ChatMessage();
        $this->faqModel = new FAQ();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
    
    private function getSessionId()
    {
        // Nếu đã đăng nhập
        if (isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
            $_SESSION['chat_session_id'] = 'user_' . $userId;
            return $_SESSION['chat_session_id'];
        }
        
        // Khách chưa đăng nhập
        if (!isset($_SESSION['chat_session_id'])) {
            $_SESSION['chat_session_id'] = 'guest_' . session_id();
        }
        return $_SESSION['chat_session_id'];
    }
    
    public function index()
    {
        $sessionId = $this->getSessionId();
        $userInfo = $this->getUserInfo();
        $faqs = $this->faqModel->getAllActive();
        
        // Lấy lịch sử tin nhắn
        $messages = $this->chatModel->getMessagesBySession($sessionId, $lastId);
        
        $this->view('user/chat', [
            'sessionId' => $sessionId,
            'userInfo' => $userInfo,
            'faqs' => $faqs,
            'messages' => $messages
        ]);
    }
    
    public function sendMessage()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        $message = trim($_POST['message'] ?? '');
        $sessionId = $_POST['session_id'] ?? null; // NHẬN SESSION_ID TỪ REQUEST
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Vui lòng nhập tin nhắn']);
            return;
        }
        
        // Nếu không có session_id từ request, tự lấy
        if (!$sessionId) {
            $sessionId = $this->getSessionId();
        }
        
        $userId = $_SESSION['user']['id'] ?? null;
        
        $result = $this->chatModel->sendMessage($userId, $sessionId, 'user', $message);
        
        echo json_encode([
            'success' => $result,
            'message' => [
                'id' => $result ? $this->chatModel->getInsertId() : 0,
                'message' => $message,
                'sender_type' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
    public function getMessages()
{
    header('Content-Type: application/json');
    
    $sessionId = $this->getSessionId();
    $lastId = intval($_GET['last_id'] ?? 0);
    
    // Nếu user đã đăng nhập, lấy theo user_id, ngược lại theo session_id
    if (isset($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $messages = $this->chatModel->getMessagesByUser($userId, $lastId);
    } else {
        $messages = $this->chatModel->getMessagesBySession($sessionId, $lastId);
    }
    
    // Đánh dấu tin nhắn admin đã đọc
    $adminMessageIds = [];
    foreach ($messages as $msg) {
        if ($msg['sender_type'] === 'admin' && !$msg['is_read']) {
            $adminMessageIds[] = $msg['id'];
        }
    }
    if (!empty($adminMessageIds)) {
        $this->chatModel->markAsRead($adminMessageIds);
    }
    
    // Lấy last_id mới nhất
    $newLastId = !empty($messages) ? end($messages)['id'] : $lastId;
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'last_id' => $newLastId
    ]);
}
    
    public function searchFaq()
    {
        header('Content-Type: application/json');
        
        $keyword = trim($_GET['keyword'] ?? '');
        
        // Nếu không có keyword hoặc keyword quá ngắn, lấy tất cả FAQ
        if (strlen($keyword) < 2) {
            $results = $this->faqModel->getAllActive();
            echo json_encode($results);
            return;
        }
        
        $results = $this->faqModel->searchByKeyword($keyword);
        echo json_encode($results);
    }

    public function getUserInfo()
    {
        header('Content-Type: application/json');
        
        if (isset($_SESSION['user']['id'])) {
            echo json_encode([
                'is_logged_in' => true,
                'name' => $_SESSION['user']['name'] ?? 'Khách hàng',
                'email' => $_SESSION['user']['email'] ?? ''
            ]);
        } else {
            echo json_encode([
                'is_logged_in' => false,
                'name' => 'Khách'
            ]);
        }
    }

        public function getSession()
    {
        header('Content-Type: application/json');
        echo json_encode(['session_id' => $this->getSessionId()]);
    }
    
    public function getUnreadCount()
    {
        header('Content-Type: application/json');
        
        $sessionId = $this->getSessionId();
        $unread = $this->chatModel->getUnreadMessages($sessionId);
        $unreadCount = count(array_filter($unread, function($msg) {
            return $msg['sender_type'] === 'admin' && !$msg['is_read'];
        }));
        
        echo json_encode(['unread_count' => $unreadCount]);
    }

        public function botReply()
    {
        header('Content-Type: application/json');
        
        $question = trim($_POST['question'] ?? '');
        if (empty($question)) {
            echo json_encode(['success' => false]);
            return;
        }
        
        // Lấy session_id từ request (nếu có) hoặc từ session hiện tại
        $sessionId = $_POST['session_id'] ?? null;
        if (!$sessionId) {
            $sessionId = $this->getSessionId();
        }
        
        $faqs = $this->faqModel->searchByKeyword($question);
        $answer = !empty($faqs) ? $faqs[0]['answer'] : 'Admin sẽ phản hồi câu hỏi của bạn sớm!';
        
        // Gửi tin nhắn từ bot
        $userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        $result = $this->chatModel->sendMessage($userId, $sessionId, 'admin', $answer);
        
        echo json_encode(['success' => $result]);
    }

    public function getStickers()
    {
        header('Content-Type: application/json');
        
        $stickers = [
            ['code' => '😊', 'name' => 'Vui'],
            ['code' => '😢', 'name' => 'Buồn'],
            ['code' => '❤️', 'name' => 'Tim'],
            ['code' => '😂', 'name' => 'Cười lớn'],
            ['code' => '👍', 'name' => 'Like'],
            ['code' => '💕', 'name' => 'Thả tim'],
            ['code' => '🎉', 'name' => 'Chúc mừng'],
            ['code' => '🙏', 'name' => 'Cảm ơn']
        ];
        
        echo json_encode($stickers);
    }
}