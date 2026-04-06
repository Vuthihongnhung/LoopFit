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
    
    public function index()
    {
        return $this->render('admin/users/index.php');
    }
    
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
                "status" => $row["status"] ?? 'active',   // ✅ THÊM DÒNG NÀY
                "created_at" => $row["created_at"]
            ];
        }
        
        return $this->json([
            "success" => true,
            "data" => $formattedUsers
        ]);
    }
    
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