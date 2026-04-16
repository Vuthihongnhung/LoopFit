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

    public function loginForm()
    {
        if (isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        if (method_exists($this, 'render')) {
            return $this->render('user/login');
        }

        echo '<h2>Đăng nhập</h2>';
        echo '<form action="' . BASE_URL . '/login" method="POST">';
        echo '<label>Email</label><br>';
        echo '<input type="email" name="email" required><br><br>';
        echo '<label>Mật khẩu</label><br>';
        echo '<input type="password" name="password" required><br><br>';
        echo '<button type="submit">Đăng nhập</button>';
        echo '</form>';
        exit;
    }

    public function registerForm()
    {
        if (isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        if (method_exists($this, 'render')) {
            return $this->render('user/register');
        }

        echo '<h2>Đăng ký</h2>';
        echo '<form action="' . BASE_URL . '/register" method="POST">';
        echo '<label>Họ tên</label><br>';
        echo '<input type="text" name="name" required><br><br>';
        echo '<label>Email</label><br>';
        echo '<input type="email" name="email" required><br><br>';
        echo '<label>Mật khẩu</label><br>';
        echo '<input type="password" name="password" required><br><br>';
        echo '<label>Số điện thoại</label><br>';
        echo '<input type="text" name="phone"><br><br>';
        echo '<label>Địa chỉ</label><br>';
        echo '<textarea name="address"></textarea><br><br>';
        echo '<button type="submit">Đăng ký</button>';
        echo '</form>';
        exit;
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ họ tên, email và mật khẩu'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email không hợp lệ'
            ], 400);
        }

        if (strlen($password) < 6) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Mật khẩu phải có ít nhất 6 ký tự'
            ], 400);
        }

        $userModel = new User();
        $existingUser = null;

        if (method_exists($userModel, 'findByEmail')) {
            $existingUser = $userModel->findByEmail($email);
        } elseif (method_exists($userModel, 'getByEmail')) {
            $existingUser = $userModel->getByEmail($email);
        }

        if ($existingUser) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email đã được sử dụng'
            ], 409);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'phone' => $phone,
            'address' => $address
        ];

        if (!method_exists($userModel, 'create')) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'User model chưa có hàm create()'
            ], 500);
        }

        $userId = $userModel->create($data);

        if (!$userId) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Đăng ký thất bại'
            ], 500);
        }

        $_SESSION['user'] = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'role' => 'user'
        ];

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Đăng ký thành công'
        ]);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vui lòng nhập email và mật khẩu'
            ], 400);
        }

        $userModel = new User();
        $user = null;

        if (method_exists($userModel, 'findByEmail')) {
            $user = $userModel->findByEmail($email);
        } elseif (method_exists($userModel, 'getByEmail')) {
            $user = $userModel->getByEmail($email);
        }

        if (!$user) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email không tồn tại'
            ], 404);
        }

        if (!isset($user['password']) || !password_verify($password, $user['password'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Mật khẩu không đúng'
            ], 401);
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'role' => $user['role'] ?? 'user'
        ];

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Đăng nhập thành công'
        ]);
    }

    public function logout()
    {
        unset($_SESSION['user']);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        header('Location: ' . BASE_URL . '/');
        exit;
    }
}