<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\ChatMessage;

class AdminChatController extends Controller
{
    private $chatModel;
    
    public function __construct()
    {
        $this->chatModel = new ChatMessage();
        
        if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }
    
    public function index()
{
    $sql = "(SELECT 
                u.id as user_id,
                u.name as user_name,
                u.email as user_email,
                cm.session_id,
                MAX(cm.created_at) as last_message,
                COUNT(cm.id) as total_messages,
                SUM(CASE WHEN cm.is_read = 0 AND cm.sender_type = 'user' THEN 1 ELSE 0 END) as unread_count
            FROM chat_messages cm
            LEFT JOIN users u ON cm.user_id = u.id
            WHERE u.id IS NOT NULL
            GROUP BY u.id)
            
            UNION ALL
            
            (SELECT 
                NULL as user_id,
                'Khách' as user_name,
                NULL as user_email,
                cm.session_id,
                MAX(cm.created_at) as last_message,
                COUNT(cm.id) as total_messages,
                SUM(CASE WHEN cm.is_read = 0 AND cm.sender_type = 'user' THEN 1 ELSE 0 END) as unread_count
            FROM chat_messages cm
            WHERE cm.user_id IS NULL
            GROUP BY cm.session_id)
            
            ORDER BY last_message DESC";
    
    $result = $this->chatModel->getConnection()->query($sql);
    $sessions = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    $this->view('admin/chat/index', [
        'pageTitle' => 'Quản lý Chat',
        'activeMenu' => 'chat',
        'sessions' => $sessions
    ]);
}
    
    public function getSessionMessages()
    {
        header('Content-Type: application/json');
        
        $sessionId = $_GET['session_id'] ?? '';
        if (empty($sessionId)) {
            echo json_encode(['success' => false, 'messages' => []]);
            return;
        }
        
        $messages = $this->chatModel->getMessagesBySession($sessionId);
        
        $userMessageIds = [];
        foreach ($messages as $msg) {
            if ($msg['sender_type'] === 'user' && !$msg['is_read']) {
                $userMessageIds[] = $msg['id'];
            }
        }
        
        if (!empty($userMessageIds)) {
            $this->chatModel->markAsRead($userMessageIds);
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
    }
    
    public function sendMessage()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        $sessionId = $_POST['session_id'] ?? '';
        $message = trim($_POST['message'] ?? '');
        
        if (empty($sessionId) || empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            return;
        }
        
        $adminId = $_SESSION['admin']['id'];
        $result = $this->chatModel->sendMessage(null, $sessionId, 'admin', $message);
        
        echo json_encode([
            'success' => $result,
            'message' => [
                'id' => $result ? $this->chatModel->getInsertId() : 0,
                'message' => $message,
                'sender_type' => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
    public function getUnreadCounts()
    {
        header('Content-Type: application/json');
        
        $sql = "SELECT session_id, COUNT(*) as unread_count
                FROM chat_messages 
                WHERE is_read = 0 AND sender_type = 'user'
                GROUP BY session_id";
        
        $result = $this->chatModel->getConnection()->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['session_id']] = (int)$row['unread_count'];
        }
        
        echo json_encode($counts);
    }
        public function resetChat()
    {
        header('Content-Type: application/json');
        
        $sessionId = $_POST['session_id'] ?? '';
        if (empty($sessionId)) {
            echo json_encode(['success' => false]);
            return;
        }
        
        $sql = "DELETE FROM chat_messages WHERE session_id = ?";
        $stmt = $this->chatModel->getConnection()->prepare($sql);
        $stmt->bind_param('s', $sessionId);
        $result = $stmt->execute();
        
        echo json_encode(['success' => $result]);
    }
    public function deleteSession()
{
    header('Content-Type: application/json');
    
    $sessionId = $_POST['session_id'] ?? '';
    if (empty($sessionId)) {
        echo json_encode(['success' => false]);
        return;
    }
    
    $sql = "DELETE FROM chat_messages WHERE session_id = ?";
    $stmt = $this->chatModel->getConnection()->prepare($sql);
    $stmt->bind_param('s', $sessionId);
    $result = $stmt->execute();
    
    echo json_encode(['success' => $result]);
}
}