<?php
namespace App\Services;

use App\Models\User;

class AuthService
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register(array $data): array
    {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ họ tên, email và mật khẩu'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        }

        if ($this->userModel->findByEmail($email)) {
            return ['success' => false, 'message' => 'Email đã được sử dụng'];
        }

        $userId = $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone,
            'address' => $address,
            'role' => $data['role'] ?? 'user',
            'status' => $data['status'] ?? 'active',
        ]);

        if (!$userId) {
            return ['success' => false, 'message' => 'Đăng ký thất bại'];
        }

        $user = $this->userModel->getByEmail($email);
        $this->setUserSession($user ?: ['id' => $userId, 'name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address, 'role' => 'user']);

        return ['success' => true, 'message' => 'Đăng ký thành công', 'user_id' => $userId];
    }

    public function login(string $email, string $password): array
    {
        $email = trim($email);
        if ($email === '' || $password === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập email và mật khẩu'];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || empty($user['password']) || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng'];
        }

        if (($user['status'] ?? 'active') !== 'active') {
            return ['success' => false, 'message' => 'Tài khoản đang bị khóa'];
        }

        $this->setUserSession($user);
        return ['success' => true, 'message' => 'Đăng nhập thành công', 'user' => $_SESSION['user']];
    }

    public function logout(): void
    {
        unset($_SESSION['user'], $_SESSION['user_id']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function setUserSession(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'role' => $user['role'] ?? 'user',
        ];
        $_SESSION['user_id'] = (int)$user['id'];
    }

    public function currentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
    }
}
