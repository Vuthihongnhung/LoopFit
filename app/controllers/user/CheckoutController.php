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

        if (empty($items)) {
            echo 'Giỏ hàng trống';
            exit;
        }

        $total = 0;

        foreach ($items as &$item) {

            $days = 1;

            if (!empty($item['rent_date']) && !empty($item['return_date'])) {
                $rent = strtotime($item['rent_date']);
                $return = strtotime($item['return_date']);
                $days = max(1, ($return - $rent) / 86400);
            }

            $item['days'] = $days;
            $item['item_total'] = $item['price'] * $item['quantity'] * $days;

            $total += $item['item_total'];
        }

        $viewContent = __DIR__ . '/../../../views/user/checkout.php';
        require_once __DIR__ . '/../../../views/layouts/user.php';
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

        $cartModel = new Cart();
        $items = $cartModel->getByUser($userId);

        if (empty($items)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Giỏ hàng trống'
            ], 400);
        }

        $rentalService = new RentalService();

        $total = 0;

        foreach ($items as $item) {

            // validate ngày từng sản phẩm
            if (!$rentalService->isDateValid($item['rent_date'], $item['return_date'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ngày thuê không hợp lệ ở sản phẩm: ' . $item['name']
                ], 400);
            }

            // check trùng lịch
            $conflict = $rentalService->hasConflict(
                $item['product_id'],
                $item['rent_date'],
                $item['return_date']
            );

            if ($conflict) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Sản phẩm "' . $item['name'] . '" đã được thuê trong khoảng này'
                ], 400);
            }

            // tính tiền
            $days = max(1, (strtotime($item['return_date']) - strtotime($item['rent_date'])) / 86400);

            $total += $item['price'] * $item['quantity'] * $days;
        }

        $db = new Database();
        $conn = $db->getConnection();

        $orderModel = new Order();
        $orderItemModel = new OrderItem();

        try {
            $conn->begin_transaction();

            // 👉 lấy ngày tổng (có thể lấy min-max)
            $rentDate = min(array_column($items, 'rent_date'));
            $returnDate = max(array_column($items, 'return_date'));

            $orderId = $orderModel->create($userId, $rentDate, $returnDate, $total, 'pending');

            if (!$orderId) {
                throw new \Exception('Không thể tạo đơn hàng');
            }

            foreach ($items as $item) {

                $days = max(1, (strtotime($item['return_date']) - strtotime($item['rent_date'])) / 86400);

                $ok = $orderItemModel->create(
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['size'] ?? null,
                    $item['rent_date'],
                    $item['return_date'],
                    $days
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