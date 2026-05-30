<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Address;
use App\Models\Promotion;
use App\Middlewares\AdminMiddleware;
use Exception; 
use App\Services\EmailService;

class OrderController extends Controller
{
    protected $orderModel;
    protected $orderItemModel;
    protected $productModel;
    protected $addressModel;

    public function __construct()
    {
        AdminMiddleware::check();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->productModel = new Product();
        $this->addressModel = new Address();
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ==================== HIỂN THỊ VIEW  ==================== */
    public function index()
    {
        $orders = $this->orderModel->getAll();
        ob_start();
        require_once __DIR__ . '/../../../views/admin/orders/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Quản lý đơn hàng';
        $activeMenu = 'orders';
        require_once __DIR__ . '/../../../views/layouts/admin.php';
    }

    public function detail()
    {
        $order_id = (int)($_GET['id'] ?? 0);
        if (!$order_id) {
            header('Location: ' . BASE_URL . '/admin/orders');
            exit;
        }

        $data = $this->orderModel->getDetail($order_id);
        if (!$data['order']) {
            header('Location: ' . BASE_URL . '/admin/orders');
            exit;
        }

        $address = null;
        if (!empty($data['order']['address_id'])) {
            $address = $this->addressModel->getById($data['order']['address_id']);
        }

        $this->view('admin/orders/detail', [
            'order'   => $data['order'],
            'items'   => $data['items'],
            'address' => $address
        ]);
    }

    /* ==================== API JSON  ==================== */
    public function getAll()
    {
        $orders = $this->orderModel->getAll();
        return $this->json(['success' => true, 'data' => $orders]);
    }

    public function getPendingPayments()
    {
        $pendingOrders = $this->orderModel->getPendingVerificationOrders();
        return $this->json(['success' => true, 'data' => $pendingOrders]);
    }

    public function getPendingDeposits()
    {
        $pendingDeposits = $this->orderModel->getPendingDepositOrders();
        return $this->json(['success' => true, 'data' => $pendingDeposits]);
    }

    public function apiGetDetail()
    {
        $order_id = (int)($_GET['id'] ?? 0);
        if (!$order_id) {
            return $this->json(['success' => false, 'error' => 'Thiếu mã đơn hàng']);
        }

        $data = $this->orderModel->getDetail($order_id);
        if (!$data['order']) {
            return $this->json(['success' => false, 'error' => 'Đơn hàng không tồn tại']);
        }

        return $this->json([
            'success' => true,
            'order'   => $data['order'],
            'items'   => $data['items']
        ]);
    }

    /* ==================== CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG  ==================== */
    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            return $this->json([
                'success' => false,
                'error' => 'Method không hợp lệ'
            ]);
        }

        $orderId = (int)($_POST['id'] ?? 0);

        $newStatus = $_POST['status'] ?? '';

        $allowed = [
            'pending',
            'confirmed',
            'shipping',
            'renting',
            'returning',
            'completed',
            'cancelled'
        ];

        if (!in_array($newStatus, $allowed)) {

            return $this->json([
                'success' => false,
                'error' => 'Trạng thái không hợp lệ'
            ]);
        }

        $order = $this->orderModel->getById($orderId);

        if (!$order) {

            return $this->json([
                'success' => false,
                'error' => 'Đơn hàng không tồn tại'
            ]);
        }

        $currentStatus = $order['status'];

        $result = false;

        $emailService = new EmailService();

        // XÁC NHẬN ĐƠN
        if ($newStatus == 'confirmed' && $currentStatus == 'pending') {

            $result = $this->orderModel->confirmOrder($orderId);

            if ($result !== true) {

                return $this->json([
                    'success' => false,
                    'error' => is_string($result)
                        ? $result
                        : 'Xác nhận thất bại'
                ]);
            }

            // Gửi mail xác nhận
            $emailService->sendOrderConfirmed($order);

            $result = true;
        }

