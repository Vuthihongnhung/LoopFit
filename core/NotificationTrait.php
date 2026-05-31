<?php
trait NotificationTrait {
    protected function sendNotification($userId, $userType, $title, $message, $type, $referenceId = null, $referenceType = null, $linkUrl = null, $icon = null, $priority = 0) {
        require_once __DIR__ . '/../app/models/Notification.php';
        $notification = new \App\Models\Notification();
        return $notification->createNotification(
            $userId,
            $userType,
            $title,
            $message,
            $type,
            $referenceId,
            $referenceType,
            $linkUrl,
            $icon,
            $priority
        );
    }
}