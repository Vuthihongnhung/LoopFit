<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Order;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function showQr($order_id = null)
    {
        $orderId = (int)($order_id ?? $_GET['order_id'] ?? 0);
        if ($orderId <= 0) {
            http_response_code(400);
            echo 'Thiếu mã đơn hàng';
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getPaymentInfo($orderId);
        if (!$order) {
            http_response_code(404);
            echo 'Không tìm thấy đơn hàng';
            return;
        }

        $amount = $order['final_total'] ?? $order['total_price'] ?? $order['paid_amount'] ?? 0;
        $customerName = $order['user_name'] ?? $order['name'] ?? 'Khách hàng';

        $viewPath = __DIR__ . '/../../../views/user/payment_qr.php';
        if (file_exists($viewPath)) {
            return $this->view('user/payment_qr', [
                'order' => $order,
                'order_id' => $orderId,
                'customer_name' => $customerName,
                'amount' => $amount,
            ]);
        }

        echo '<h2>Thanh toán chuyển khoản</h2>';
        echo '<p>Khách hàng: ' . htmlspecialchars($customerName) . '</p>';
        echo '<p>Mã đơn: #' . (int)$orderId . '</p>';
        echo '<p>Số tiền: ' . number_format((float)$amount) . 'đ</p>';
    }

    public function confirmPayment()
    {
        header('Content-Type: application/json; charset=utf-8');
        $orderId = (int)($_POST['order_id'] ?? $_GET['order_id'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu order_id'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $orderModel = new Order();
        $paymentModel = new Payment();

        $order = $orderModel->getById($orderId);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $orderModel->updatePaymentStatus($orderId, 'pending_verification');
        $paymentModel->updateStatusByOrderId($orderId, 'pending_verification');

        echo json_encode([
            'status' => 'success',
            'order_id' => $orderId,
            'payment_status' => 'pending_verification',
            'message' => 'Đã ghi nhận thanh toán, đang chờ xác minh'
        ], JSON_UNESCAPED_UNICODE);
    }
}