        // HOÀN TẤT
        elseif ($newStatus == 'completed' && $currentStatus == 'returning') {
            // Xác định ngày trả thực tế (nếu chưa có thì dùng hôm nay)
            $actualReturnDate = $order['actual_return_date'] ?? date('Y-m-d');
            // Tính phí trễ nếu cần
            $lateFee = $order['late_fee'];
            $lateFeeStatus = $order['late_fee_status'] ?? 'paid';

            if ($order['late_fee_status'] !== 'paid') {
                $latestComputedFee = $this->calculateLateFee($order['return_date'], $actualReturnDate);
                $lateFee = $latestComputedFee;
                $lateFeeStatus = $lateFee > 0 ? 'unpaid' : 'paid';
            }

            // Cập nhật actual_return_date và late_fee nếu cần
            if (empty($order['actual_return_date'])) {
                $updateData = [
                    'actual_return_date' => $actualReturnDate
                ];
                if ($order['late_fee_status'] !== 'paid') {
                    $updateData['late_fee'] = $lateFee;
                    $updateData['late_fee_status'] = $lateFeeStatus;
                }
                $this->orderModel->updateOrder($orderId, $updateData);
            } elseif ($order['late_fee_status'] !== 'paid' && $order['late_fee'] != $lateFee) {
                $this->orderModel->updateOrder($orderId, [
                    'late_fee' => $lateFee,
                    'late_fee_status' => $lateFeeStatus
                ]);
            }

            // Gọi completeReturn (cập nhật kho, status = completed, ...)
            $result = $this->orderModel->completeReturn($orderId);

            if ($result) {
                $emailService->sendOrderCompleted($order);
            }
        }
        // HỦY ĐƠN
        elseif (
            $newStatus == 'cancelled'
            && in_array($currentStatus, ['pending', 'confirmed'])
        ) {

            if ($currentStatus == 'confirmed') {

                $this->orderModel->cancelConfirmedOrder($orderId);
            }

            $result = $this->orderModel->updateStatus(
                $orderId,
                'cancelled'
            );
        }

        // CÁC STATUS KHÁC
        else {

            $result = $this->orderModel->updateStatus(
                $orderId,
                $newStatus
            );
        }

        if ($result) {

            return $this->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        }

