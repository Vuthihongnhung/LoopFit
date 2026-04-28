<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Address; // Thêm model Address
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
        $orderModel = new Order();
        $orders = $orderModel->getByUser($userId);
        if (!$user) {
            http_response_code(404);
            echo 'Không tìm thấy người dùng';
            exit;
        }

        if (method_exists($this, 'render')) {
            return $this->view('user/profile', ['user' => $user, 'orders' => $orders]);
        }
        // Fallback HTML...
        echo '<h2>Thông tin cá nhân</h2>';
        echo '<form id="profile-form">';
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
        echo '<h3>Đổi mật khẩu</h3>';
        echo '<form id="password-form">';
        echo '<label>Mật khẩu hiện tại</label><br>';
        echo '<input type="password" name="current_password" required><br><br>';
        echo '<label>Mật khẩu mới</label><br>';
        echo '<input type="password" name="new_password" required><br><br>';
        echo '<label>Xác nhận mật khẩu mới</label><br>';
        echo '<input type="password" name="confirm_password" required><br><br>';
        echo '<button type="submit">Đổi mật khẩu</button>';
        echo '</form>';
        echo '<h3>Đơn hàng của tôi</h3>';
        if (empty($orders)) {
            echo '<p>Chưa có đơn hàng nào.</p>';
        } else {
            foreach ($orders as $order) {
                echo '<div>';
                echo '<strong>Mã đơn: #' . $order['id'] . '</strong><br>';
                echo 'Ngày đặt: ' . date('d/m/Y', strtotime($order['created_at'])) . '<br>';
                echo 'Ngày thuê: ' . date('d/m/Y', strtotime($order['rent_date'])) . '<br>';
                echo 'Ngày trả dự kiến: ' . date('d/m/Y', strtotime($order['return_date'])) . '<br>';
                if ($order['actual_return_date']) {
                    echo 'Ngày trả thực tế: ' . date('d/m/Y', strtotime($order['actual_return_date'])) . '<br>';
                }
                echo 'Tổng tiền: ' . number_format($order['total_price'], 0, ',', '.') . 'đ<br>';
                if ($order['late_fee'] > 0) {
                    echo 'Phí trễ: ' . number_format($order['late_fee'], 0, ',', '.') . 'đ<br>';
                }
                echo 'Trạng thái: ' . ucfirst($order['status']) . '<br>';
                echo '</div><hr>';
            }
        }
        exit;
    }

    public function updateProfile()
    {
        AuthMiddleware::handle();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
        }
        $userId = $_SESSION['user']['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if ($name === '') {
            return $this->jsonResponse(['success' => false, 'message' => 'Tên không được để trống'], 400);
        }
        $userModel = new User();
        $updated = $userModel->updateProfile($userId, [
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ]);
        if (!$updated) {
            return $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại'], 500);
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
        return $this->jsonResponse(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
    }

    public function changePassword()
    {
        AuthMiddleware::handle();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
        }
        $userId = $_SESSION['user']['id'] ?? 0;
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($new) < 6) {
            return $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'], 400);
        }
        if ($new !== $confirm) {
            return $this->jsonResponse(['success' => false, 'message' => 'Xác nhận mật khẩu không khớp'], 400);
        }
        $userModel = new User();
        $user = $userModel->getById($userId);
        if (!$user || !password_verify($current, $user['password'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng'], 400);
        }
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $updated = $userModel->updatePassword($userId, $hashed);
        if (!$updated) {
            return $this->jsonResponse(['success' => false, 'message' => 'Đổi mật khẩu thất bại'], 500);
        }
        return $this->jsonResponse(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
    }

    // ---------- ĐỊA CHỈ (API JSON) ----------
    public function getAddresses()
    {
        header('Content-Type: application/json');
        $userId = $_SESSION['user']['id'] ?? 0;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }
        $addressModel = new \App\Models\Address();
        $addresses = $addressModel->getByUser($userId);
        echo json_encode(['success' => true, 'data' => $addresses]);
        exit;
    }

    public function addAddress()
    {
        AuthMiddleware::handle();
        $userId = $_SESSION['user']['id'];
        // Đọc dữ liệu JSON từ input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            return $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], 400);
        }
        $fullName = trim($input['full_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $addressLine = trim($input['address_line'] ?? '');
        $isDefault = isset($input['is_default']) ? (int)$input['is_default'] : 0;

        if (empty($fullName) || empty($phone) || empty($addressLine)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin'], 400);
        }

        $addressModel = new Address();
        if ($isDefault) {
            // Xóa default khác trước khi thêm
            $addressModel->resetDefault($userId);
        }
        $data = [
            'user_id' => $userId,
            'full_name' => $fullName,
            'phone' => $phone,
            'address_line' => $addressLine,
            'is_default' => $isDefault
        ];
        $result = $addressModel->create($data);
        if ($result) {
            return $this->jsonResponse(['success' => true, 'message' => 'Thêm địa chỉ thành công']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Thêm thất bại'], 500);
        }
    }

    public function updateAddress()
    {
        AuthMiddleware::handle();
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            return $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], 400);
        }
        $addressId = (int)($input['id'] ?? 0);
        if (!$addressId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu mã địa chỉ'], 400);
        }
        $fullName = trim($input['full_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $addressLine = trim($input['address_line'] ?? '');
        $isDefault = isset($input['is_default']) ? (int)$input['is_default'] : 0;

        if (empty($fullName) || empty($phone) || empty($addressLine)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin'], 400);
        }

        $addressModel = new Address();
        // Kiểm tra địa chỉ thuộc user
        $addr = $addressModel->getById($addressId);
        if (!$addr || $addr['user_id'] != $userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Địa chỉ không hợp lệ'], 404);
        }

        if ($isDefault) {
            $addressModel->resetDefault($userId);
        }
        $data = [
            'full_name' => $fullName,
            'phone' => $phone,
            'address_line' => $addressLine,
            'is_default' => $isDefault
        ];
        $result = $addressModel->update($addressId, $data);
        if ($result) {
            return $this->jsonResponse(['success' => true, 'message' => 'Cập nhật địa chỉ thành công']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại'], 500);
        }
    }

    public function deleteAddress()
    {
        AuthMiddleware::handle();
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            return $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], 400);
        }
        $addressId = (int)($input['id'] ?? 0);
        if (!$addressId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu mã địa chỉ'], 400);
        }

        $addressModel = new Address();
        $addr = $addressModel->getById($addressId);
        if (!$addr || $addr['user_id'] != $userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Địa chỉ không hợp lệ'], 404);
        }
        $result = $addressModel->delete($addressId);
        if ($result) {
            return $this->jsonResponse(['success' => true, 'message' => 'Xóa địa chỉ thành công']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Xóa thất bại'], 500);
        }
    }

    public function setDefaultAddress()
    {
        AuthMiddleware::handle();
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            return $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], 400);
        }
        $addressId = (int)($input['id'] ?? 0);
        if (!$addressId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu mã địa chỉ'], 400);
        }
        $addressModel = new Address();
        $addr = $addressModel->getById($addressId);
        if (!$addr || $addr['user_id'] != $userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Địa chỉ không hợp lệ'], 404);
        }
        // Reset default cho tất cả, sau đó set default cho addressId
        $addressModel->resetDefault($userId);
        $result = $addressModel->setDefaultOnly($addressId); // method mới
        if ($result) {
            return $this->jsonResponse(['success' => true, 'message' => 'Đặt địa chỉ mặc định thành công']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Thất bại'], 500);
        }
    }
}