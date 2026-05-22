<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    /* ===== Hiển thị form đăng nhập admin ===== */
    public function showLogin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Nếu đã đăng nhập với role admin thì chuyển dashboard
        if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }
        
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        
        return $this->view('admin/login', ['error' => $error], false);
    }

    /* ===== Xử lý đăng nhập admin ===== */
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Vui lòng nhập email và mật khẩu';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $userModel = new User();
        $user = null;

        // Tìm user theo email
        if (method_exists($userModel, 'findByEmail')) {
            $user = $userModel->findByEmail($email);
        } elseif (method_exists($userModel, 'getByEmail')) {
            $user = $userModel->getByEmail($email);
        } else {
            $conn = $userModel->getConnection();
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        }

        // Kiểm tra user, role admin và mật khẩu
        if (!$user || !isset($user['role']) || $user['role'] !== 'admin' || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Email hoặc mật khẩu không đúng, hoặc bạn không có quyền admin';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        // Lưu session theo cấu trúc chuẩn (dùng mảng user)
        // Sau khi đăng nhập thành công
        $_SESSION['admin'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => 'admin'
        ];
        unset($_SESSION['user']);
        unset($_SESSION['error']);

        // Chuyển hướng đến dashboard admin
        header('Location: ' . BASE_URL . '/admin/dashboard');
        exit;
    }

    /* ===== Đăng xuất admin ===== */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user']);
        unset($_SESSION['user_id'], $_SESSION['admin_id'], $_SESSION['role'], $_SESSION['is_admin']);
        
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }

    /* ===== Kiểm tra trạng thái đăng nhập admin (dùng cho AJAX) ===== */
    public function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
        
        header('Content-Type: application/json');
        echo json_encode([
            'logged_in' => $isAdmin,
            'role' => $isAdmin ? 'admin' : null
        ]);
        exit;
    }
}