        return $this->json([
            'success' => false,
            'error' => 'Cập nhật thất bại'
        ]);
    }

    /* ==================== XỬ LÝ TRẢ HÀNG CHI TIẾT SẢN PHẨM  ==================== */
    public function processReturn()
    {
        $detailId = (int)($_POST['detail_id'] ?? 0);
        $condition = $_POST['condition'] ?? '';
        $lateDays = (int)($_POST['late_days'] ?? 0);

        if (!$detailId || !in_array($condition, ['good', 'damaged', 'lost'])) {
            return $this->json(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
        }

        $item = $this->orderItemModel->find($detailId);
        if (!$item) {
            return $this->json(['success' => false, 'error' => 'Không tìm thấy chi tiết đơn hàng']);
        }

        $conn = $this->orderItemModel->getConnection();
        $conn->begin_transaction();
        try {
            // Chỉ cập nhật tình trạng sản phẩm
            $this->orderItemModel->updateReturnStatus($detailId, $condition);

            // Lấy thông tin đơn hàng
            $order = $this->orderModel->getById($item['order_id']);
            $expectedDate = $order['return_date'];
            $actualDate = $order['actual_return_date'] ?? date('Y-m-d');
            $expected = new \DateTime($expectedDate);
            $actual = new \DateTime($actualDate);
            $daysLate = ($actual > $expected) ? $actual->diff($expected)->days : 0;
            if ($daysLate > 0 && $order['late_fee_status'] !== 'paid') {
                $lateFee = $daysLate * 50000; // hoặc tính theo % total_price
                $stmt = $conn->prepare("UPDATE orders SET late_fee = ?, late_fee_status = 'unpaid' WHERE id = ?");
                $stmt->bind_param("di", $lateFee, $item['order_id']);
                $stmt->execute();
            }

            $remaining = $this->orderItemModel->countNotReturnedByOrder($item['order_id']);
            if ($remaining == 0) {
                if ($order['status'] !== 'completed') {
                    $this->orderModel->updateStatus($item['order_id'], 'returning');
                }
                if ($order['status'] == 'completed' && $order['deposit_status'] == 'pending') {
                    $this->orderModel->updateDepositStatus($item['order_id'], 'pending_return');
                }
            }

            $conn->commit();
            return $this->json(['success' => true, 'message' => 'Cập nhật tình trạng sản phẩm thành công']);
        } catch (\Exception $e) {
            $conn->rollback();
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function updateActualReturn()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $actualReturnDate = trim($_POST['actual_return_date'] ?? '');

        if (!$orderId) {
            return $this->json(['success' => false, 'error' => 'Thiếu mã đơn hàng']);
        }

        if (empty($actualReturnDate)) {
            $actualReturnDate = date('Y-m-d');
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order) {
            return $this->json(['success' => false, 'error' => 'Đơn hàng không tồn tại']);
        }

        if (!in_array($order['status'], ['confirmed', 'renting', 'returning'])) {
            return $this->json(['success' => false, 'error' => 'Đơn hàng không ở trạng thái hợp lệ để cập nhật ngày trả thực tế']);
        }

        if (!empty($order['actual_return_date'])) {
            return $this->json(['success' => false, 'error' => 'Đơn hàng đã có ngày trả thực tế']);
        }

        $expected = new \DateTime($order['return_date']);
        $actual = new \DateTime($actualReturnDate);

        $lateFee = $order['late_fee'] ?? 0;
        $lateFeeStatus = $order['late_fee_status'] ?? 'paid';
        if ($order['late_fee_status'] !== 'paid') {
            if ($actual > $expected) {
                $daysLate = $actual->diff($expected)->days;
                $lateFee = $daysLate * 50000;
                $lateFeeStatus = 'unpaid';
            } else {
                $lateFee = 0;
                $lateFeeStatus = 'paid';
            }
        }

        $conn = $this->orderModel->getConnection();
        $conn->begin_transaction();
        try {
            $items = $this->orderItemModel->getByOrder($orderId);
            foreach ($items as $item) {
                $this->productModel->decreaseVariantRentedQuantity(
                    $item['product_id'],
                    $item['color'] ?? '',
                    $item['size'] ?? '',
                    $item['quantity']
                );
            }

            $sql = "UPDATE orders SET actual_return_date = ?, late_fee = ?, late_fee_status = ?, status = 'returning' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sdsi', $actualReturnDate, $lateFee, $lateFeeStatus, $orderId);
            $stmt->execute();

            $conn->commit();
            return $this->json([
                'success' => true,
                'message' => 'Đã cập nhật ngày trả thực tế. Phí trễ: ' . number_format($lateFee) . 'đ',
                'late_fee' => $lateFee,
                'late_fee_status' => $lateFeeStatus
            ]);
        } catch (\Exception $e) {
            $conn->rollback();
            return $this->json(['success' => false, 'error' => 'Lỗi cập nhật: ' . $e->getMessage()]);
        }
    }

    /* ==================== XÁC NHẬN THANH TOÁN  ==================== */
    public function approvePayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            return $this->json(['success' => false, 'error' => 'ID đơn hàng không hợp lệ']);
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order || $order['payment_status'] != 'pending_verification') {
            return $this->json(['success' => false, 'error' => 'Đơn hàng không ở trạng thái chờ thanh toán']);
        }

        $conn = $this->orderModel->getConnection();
        $sql = "UPDATE orders SET payment_status = 'paid', payment_verified_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $orderId);
        $updated = $stmt->execute();

        if ($updated) {
            return $this->json(['success' => true, 'message' => 'Đã xác nhận thanh toán thành công']);
        } else {
            return $this->json(['success' => false, 'error' => 'Cập nhật thất bại']);
        }
    }

    /* ==================== XỬ LÝ PHÍ TRỄ  ==================== */
    public function updateLateFeeStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['unpaid', 'paid'])) {
            return $this->json(['success' => false, 'error' => 'Trạng thái không hợp lệ']);
        }
        $updated = $this->orderModel->updateLateFeeStatus($orderId, $status);
        if ($updated) {
            return $this->json(['success' => true, 'message' => 'Cập nhật trạng thái phí trễ thành công']);
        } else {
            return $this->json(['success' => false, 'error' => 'Cập nhật thất bại']);
        }
    }

    public function markLateFeePaid()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        $orderId = (int)($_POST['order_id'] ?? 0);
        if (!$orderId) {
            return $this->json(['success' => false, 'error' => 'Thiếu mã đơn hàng']);
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order) {
            return $this->json(['success' => false, 'error' => 'Đơn hàng không tồn tại']);
        }

        if ((float)$order['late_fee'] <= 0) {
            return $this->json(['success' => false, 'error' => 'Đơn hàng không có phí trễ']);
        }

        $currentStatus = $order['late_fee_status'];
        if (!in_array($currentStatus, ['unpaid', 'pending_verification'])) {
            return $this->json(['success' => false, 'error' => 'Phí trễ đã được thanh toán hoặc không ở trạng thái chờ']);
        }

        $conn = $this->orderModel->getConnection();
        $conn->begin_transaction();
        try {
            // Cập nhật phí trễ
            $sql = "UPDATE orders SET late_fee_status = 'paid', late_fee_paid_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $orderId);
            $stmt->execute();

            // Nếu đơn hàng đã hoàn tất (completed) và deposit_status đang là 'pending', chuyển thành 'pending_return'
            if ($order['status'] == 'completed' && $order['deposit_status'] == 'pending') {
                $sql2 = "UPDATE orders SET deposit_status = 'pending_return' WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param('i', $orderId);
                $stmt2->execute();
            }

            $conn->commit();
            return $this->json(['success' => true, 'message' => 'Đã xác nhận thanh toán phí trễ và cập nhật trạng thái hoàn cọc']);
        } catch (\Exception $e) {
            $conn->rollback();
            return $this->json(['success' => false, 'error' => 'Cập nhật thất bại: ' . $e->getMessage()]);
        }
    }

    /* ==================== XỬ LÝ HOÀN CỌC  ==================== */
    public function processDeposit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            return $this->json(['success' => false, 'error' => 'ID đơn hàng không hợp lệ']);
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order || !in_array($order['status'], ['returning', 'completed']) || $order['deposit_status'] != 'pending_return') {
            return $this->json(['success' => false, 'error' => 'Đơn hàng không ở trạng thái chờ hoàn cọc']);
        }

        if ($order['late_fee'] > 0 && $order['late_fee_status'] !== 'paid') {
            return $this->json(['success' => false, 'error' => 'Khách hàng chưa thanh toán phí trễ. Vui lòng xác nhận phí trễ trước khi hoàn cọc.']);
        }

        $items = $this->orderItemModel->getByOrder($orderId);
        $totalDeposit = (float)$order['deposit_amount'];
        $totalPenalty = 0;
        $allGood = true;

        // Biến để lưu thông tin cập nhật kho
        $productModel = $this->productModel;

        $conn = $this->orderModel->getConnection();
        $conn->begin_transaction();
        try {
            foreach ($items as $item) {
                $status = $item['status_after_return'] ?? 'good';
                $depositPerItem = (float)($item['deposit_per_item'] ?? 0);
                $quantity = (int)$item['quantity'];
                $itemDeposit = $depositPerItem * $quantity;

                if ($status == 'damaged') {
                    $totalPenalty += $itemDeposit * 0.5;
                    $allGood = false;
                    // Cập nhật kho: giảm rented_quantity và giảm stock (do hỏng)
                    $productModel->decreaseVariantRentedQuantity(
                        $item['product_id'],
                        $item['color'] ?? '',
                        $item['size'] ?? '',
                        $quantity
                    );
                    $productModel->decreaseStock($item['product_id'], $quantity);
                } elseif ($status == 'lost') {
                    $totalPenalty += $itemDeposit;
                    $allGood = false;
                    // Mất: giảm rented_quantity và giảm stock
                    $productModel->decreaseVariantRentedQuantity(
                        $item['product_id'],
                        $item['color'] ?? '',
                        $item['size'] ?? '',
                        $quantity
                    );
                    $productModel->decreaseStock($item['product_id'], $quantity);
                } else { // good
                    // Chỉ giảm rented_quantity, không giảm stock
                    $productModel->decreaseVariantRentedQuantity(
                        $item['product_id'],
                        $item['color'] ?? '',
                        $item['size'] ?? '',
                        $quantity
                    );
                }
            }

            $refundAmount = max(0, $totalDeposit - $totalPenalty);
            if ($allGood) {
                $newStatus = 'refunded_full';
            } elseif ($refundAmount > 0) {
                $newStatus = 'refunded_partial';
            } else {
                $newStatus = 'forfeited';
            }

            $updated = $this->orderModel->updateDepositStatus($orderId, $newStatus, $refundAmount);
            if (!$updated) {
                throw new \Exception('Cập nhật deposit status thất bại');
            }

            if ($order['status'] !== 'completed') {
                $this->orderModel->updateStatus($orderId, 'completed');
            }

            $conn->commit();
            return $this->json([
                'success' => true,
                'message' => 'Hoàn cọc thành công! Số tiền hoàn: ' . number_format($refundAmount) . 'đ',
                'refund_amount' => $refundAmount,
                'penalty' => $totalPenalty,
                'deposit_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            $conn->rollback();
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /* ==================== THAO TÁC HÀNG LOẠT  ==================== */
    public function batchApprove()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderIds = $input['order_ids'] ?? [];
        if (empty($orderIds)) {
            return $this->json(['success' => false, 'error' => 'Không có đơn hàng được chọn']);
        }

        $approved = 0;
        $skipped = 0;
        $emailService = new \App\Services\EmailService();

        foreach ($orderIds as $id) {
            $order = $this->orderModel->getById($id);
            if ($order && in_array($order['status'], ['pending', 'waiting_confirm'])) {
                $result = $this->orderModel->confirmOrder($id);
                if ($result === true) {
                    $approved++;
                    // Gửi email xác nhận
                    try {
                        $emailService->sendOrderConfirmed($order);
                        error_log("Email confirmed sent for order #{$id}");
                    } catch (\Exception $e) {
                        error_log("Email error for order #{$id}: " . $e->getMessage());
                    }
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }
        return $this->json(['success' => true, 'approved' => $approved, 'skipped' => $skipped]);
    }
    
    public function batchCancel()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderIds = $input['order_ids'] ?? [];
        if (empty($orderIds)) {
            return $this->json(['success' => false, 'error' => 'Không có đơn hàng được chọn']);
        }

        $cancelled = 0;
        foreach ($orderIds as $id) {
            $order = $this->orderModel->getById($id);
            if ($order && in_array($order['status'], ['pending', 'confirmed'])) {
                if ($order['status'] == 'confirmed') {
                    $this->orderModel->cancelConfirmedOrder($id);
                }
                if ($this->orderModel->updateStatus($id, 'cancelled')) $cancelled++;
            }
        }
        return $this->json(['success' => true, 'cancelled' => $cancelled]);
    }

    /* ==================== HÀM HỖ TRỢ  ==================== */
    private function calculateLateFee($expectedReturnDate, $actualReturnDate)
    {
        $expected = new \DateTime($expectedReturnDate);
        $actual = new \DateTime($actualReturnDate);
        if ($actual <= $expected) return 0;
        $daysLate = $actual->diff($expected)->days;
        return $daysLate * 50000;
    }
}