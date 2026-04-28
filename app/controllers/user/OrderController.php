<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Middlewares\AuthMiddleware;

class OrderController extends Controller
{
    protected function getUserId()
    {
        return $_SESSION['user']['id'] ?? null;
    }

    /**
     * Hiển thị trang danh sách đơn hàng (dùng AJAX để tải dữ liệu)
     */
    public function index()
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $orderModel = new Order();
        $orders = $orderModel->getByUser($userId); // lấy tất cả đơn hàng của user

        $this->view('user/orders', [
            'orders' => $orders
        ]);
    }

    /**
     * API lấy danh sách đơn hàng (trả JSON)
     */
    public function apiList()
{
    header('Content-Type: application/json');
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user']) || !$_SESSION['user']['id']) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        return;
    }
    $userId = $_SESSION['user']['id'];
    $status = $_GET['status'] ?? 'all';

    $orderModel = new Order();
    // Lấy đơn hàng theo user, có thể lọc status
    $orders = $orderModel->getByUser($userId, $status === 'all' ? null : $status);
    
    // Nếu chưa có dữ liệu, trả về mảng rỗng
    $data = [];
    foreach ($orders as $order) {
        // Lấy chi tiết sản phẩm trong đơn
        $orderItemModel = new OrderItem();
        $items = $orderItemModel->getByOrder($order['id']);
        
        // Format items
        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'product_name'   => $item['product_name'],
                'product_image'  => !empty($item['image']) ? BASE_URL . '/uploads/' . $item['image'] : BASE_URL . '/assets/user/images/default-product.png',
                'quantity'       => $item['quantity'],
                'price_per_day'  => number_format($item['price'], 0, ',', '.'),
                'rental_start'   => date('d/m/Y', strtotime($item['rental_start'])),
                'rental_end'     => date('d/m/Y', strtotime($item['rental_end'])),
                'subtotal'       => number_format($item['subtotal'], 0, ',', '.')
            ];
        }
        
        $statusMap = [
            'pending' => 'Chờ xác nhận',
            'processing' => 'Đang xử lý',
            'renting' => 'Đang thuê',
            'completed' => 'Hoàn trả',
            'cancelled' => 'Đã hủy'
        ];
        
        $data[] = [
            'id'                     => $order['id'],
            'order_code'             => '#' . $order['id'],   // tạo mã từ id
            'created_at'             => $order['created_at'],
            'status_slug'            => $order['status'],
            'status_name'            => $statusMap[$order['status']] ?? $order['status'],
            'final_amount_formatted' => number_format($order['final_total'], 0, ',', '.'),
            'items'                  => $formattedItems
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}
    /**
     * API hủy đơn hàng (chỉ cho phép khi status pending hoặc processing)
     */
    public function apiCancel()
    {
        header('Content-Type: application/json');
        AuthMiddleware::handle(true);
        $userId = $this->getUserId();
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }

        $orderId = $_POST['order_id'] ?? 0;
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getById($orderId);
        if (!$order || $order['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng không hợp lệ']);
            return;
        }

        if (!in_array($order['status'], ['pending', 'processing'])) {
            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng ở trạng thái này']);
            return;
        }

        $updated = $orderModel->updateStatus($orderId, 'cancelled');
        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hủy đơn thất bại']);
        }
    }

    /**
     * API yêu cầu hoàn trả (chuyển từ renting sang completed)
     */
    public function apiReturn()
    {
        header('Content-Type: application/json');
        AuthMiddleware::handle(true);
        $userId = $this->getUserId();
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }

        $orderId = $_POST['order_id'] ?? 0;
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getById($orderId);
        if (!$order || $order['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng không hợp lệ']);
            return;
        }

        if ($order['status'] !== 'renting') {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng không ở trạng thái đang thuê']);
            return;
        }

        $updated = $orderModel->updateStatus($orderId, 'completed');
        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Yêu cầu hoàn trả đã được gửi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu thất bại']);
        }
    }

    /**
     * Chi tiết đơn hàng (trang riêng)
     */
    public function detail($id)
{
    AuthMiddleware::handle();
    $userId = $this->getUserId();
    if (!$userId) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    $orderModel = new Order();
    $order = $orderModel->getById($id);
    if (!$order || $order['user_id'] != $userId) {
        header('Location: ' . BASE_URL . '/orders');
        exit;
    }

    // Lấy địa chỉ giao hàng và chuẩn hóa
    $address = ['full_name' => 'Chưa có', 'phone' => 'Chưa có', 'address_line' => 'Chưa có'];
    if (!empty($order['address_id'])) {
        $addressModel = new Address();
        $addrData = $addressModel->getById($order['address_id']);
        if ($addrData) {
            // Debug: có thể ghi log để xem cấu trúc
            // error_log(print_r($addrData, true));
            
            $address['full_name'] = $addrData['full_name'] ?? ($addrData['name'] ?? 'Chưa có');
            $address['phone'] = $addrData['phone'] ?? 'Chưa có';
            // Ưu tiên address_line, nếu không có thì address, rồi full_address
            $street = $addrData['address_line'] ?? ($addrData['address'] ?? ($addrData['full_address'] ?? ''));
            if (empty($street)) {
                // Nếu vẫn trống, thử lấy từ các trường khác? Hoặc để mặc định
                $street = 'Chưa có';
            }
            $address['address_line'] = $street;
        }
    }
    $order['address'] = $address;

    // Lấy danh sách sản phẩm trong đơn
    $orderItemModel = new OrderItem();
    $order['items'] = $orderItemModel->getByOrder($id);

    // Tính số ngày thuê
    try {
        $rentDate = new \DateTime($order['rent_date']);
        $returnDate = new \DateTime($order['return_date']);
        $order['days'] = max(1, $rentDate->diff($returnDate)->days + 1);
    } catch (\Exception $e) {
        $order['days'] = 1;
    }

    // Tính toán lại tổng tiền
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
    $discount = (float)($order['discount_price'] ?? 0);
    $lateFee = (float)($order['late_fee'] ?? 0);
    $order['final_total'] = $totalRent + $totalDeposit - $discount + $lateFee;
    if ($order['final_total'] < 0) $order['final_total'] = 0;

    $this->view('user/order_detail', ['order' => $order]);
}
    public function success()
    {
        $orderId = $_GET['order_id'] ?? 0;
        $this->view('user/order_success', ['order_id' => $orderId]);
    }
}