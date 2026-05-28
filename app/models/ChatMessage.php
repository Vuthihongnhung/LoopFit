<?php

namespace App\Models;

use Core\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';
    
    public function getMessagesBySession($sessionId, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE session_id = ? 
                ORDER BY created_at ASC 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        
        $stmt->bind_param('si', $sessionId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getUnreadMessages($sessionId = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_read = 0 AND sender_type = 'user'";
        if ($sessionId) {
            $sql .= " AND session_id = ?";
        }
        $sql .= " ORDER BY created_at ASC";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        
        if ($sessionId) {
            $stmt->bind_param('s', $sessionId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function markAsRead($messageIds)
    {
        if (empty($messageIds)) return true;
        $ids = implode(',', array_map('intval', $messageIds));
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE id IN ($ids)";
        return $this->conn->query($sql);
    }
    
    public function sendMessage($userId, $sessionId, $senderType, $message)
    {
        $sql = "INSERT INTO {$this->table} (user_id, session_id, sender_type, message) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param('isss', $userId, $sessionId, $senderType, $message);
        return $stmt->execute();
    }
        public function getInsertId()
    {
        return $this->conn->insert_id;
    }
        public function getConnection()
    {
        return $this->conn;
    }
}