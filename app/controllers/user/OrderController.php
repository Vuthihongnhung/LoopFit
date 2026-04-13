<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
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

        $orderModel = new Order();
        $orders = $orderModel->getByUser($userId);

        $orderItemModel = new OrderItem();

        foreach ($orders as &$order) {
            $order['items'] = $orderItemModel->getByOrder($order['id']);
        }

        if (method_exists($this, 'render')) {
            return $this->render('user/orders/index', [
                'orders' => $orders
            ]);
        }

        echo '<h2>Lịch sử đơn hàng</h2>';

        if (empty($orders)) {
            echo '<p>Bạn chưa có đơn hàng nào.</p>';
            exit;
        }

        foreach ($orders as $order) {
            echo '<div style="border:1px solid #ccc; padding:15px; margin-bottom:15px;">';
            echo '<p><strong>Mã đơn:</strong> #' . $order['id'] . '</p>';
            echo '<p><strong>Ngày thuê:</strong> ' . htmlspecialchars($order['rent_date']) . '</p>';
            echo '<p><strong>Ngày trả:</strong> ' . htmlspecialchars($order['return_date']) . '</p>';
            echo '<p><strong>Trạng thái:</strong> ' . htmlspecialchars($order['status']) . '</p>';
            echo '<p><strong>Tổng tiền:</strong> ' . number_format($order['total_price'], 0, ',', '.') . ' đ</p>';

            if (!empty($order['items'])) {
                echo '<p><strong>Sản phẩm:</strong></p>';
                echo '<ul>';
                foreach ($order['items'] as $item) {
                    echo '<li>';
                    echo htmlspecialchars($item['name']) . ' - SL: ' . (int)$item['quantity'] . ' - Giá: ' . number_format($item['price'], 0, ',', '.') . ' đ';
                    echo '</li>';
                }
                echo '</ul>';
            }

            echo '</div>';
        }

        exit;
    }
}