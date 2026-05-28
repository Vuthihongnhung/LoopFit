<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    protected function redirect($url)
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    /* ===== Hiển thị form đăng nhập ===== */
    public function loginForm()
    {
        if (isset($_SESSION['user'])) {
            $this->redirect('/');
        }
        $email = $_SESSION['registered_email'] ?? '';
        unset($_SESSION['registered_email']);
        $this->view('user/auth/login', ['email' => $email]);
    }

    /* ===== Hiển thị form đăng ký ===== */
    public function registerForm()
    {
        if (isset($_SESSION['user'])) {
            $this->redirect('/');
        }
        $this->view('user/auth/register');
    }

    /* ===== Xử lý đăng ký tài khoản mới ===== */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập đầy đủ họ tên, email và mật khẩu'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Email không hợp lệ'], 400);
        }
        if (strlen($password) < 6) {
            return $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'], 400);
        }

        $userModel = new User();
        $existingUser = $userModel->getByEmail($email);
        if ($existingUser) {
            return $this->jsonResponse(['success' => false, 'message' => 'Email đã được sử dụng'], 409);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $data = [
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
            'phone'    => $phone,
            'address'  => $address,
            'role'     => 'user',
            'status'   => 'active'
        ];

        $userId = $userModel->create($data);
        if (!$userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Đăng ký thất bại'], 500);
        }

        // Lưu session user
        $_SESSION['user'] = [
            'id'      => $userId,
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
            'role'    => 'user'
        ];
        // Xóa session chat cũ của khách
    unset($_SESSION['chat_session_id']);

        return $this->jsonResponse(['success' => true, 'message' => 'Đăng ký thành công']);
    }

    /* ===== Xử lý đăng nhập ===== */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->loginForm();
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Vui lòng nhập email và mật khẩu';
            if ($this->isAjax()) {
                return $this->jsonResponse(['success' => false, 'message' => $error], 400);
            } else {
                $_SESSION['error'] = $error;
                $this->redirect('/login');
            }
        }

        $userModel = new User();
        $user = $userModel->getByEmail($email);

        if (!$user) {
            $error = 'Email không tồn tại';
            if ($this->isAjax()) {
                return $this->jsonResponse(['success' => false, 'message' => $error], 404);
            } else {
                $_SESSION['error'] = $error;
                $this->redirect('/login');
            }
        }

        if (!password_verify($password, $user['password'])) {
            $error = 'Mật khẩu không đúng';
            if ($this->isAjax()) {
                return $this->jsonResponse(['success' => false, 'message' => $error], 401);
            } else {
                $_SESSION['error'] = $error;
                $this->redirect('/login');
            }
        }

        // *** CHẶN ADMIN ĐĂNG NHẬP VÀO TRANG USER ***
        if (isset($user['role']) && $user['role'] === 'admin') {
            $error = 'Tài khoản quản trị không thể đăng nhập vào khu vực người dùng. Vui lòng đăng nhập qua trang quản trị.';
            if ($this->isAjax()) {
                return $this->jsonResponse(['success' => false, 'message' => $error], 403);
            } else {
                $_SESSION['error'] = $error;
                $this->redirect('/login');
            }
        }

        // Lưu session user
        $_SESSION['user'] = [
            'id'      => $user['id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'phone'   => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'role'    => 'user'  
        ];
unset($_SESSION['chat_session_id']);
        $redirectUrl = BASE_URL . '/';

        if ($this->isAjax()) {
            return $this->jsonResponse([
                'success'  => true,
                'message'  => 'Đăng nhập thành công',
                'redirect' => $redirectUrl
            ]);
        } else {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /* ===== Đăng xuất ===== */
    public function logout()
    {
         unset($_SESSION['chat_session_id']);
        if ($this->isAjax()) {
            unset($_SESSION['user']);
            return $this->jsonResponse(['success' => true, 'message' => 'Đăng xuất thành công']);
        } else {
            unset($_SESSION['user']);
            $this->redirect('/login');
        }
    }
}