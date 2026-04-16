<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\User;
use App\Middlewares\AuthMiddleware;

class UserController extends Controller
{
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function profile()
    {
        AuthMiddleware::handle();

        $userId = $_SESSION['user']['id'] ?? 0;

        $userModel = new User();
        $user = $userModel->getById($userId);

        if (!$user) {
            http_response_code(404);
            echo 'Không tìm thấy người dùng';
            exit;
        }

        if (method_exists($this, 'render')) {
            return $this->render('user/profile', [
                'user' => $user
            ]);
        }

        echo '<h2>Thông tin cá nhân</h2>';
        echo '<form action="' . BASE_URL . '/profile/update" method="POST">';
        echo '<label>Họ tên</label><br>';
        echo '<input type="text" name="name" value="' . htmlspecialchars($user['name'] ?? '') . '" required><br><br>';

        echo '<label>Email</label><br>';
        echo '<input type="email" value="' . htmlspecialchars($user['email'] ?? '') . '" disabled><br><br>';

        echo '<label>Số điện thoại</label><br>';
        echo '<input type="text" name="phone" value="' . htmlspecialchars($user['phone'] ?? '') . '"><br><br>';

        echo '<label>Địa chỉ</label><br>';
        echo '<textarea name="address">' . htmlspecialchars($user['address'] ?? '') . '</textarea><br><br>';

        echo '<button type="submit">Cập nhật</button>';
        echo '</form>';
        exit;
    }

    public function updateProfile()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $userId = $_SESSION['user']['id'] ?? 0;

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Tên không được để trống'
            ], 400);
        }

        $userModel = new User();
        $updated = $userModel->updateProfile($userId, [
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ]);

        if (!$updated) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Cập nhật thất bại'
            ], 500);
        }

        $user = $userModel->getById($userId);

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
            'message' => 'Cập nhật thông tin thành công'
        ]);
    }
}