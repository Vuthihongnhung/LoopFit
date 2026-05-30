<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function getUnreadCount()
    {
        $count = $this->notificationModel->getUnreadCount(0, 'admin');
        $this->json(['count' => $count]);
    }

    public function getLatest()
    {
        $notifications = $this->notificationModel->getByUser(0, 'admin', 10, 0);
        $this->json(['notifications' => $notifications]);
    }

    public function markAsRead()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        $success = $this->notificationModel->markAsRead($id, 0, 'admin');
        $this->json(['success' => $success]);
    }

    public function markAllAsRead()
    {
        $success = $this->notificationModel->markAllAsRead(0, 'admin');
        $this->json(['success' => $success]);
    }

    public function delete()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        $success = $this->notificationModel->delete($id, 0, 'admin');
        $this->json(['success' => $success]);
    }
}