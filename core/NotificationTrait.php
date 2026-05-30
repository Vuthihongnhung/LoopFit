<?php
trait NotificationTrait {
    protected function sendNotification($userId, $userType, $title, $message, $type, $referenceId = null, $referenceType = null, $linkUrl = null, $icon = null, $priority = 0) {
        require_once __DIR__ . '/../app/models/Notification.php';
        $notification = new Notification();
        
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $sql = "INSERT INTO notifications (user_id, user_type, title, message, type, reference_id, reference_type, link_url, icon, priority, expires_at) 
                VALUES (:user_id, :user_type, :title, :message, :type, :ref_id, :ref_type, :link, :icon, :priority, :expires)";
        
        $stmt = $notification->getConnection()->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'user_type' => $userType,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'ref_id' => $referenceId,
            'ref_type' => $referenceType,
            'link' => $linkUrl,
            'icon' => $icon,
            'priority' => $priority,
            'expires' => $expiresAt
        ]);
    }
}