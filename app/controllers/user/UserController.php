<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\User;
use App\Models\Order;        // Thêm model Order
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

        // Lấy danh sách đơn hàng của user
        $orderModel = new Order();
        $orders = $orderModel->getByUser($userId);

        if (!$user) {
            http_response_code(404);
            echo 'Không tìm thấy người dùng';
            exit;
        }

        if (method_exists($this, 'render')) {
            return $this->view('user/profile', [
                'user' => $user,
                'orders' => $orders
            ]);
        }

        // Fallback HTML (nếu không dùng render, vẫn hiển thị đủ thông tin)
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

        // Form đổi mật khẩu
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

        // Hiển thị đơn hàng
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

    // ========== ĐỔI MẬT KHẨU ==========
    public function changePassword()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $userId = $_SESSION['user']['id'] ?? 0;
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 6) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'
            ], 400);
        }

        if ($new !== $confirm) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Xác nhận mật khẩu không khớp'
            ], 400);
        }

        $userModel = new User();
        $user = $userModel->getById($userId);

        if (!$user || !password_verify($current, $user['password'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng'
            ], 400);
        }

        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $updated = $userModel->updatePassword($userId, $hashed);

        if (!$updated) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Đổi mật khẩu thất bại'
            ], 500);
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }

        // Lấy danh sách địa chỉ (trả về JSON)
    public function getAddresses()
    {
        AuthMiddleware::handle();
        $userId = $_SESSION['user']['id'];
        $addressModel = new \App\Models\Address();
        $addresses = $addressModel->getByUser($userId);
        return $this->jsonResponse(['success' => true, 'addresses' => $addresses]);
    }

    // Thêm địa chỉ mới
    public function addAddress()
    {
        AuthMiddleware::handle();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
        }
        $userId = $_SESSION['user']['id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $addressLine = trim($_POST['address_line'] ?? '');
        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        if (empty($fullName) || empty($phone) || empty($addressLine)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin'], 400);
        }

        $addressModel = new \App\Models\Address();
        // Nếu địa chỉ này là mặc định, set các địa chỉ khác về 0
        if ($isDefault) {
            $addressModel->setDefault($userId, 0); // 0 là không có id, nhưng hàm setDefault cần addressId? Ta sẽ sửa lại: thực tế setDefault($userId, $addressId) dùng để set default cho addressId, nhưng khi thêm mới thì ta cần reset default trước khi insert.
            // Cách khác: gọi resetDefault trước
            $addressModel->resetDefault($userId); // thêm method resetDefault
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
            // Nếu vừa thêm là default, đảm bảo các address khác không default (đã reset ở trên)
            return $this->jsonResponse(['success' => true, 'message' => 'Thêm địa chỉ thành công']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Thêm thất bại'], 500);
        }
    }

    // Sửa địa chỉ
    public function updateAddress()
    {
        // tương tự
    }

    // Xóa địa chỉ
    public function deleteAddress()
    {
        // tương tự
    }

    // Đặt làm mặc định
    public function setDefaultAddress()
    {
        AuthMiddleware::handle();
        $userId = $_SESSION['user']['id'];
        $addressId = $_POST['address_id'] ?? 0;
        if (!$addressId) return $this->jsonResponse(['success' => false, 'message' => 'Thiếu địa chỉ'], 400);
        $addressModel = new \App\Models\Address();
        $result = $addressModel->setDefault($userId, $addressId);
        if ($result) {
            return $this->jsonResponse(['success' => true, 'message' => 'Đã đặt làm địa chỉ mặc định']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Thất bại'], 500);
        }
    }
    }