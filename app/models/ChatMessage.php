<?php

namespace App\Models;

use Core\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    /**
     * Gửi tin nhắn (dùng chung cho user và admin)
     * @param int|null $userId
     * @param string $sessionId
     * @param string $senderType 'user' hoặc 'admin'
     * @param string $message
     * @return bool
     */
    public function sendMessage($userId, $sessionId, $senderType, $message)
    {
        $sql = "INSERT INTO {$this->table} (user_id, session_id, sender_type, message, created_at, is_read) 
                VALUES (?, ?, ?, ?, NOW(), 0)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('isss', $userId, $sessionId, $senderType, $message);
        return $stmt->execute();
    }

    /**
     * Lấy tin nhắn theo session (có hỗ trợ last_id để lấy tin mới)
     * @param string $sessionId
     * @param int $lastId
     * @return array
     */
    public function getMessagesBySession($sessionId, $lastId = 0)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE session_id = ? AND id > ?
                ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('si', $sessionId, $lastId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy tin nhắn theo user_id (cho người dùng đã đăng nhập)
     * @param int $userId
     * @param int $lastId
     * @return array
     */
    public function getMessagesByUser($userId, $lastId = 0)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND id > ?
                ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ii', $userId, $lastId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy tất cả tin nhắn của session (không phân trang, dùng cho admin)
     * @param string $sessionId
     * @param int $limit
     * @return array
     */
    public function getAllMessagesBySession($sessionId, $limit = 50)
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

    /**
     * Lấy tin nhắn chưa đọc (dùng cho admin)
     * @param string|null $sessionId
     * @return array
     */
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

    /**
     * Đánh dấu tin nhắn đã đọc
     * @param array $messageIds
     * @return bool
     */
    public function markAsRead($messageIds)
    {
        if (empty($messageIds)) return true;
        $ids = implode(',', array_map('intval', $messageIds));
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE id IN ($ids)";
        return $this->conn->query($sql);
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