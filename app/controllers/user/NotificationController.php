<?php
namespace App\Controllers\User;

require_once __DIR__ . '/../../../core/Controller.php';
require_once __DIR__ . '/../../../app/models/Notification.php';

use Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    // API: Lấy số lượng thông báo chưa đọc
    public function getUnreadCount()
    {
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
        if (empty($userId)) {
            $this->json(['count' => 0]);
            return;
        }
        $count = $this->notificationModel->getUnreadCount($userId, 'customer');
        $this->json(['count' => $count]);
    }

    // API: Lấy 10 thông báo mới nhất
    public function getLatest()
    {
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
        if (empty($userId)) {
            $this->json(['notifications' => []]);
            return;
        }
        $notifications = $this->notificationModel->getByUser($userId, 'customer', 10, 0);
        $this->json(['notifications' => $notifications]);
    }

    // API: Đánh dấu 1 thông báo đã đọc
    public function markAsRead()
    {
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
        if (empty($userId)) {
            $this->json(['success' => false]);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        $success = $this->notificationModel->markAsRead($id, $userId, 'customer');
        $this->json(['success' => $success]);
    }

    // API: Đánh dấu tất cả đã đọc
    public function markAllAsRead()
    {
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
        if (empty($userId)) {
            $this->json(['success' => false]);
            return;
        }
        $success = $this->notificationModel->markAllAsRead($userId, 'customer');
        $this->json(['success' => $success]);
    }

    // API: Xóa 1 thông báo
    public function delete()
    {
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
        if (empty($userId)) {
            $this->json(['success' => false]);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        $success = $this->notificationModel->delete($id, $userId, 'customer');
        $this->json(['success' => $success]);
    }

    // Trang danh sách thông báo (dùng view)
    public function index()
    {
        if (empty($_SESSION['user']['id']) && empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
        $notifications = $this->notificationModel->getByUser($userId, 'customer', $limit, $offset);
        $this->view('user/notifications/index', ['notifications' => $notifications, 'current_page' => $page]);
    }
}