<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\RentalService;
use Core\Database;

class CheckoutController extends Controller
{
    protected function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function index()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            header('Location: /');
            exit;
        }

        $cartModel = new Cart();
        $items = $cartModel->getByUser($userId);
        $total = $cartModel->getTotal($userId);

        if (empty($items)) {
            echo 'Giỏ hàng trống';
            exit;
        }

        if (method_exists($this, 'render')) {
            return $this->render('user/checkout', [
                'items' => $items,
                'total' => $total
            ]);
        }

        echo '<h2>Checkout</h2>';
        echo '<p>Tổng tiền: ' . number_format($total, 0, ',', '.') . ' đ</p>';
        echo '<form action="/checkout/process" method="POST">';
        echo '<label>Ngày thuê:</label><br>';
        echo '<input type="date" name="rent_date" required><br><br>';
        echo '<label>Ngày trả:</label><br>';
        echo '<input type="date" name="return_date" required><br><br>';
        echo '<button type="submit">Xác nhận đặt hàng</button>';
        echo '</form>';
        exit;
    }

    public function process()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $rentDate = $_POST['rent_date'] ?? '';
        $returnDate = $_POST['return_date'] ?? '';

        if (empty($rentDate) || empty($returnDate)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vui lòng chọn ngày thuê và ngày trả'
            ], 400);
        }

        $cartModel = new Cart();
        $items = $cartModel->getByUser($userId);

        if (empty($items)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Giỏ hàng trống'
            ], 400);
        }

        $rentalService = new RentalService();

        if (!$rentalService->isDateValid($rentDate, $returnDate)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Ngày thuê hoặc ngày trả không hợp lệ'
            ], 400);
        }

        $conflictCheck = $rentalService->checkCartConflict($items, $rentDate, $returnDate);

        if (!$conflictCheck['success']) {
            return $this->jsonResponse($conflictCheck, 400);
        }

        $total = $cartModel->getTotal($userId);

        $db = new Database();
        $conn = $db->getConnection();

        $orderModel = new Order();
        $orderItemModel = new OrderItem();

        try {
            $conn->begin_transaction();

            $orderId = $orderModel->create($userId, $rentDate, $returnDate, $total, 'pending');

            if (!$orderId) {
                throw new \Exception('Không thể tạo đơn hàng');
            }

            foreach ($items as $item) {
                $ok = $orderItemModel->create(
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                );

                if (!$ok) {
                    throw new \Exception('Không thể tạo chi tiết đơn hàng');
                }
            }

            $cartModel->clear($userId);

            $conn->commit();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            $conn->rollback();

            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}