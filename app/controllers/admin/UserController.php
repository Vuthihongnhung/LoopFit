<?php
namespace App\Controllers\Admin;

use App\Models\User;
use Core\Controller;
use App\Middlewares\AdminMiddleware;

class UserController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::check();
    }

    /* ===== Hiển thị trang danh sách người dùng ===== */
    public function index()
    {
        ob_start();
        require_once __DIR__ . '/../../../views/admin/users/index.php'; 
        $content = ob_get_clean();
        $pageTitle = 'Quản lý người dùng'; 
        $activeMenu = 'users'; 
        require_once __DIR__ . '/../../../views/layouts/admin.php';
    }

    /* ===== API: Lấy danh sách tất cả người dùng (dùng cho AJAX) ===== */
    public function apiGetList()
    {
        $userModel = new User();
        $users = $userModel->getAll();
        
        $formattedUsers = [];
        foreach ($users as $row) {
            $formattedUsers[] = [
                "id" => (int)$row["id"],
                "name" => $row["name"],
                "email" => $row["email"],
                "status" => $row["status"] ?? 'active',  
                "created_at" => $row["created_at"]
            ];
        }
        
        return $this->json([
            "success" => true,
            "data" => $formattedUsers
        ]);
    }
    
    /* ===== API: Chuyển đổi trạng thái của người dùng ===== */
    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json([
                "success" => false,
                "error" => "Phương thức không hợp lệ"
            ]);
        }
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            return $this->json([
                "success" => false,
                "error" => "Thiếu user id"
            ]);
        }
        
        $userModel = new User();
        $user = $userModel->getStatusById($id);
        
        if (!$user) {
            return $this->json([
                "success" => false,
                "error" => "User không tồn tại"
            ]);
        }
        
        $new_status = ($user['status'] === 'active') ? 'banned' : 'active';
        $result = $userModel->updateStatus($id, $new_status);
        
        if ($result) {
            return $this->json([
                "success" => true,
                "new_status" => $new_status
            ]);
        } else {
            return $this->json([
                "success" => false,
                "error" => "Cập nhật thất bại"
            ]);
        }
    }
}