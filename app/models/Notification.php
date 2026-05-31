<?php
namespace App\Models;

require_once __DIR__ . '/../../core/Model.php';

use Core\Model;

class Notification extends Model {
    protected $table = 'notifications';
    
    public function getUnreadCount($userId, $userType = 'customer') {
        if ($userType === 'admin') {
            $sql = "SELECT COUNT(*) as count FROM notifications 
                    WHERE is_read = 0 
                    AND (user_type = 'all_admins' OR (user_type = 'specific' AND user_id = ?))
                    AND (expires_at IS NULL OR expires_at > NOW())";
        } else {
            $sql = "SELECT COUNT(*) as count FROM notifications 
                    WHERE is_read = 0 AND user_id = ?
                    AND (expires_at IS NULL OR expires_at > NOW())";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int)$row['count'] : 0;
    }
    
    public function getByUser($userId, $userType = 'customer', $limit = 20, $offset = 0) {
        if ($userType === 'admin') {
            $sql = "SELECT * FROM notifications 
                    WHERE (user_type = 'all_admins' OR (user_type = 'specific' AND user_id = ?))
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
        } else {
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = ?
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        return $notifications;
    }

    public function createNotification($userId, $userType, $title, $message, $type, $referenceId = null, $referenceType = null, $linkUrl = null, $icon = null, $priority = 0, $expiresAt = null) {
        if (!$expiresAt) {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        }

        $userId = (int) $userId;
        $referenceId = $referenceId !== null ? (int) $referenceId : null;
        $priority = (int) $priority;

        // Tránh tạo trùng thông báo cùng loại cho cùng order/reference_id
        if ($this->notificationExists($userId, $userType, $type, $referenceId)) {
            return true;
        }

        $sql = "INSERT INTO notifications (user_id, user_type, title, message, type, reference_id, reference_type, link_url, icon, priority, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            'issssisssis',
            $userId,
            $userType,
            $title,
            $message,
            $type,
            $referenceId,
            $referenceType,
            $linkUrl,
            $icon,
            $priority,
            $expiresAt
        );

        return $stmt->execute();
    }

    private function notificationExists($userId, $userType, $type, $referenceId = null) {
        if ($referenceId === null) {
            return false;
        }

        $sql = "SELECT id FROM notifications WHERE user_id = ? AND user_type = ? AND type = ? AND reference_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('issi', $userId, $userType, $type, $referenceId);

        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result && $result->fetch_assoc();
        $stmt->close();

        return (bool) $exists;
    }
    
    public function markAsRead($id, $userId, $userType = 'customer') {
        if ($userType === 'admin') {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                    WHERE id = ? AND (user_type = 'all_admins' OR (user_type = 'specific' AND user_id = ?))";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ii', $id, $userId);
        } else {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                    WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ii', $id, $userId);
        }
        return $stmt->execute();
    }
    
    public function markAllAsRead($userId, $userType = 'customer') {
        if ($userType === 'admin') {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                    WHERE is_read = 0 AND (user_type = 'all_admins' OR (user_type = 'specific' AND user_id = ?))";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $userId);
        } else {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                    WHERE is_read = 0 AND user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $userId);
        }
        return $stmt->execute();
    }
    
    public function delete($id, $userId, $userType = 'customer') {
        if ($userType === 'admin') {
            $sql = "DELETE FROM notifications 
                    WHERE id = ? AND (user_type = 'all_admins' OR (user_type = 'specific' AND user_id = ?))";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ii', $id, $userId);
        } else {
            $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ii', $id, $userId);
        }
        return $stmt->execute();
    }
}