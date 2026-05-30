<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Middlewares\AuthMiddleware;

class OrderController extends Controller
{
    protected $orderModel;
    protected $orderItemModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
    }

    protected function getUserId()
    {
        return $_SESSION['user']['id'] ?? null;
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ===== Hiển thị trang danh sách đơn hàng ===== */
    public function index()
    {
        $this->view('user/orders');
    }

    /* ===== API lấy danh sách đơn hàng (trả JSON) ===== */
    public function apiList()
    {
        // Xóa buffer để tránh lỗi JSON
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user']) || !$_SESSION['user']['id']) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }
        $userId = $_SESSION['user']['id'];
        $status = $_GET['status'] ?? 'all';

        try {
            if ($status !== 'all') {
                $orders = $this->orderModel->getByUserAndStatus($userId, $status);
            } else {
                $orders = $this->orderModel->getByUser($userId);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . $e->getMessage()]);
            return;
        }

        $data = [];
        foreach ($orders as $order) {
            $items = $this->orderItemModel->getByOrder($order['id']);
            $formattedItems = [];
            foreach ($items as $item) {
                // Xử lý ảnh
                $imageRaw = $item['image'] ?? '';
                $firstImage = trim(explode(',', $imageRaw)[0] ?? '');
                if (empty($firstImage)) $firstImage = 'default-product.png';
                $imageUrl = BASE_URL . '/uploads/' . $firstImage;
                
                // Tính số ngày thuê
                $days = 1;
                if (!empty($item['rental_start']) && !empty($item['rental_end'])) {
                    try {
                        $start = new \DateTime($item['rental_start']);
                        $end = new \DateTime($item['rental_end']);
                        $days = max(1, $start->diff($end)->days + 1);
                    } catch (Exception $e) {}
                }
                $totalPrice = ($item['price'] ?? 0) * ($item['quantity'] ?? 1) * $days;
                
                $formattedItems[] = [
                    'product_id'     => (int)($item['product_id'] ?? 0),
                    'product_name'   => $item['product_name'] ?? $item['name'] ?? 'Sản phẩm',
                    'product_image'  => $imageUrl,
                    'image'          => $imageUrl,
                    'quantity'       => (int)($item['quantity'] ?? 1),
                    'price_per_day'  => (float)($item['price'] ?? 0),
                    'rental_start'   => !empty($item['rental_start']) ? date('d/m/Y', strtotime($item['rental_start'])) : null,
                    'rental_end'     => !empty($item['rental_end']) ? date('d/m/Y', strtotime($item['rental_end'])) : null,
                    'total_price'    => $totalPrice
                ];
            }
            
            $data[] = [
                'id'           => (int)$order['id'],
                'status'       => $order['status'],
                'created_at'   => $order['created_at'],
                'total_amount' => (float)($order['final_total'] ?? $order['total_amount'] ?? 0),
                'items'        => $formattedItems
            ];
        }
        
        echo json_encode(['success' => true, 'orders' => $data]);
    }

    /* ===== API hủy đơn hàng ===== */
    public function apiCancel()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user']) || !$_SESSION['user']['id']) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }
        $userId = $_SESSION['user']['id'];
        $orderId = (int)($_POST['order_id'] ?? 0);
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            return;
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order || $order['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng không hợp lệ']);
            return;
        }

        $allowed = ['pending', 'confirmed', 'preparing', 'shipping'];
        if (!in_array($order['status'], $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng ở trạng thái này']);
            return;
        }

        $conn = $this->orderModel->getConnection();
        $conn->begin_transaction();
        try {
            // Nếu đơn đã xác nhận, hoàn trả rented_quantity
            if (in_array($order['status'], ['confirmed', 'renting'])) {
                $items = $this->orderItemModel->getByOrder($orderId);
                $productModel = new \App\Models\Product();
                foreach ($items as $item) {
                    $productModel->decreaseRentedQuantity($item['product_id'], $item['quantity']);
                }
            }
            $this->orderModel->updateStatus($orderId, 'cancelled');
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng']);
        } catch (\Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Hủy đơn thất bại: ' . $e->getMessage()]);
        }
    }

    /* ===== API yêu cầu hoàn trả ===== */
    public function apiReturn()
    {
        header('Content-Type: application/json');

        // Đảm bảo session đã khởi tạo
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $this->getUserId();
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }

        // Nhận order_id từ POST hoặc từ JSON input
        $orderId = $_POST['order_id'] ?? 0;
        if (!$orderId) {
            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = $input['order_id'] ?? 0;
        }

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            return;
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order || $order['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng không hợp lệ']);
            return;
        }

        // Chỉ cho phép yêu cầu hoàn trả khi đơn đang ở trạng thái "đang thuê"
        if ($order['status'] !== 'renting') {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng không ở trạng thái đang thuê']);
            return;
        }

        // Chuyển trạng thái sang 'returning'
        $updated = $this->orderModel->updateStatus($orderId, 'returning');
        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Yêu cầu hoàn trả đã được gửi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu thất bại, vui lòng thử lại']);
        }
    }

    /* ===== Chi tiết đơn hàng (trang riêng) ===== */
    public function detail($id)
    {
        AuthMiddleware::handle();
        $userId = $this->getUserId();
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $order = $this->orderModel->getById($id);
        if (!$order || $order['user_id'] != $userId) {
            header('Location: ' . BASE_URL . '/orders');
            exit;
        }

        // Lấy địa chỉ
        $address = ['full_name' => 'Chưa có', 'phone' => 'Chưa có', 'address_line' => 'Chưa có'];
        if (!empty($order['address_id'])) {
            $addressModel = new Address();
            $addrData = $addressModel->getById($order['address_id']);
            if ($addrData) {
                $address['full_name'] = $addrData['full_name'] ?? ($addrData['name'] ?? 'Chưa có');
                $address['phone'] = $addrData['phone'] ?? 'Chưa có';
                $street = $addrData['address_line'] ?? ($addrData['address'] ?? ($addrData['full_address'] ?? ''));
                $address['address_line'] = $street ?: 'Chưa có';
            }
        }
        $order['address'] = $address;

        // Lấy danh sách sản phẩm
        $order['items'] = $this->orderItemModel->getByOrder($id);

        // Tính toán số ngày thuê, tổng tiền,...
        try {
            $rentDate = new \DateTime($order['rent_date']);
            $returnDate = new \DateTime($order['return_date']);
            $order['days'] = max(1, $rentDate->diff($returnDate)->days + 1);
        } catch (\Exception $e) {
            $order['days'] = 1;
        }

        $totalRent = 0;
        $totalDeposit = 0;
        foreach ($order['items'] as &$item) {
            $pricePerDay = (float)($item['price'] ?? 0);
            $depositPerItem = (float)($item['deposit_per_item'] ?? ($pricePerDay * 0.3));
            $qty = (int)$item['quantity'];
            $item['total_price'] = $pricePerDay * $qty * $order['days'];
            $item['total_deposit'] = $depositPerItem * $qty;
            $totalRent += $item['total_price'];
            $totalDeposit += $item['total_deposit'];
        }
        $order['total_price'] = $totalRent;
        $order['deposit_amount'] = $totalDeposit;

        $shipping_fee = (float)($order['shipping_fee'] ?? 0);
        $discount = (float)($order['discount_price'] ?? 0);
        $lateFee = (float)($order['late_fee'] ?? 0);
        $order['final_total'] = $totalRent + $totalDeposit + $shipping_fee - $discount;
        if ($order['final_total'] < 0) $order['final_total'] = 0;

        $order['late_days'] = 0;
        if (!empty($order['actual_return_date']) && !empty($order['return_date'])) {
            $expectedReturn = new \DateTime($order['return_date']);
            $actualReturn = new \DateTime($order['actual_return_date']);
            if ($actualReturn > $expectedReturn) {
                $order['late_days'] = $actualReturn->diff($expectedReturn)->days;
            }
        }

        $order['deposit_status'] = $order['deposit_status'] ?? 'pending';
        $order['deposit_refunded_amount'] = (float)($order['deposit_refunded_amount'] ?? 0);
        $order['payment_status'] = $order['payment_status'] ?? 'unpaid';
        $order['payment_method'] = $order['payment_method'] ?? 'cash';
        $order['late_fee_status'] = $order['late_fee_status'] ?? 'unpaid';

        $this->view('user/order_detail', ['order' => $order]);
    }

    /* ===== User thanh toán phí trễ ===== */
    public function payLateFee()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Method không hợp lệ']);
        }
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->json(['success' => false, 'message' => 'Chưa đăng nhập']);
        }
        $orderId = (int)($_POST['order_id'] ?? 0);
        if (!$orderId) {
            return $this->json(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order || $order['user_id'] != $userId) {
            return $this->json(['success' => false, 'message' => 'Đơn hàng không hợp lệ']);
        }

        if ($order['late_fee'] <= 0) {
            return $this->json(['success' => false, 'message' => 'Đơn hàng không có phí trễ']);
        }

        if ($order['late_fee_status'] != 'unpaid') {
            return $this->json(['success' => false, 'message' => 'Phí trễ đã được xử lý']);
        }

        $updated = $this->orderModel->updateLateFeeStatus($orderId, 'pending_verification');
        if ($updated) {
            return $this->json(['success' => true, 'message' => 'Đã ghi nhận yêu cầu thanh toán phí trễ, chờ admin xác nhận']);
        } else {
            return $this->json(['success' => false, 'message' => 'Lỗi cập nhật, thử lại sau']);
        }
    }

    public function success()
    {
        $orderId = $_GET['order_id'] ?? 0;
        $this->view('user/order_success', ['order_id' => $orderId]);
    }
}