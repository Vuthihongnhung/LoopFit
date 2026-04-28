<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Address;
use App\Models\Promotion;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class CheckoutController extends Controller
{
    private function getUserId()
    {
        return $_SESSION['user']['id'] ?? 0;
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function addresses()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Chưa đăng nhập']);
        }
        $addressModel = new Address();
        $addresses = $addressModel->getByUser($userId);
        $this->jsonResponse(['success' => true, 'data' => $addresses]);
    }

    public function addAddress()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Chưa đăng nhập']);
        }
        $data = $_POST;
        $data['user_id'] = $userId;
        $addressModel = new Address();
        $result = $addressModel->create($data);
        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Thêm địa chỉ thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Thêm địa chỉ thất bại']);
        }
    }

    public function index()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $cartIds = isset($_GET['cart_ids']) ? explode(',', $_GET['cart_ids']) : [];
        if (empty($cartIds)) {
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }

        $cartModel = new Cart();
        $productModel = new Product();
        $items = [];

        foreach ($cartIds as $cartId) {
            $cartItem = $cartModel->findById($cartId, $userId);
            if (!$cartItem) continue;

            $product = $productModel->getById($cartItem['product_id']);
            if (!$product) continue;

            $rentDate = $cartItem['rent_date'];
            $returnDate = $cartItem['return_date'];
            $days = max(1, (new \DateTime($rentDate))->diff(new \DateTime($returnDate))->days + 1);
            $pricePerDay = (float)$product['price'];
            $depositPerItem = (float)($product['deposit_price'] ?? $pricePerDay * 0.3);
            $quantity = (int)$cartItem['quantity'];

            $subtotal = $pricePerDay * $quantity * $days;
            $depositTotal = $depositPerItem * $quantity;

            $items[] = [
                'cart_id'       => $cartId,
                'product_id'    => $product['id'],
                'name'          => $product['name'],
                'price'         => $pricePerDay,
                'deposit_price' => $depositPerItem,
                'quantity'      => $quantity,
                'size'          => $cartItem['size'],
                'color'         => $cartItem['color'],
                'rent_date'     => $rentDate,
                'return_date'   => $returnDate,
                'days'          => $days,
                'subtotal'      => $subtotal,
                'deposit_total' => $depositTotal,
                'image'         => $product['image']
            ];
        }

        if (empty($items)) {
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }

        $totalRent = array_sum(array_column($items, 'subtotal'));
        $totalDeposit = array_sum(array_column($items, 'deposit_total'));

        $addressModel = new Address();
        $addresses = $addressModel->getByUser($userId);

        $promotionModel = new Promotion();
        $vouchers = $promotionModel->getActiveVouchers();

        $this->view('user/checkout', [
            'items'         => $items,
            'total_rent'    => $totalRent,
            'total_deposit' => $totalDeposit,
            'addresses'     => $addresses,
            'vouchers'      => $vouchers
        ]);
    }

    public function applyVoucher()
    {
        header('Content-Type: application/json');
        
        $code = $_POST['code'] ?? '';
        $totalAmount = (float)($_POST['total_amount'] ?? 0);
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã']);
            return;
        }
        
        $model = new Promotion();
        $voucher = $model->getVoucherByCode($code);
        
        if (!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Mã không hợp lệ hoặc đã hết hạn']);
            return;
        }
        
        $discount = 0;
        if (!empty($voucher['discount_percent']) && $voucher['discount_percent'] > 0) {
            $discount = $totalAmount * $voucher['discount_percent'] / 100;
            if (!empty($voucher['max_discount']) && $voucher['max_discount'] > 0) {
                $discount = min($discount, $voucher['max_discount']);
            }
        } elseif (!empty($voucher['discount_amount']) && $voucher['discount_amount'] > 0) {
            $discount = min($voucher['discount_amount'], $totalAmount);
        }
        
        $_SESSION['voucher_discount'] = $discount;
        $_SESSION['voucher_code'] = $code;
        
        echo json_encode([
            'success' => true,
            'discount' => $discount,
            'message' => 'Áp dụng thành công! Giảm ' . number_format($discount) . 'đ'
        ]);
    }

    public function process()
    {
        header('Content-Type: application/json');
        
        $userId = $this->getUserId();
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }
        
        $addressId = (int)($_POST['address_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        if (!in_array($paymentMethod, ['cash', 'transfer'])) {
            $paymentMethod = 'cash';
        }
        $notes = trim($_POST['notes'] ?? '');
        
        $discount = (float)($_SESSION['voucher_discount'] ?? 0);
        $shippingFee = (float)($_POST['shipping_fee'] ?? 0);
        $cartIds = $_POST['cart_ids'] ?? '';
        
        if ($addressId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Chưa chọn địa chỉ nhận hàng']);
            return;
        }
        
        if (empty($cartIds)) {
            echo json_encode(['success' => false, 'message' => 'Không có sản phẩm nào được chọn']);
            return;
        }
        
        $cartIdArray = explode(',', $cartIds);
        $cartModel = new Cart();
        $items = [];
        $minRentDate = null;
        $maxReturnDate = null;
        
        foreach ($cartIdArray as $cartId) {
            $item = $cartModel->findById((int)$cartId, $userId);
            if ($item) {
                $productModel = new Product();
                $product = $productModel->getById($item['product_id']);
                if ($product) {
                    $item['name'] = $product['name'];
                    $item['price'] = $product['price'];
                    $item['deposit_price'] = $product['deposit_price'] ?? ($product['price'] * 0.3);
                    $item['image'] = $product['image'];
                    $items[] = $item;
                    
                    $rentDate = new \DateTime($item['rent_date']);
                    $returnDate = new \DateTime($item['return_date']);
                    if ($minRentDate === null || $rentDate < $minRentDate) $minRentDate = $rentDate;
                    if ($maxReturnDate === null || $returnDate > $maxReturnDate) $maxReturnDate = $returnDate;
                }
            }
        }
        
        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            return;
        }
        
        $totalRent = 0;
        $totalDeposit = 0;
        $orderItemsData = [];
        
        foreach ($items as &$item) {
            $rentDate = new \DateTime($item['rent_date']);
            $returnDate = new \DateTime($item['return_date']);
            $days = max(1, $rentDate->diff($returnDate)->days + 1);
            $price = (float)$item['price'];
            $depositPrice = (float)($item['deposit_price'] ?? $price * 0.3);
            $quantity = (int)$item['quantity'];
            
            $subtotal = $price * $quantity * $days;
            $depositTotal = $depositPrice * $quantity;
            
            $item['days'] = $days;
            $item['subtotal'] = $subtotal;
            $item['deposit'] = $depositTotal;
            
            $totalRent += $subtotal;
            $totalDeposit += $depositTotal;
            
            $orderItemsData[] = [
                'product_id'       => $item['product_id'],
                'quantity'         => $quantity,
                'price_per_day'    => $price,
                'deposit_per_item' => $depositPrice,
                'color'            => $item['color'] ?? '',
                'size'             => $item['size'] ?? '',
                'rental_start'     => $item['rent_date'],
                'rental_end'       => $item['return_date'],
                'subtotal'         => $subtotal
            ];
        }
        
        $finalTotal = max(0, $totalRent + $totalDeposit - $discount + $shippingFee);
        
        $paymentExpiry = null;
        if ($paymentMethod === 'transfer') {
            $paymentExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        }
        $orderData['payment_expiry'] = $paymentExpiry;
        
        // Kiểm tra address_id có tồn tại không (tránh lỗi foreign key)
        $addressModel = new Address();
        $address = $addressModel->getById($addressId);
        if (!$address || $address['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Địa chỉ không hợp lệ']);
            return;
        }
        
        $orderModel = new Order();
        $orderData = [
            'user_id'         => $userId,
            'address_id'      => $addressId,
            'rent_date'       => $minRentDate ? $minRentDate->format('Y-m-d') : null,
            'return_date'     => $maxReturnDate ? $maxReturnDate->format('Y-m-d') : null,
            'total_price'     => $totalRent,
            'discount_price'  => $discount,
            'shipping_fee'    => $shippingFee,
            'deposit_amount'  => $totalDeposit,
            'final_total'     => $finalTotal,
            'payment_method'  => $paymentMethod,
            'notes'           => $notes,
            'status'          => 'pending',
            'payment_status'  => 'unpaid',
            'deposit_status'  => 'pending',
            'payment_expiry'  => $paymentExpiry
        ];
        
        // Debug: ghi log dữ liệu (có thể xóa sau)
        error_log("Order data: " . print_r($orderData, true));
        
        $result = $orderModel->create($orderData);
        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => 'Không thể tạo đơn hàng: ' . $result['message']]);
            return;
        }
        $orderId = $result['id'];
        
        $orderItemModel = new OrderItem();
        foreach ($orderItemsData as $itemData) {
            $itemData['order_id'] = $orderId;
            $ok = $orderItemModel->create($itemData);
            if (!$ok) {
                $orderModel->delete($orderId);
                echo json_encode(['success' => false, 'message' => 'Lỗi lưu chi tiết đơn hàng']);
                return;
            }
        }
        
        if ($paymentMethod === 'cash') {
            foreach ($cartIdArray as $cartId) {
                $cartModel->removeById((int)$cartId, $userId);
            }
        } else {
            $_SESSION['pending_order_cart_ids'] = $cartIdArray;
            $_SESSION['pending_order_id'] = $orderId;
        }
        
        unset($_SESSION['voucher_discount']);
        unset($_SESSION['voucher_code']);
        
        $expiryMs = null;
        if ($paymentExpiry) {
            $expiryMs = strtotime($paymentExpiry) * 1000;
        }
        
        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'amount' => $finalTotal,
            'expiry' => $expiryMs,
            'payment_method' => $paymentMethod
        ]);
    }

    public function showQR()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
        if (!$orderId) {
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }

        $orderModel = new Order();
        $order = $orderModel->getById($orderId);
        if (!$order || $order['user_id'] != $userId || $order['payment_method'] !== 'transfer') {
            header('Location: ' . BASE_URL . '/user/orders');
            exit;
        }

        if ($order['payment_status'] === 'paid') {
            header('Location: ' . BASE_URL . '/user/orders');
            exit;
        }

        $this->view('user/payment_qr', [
            'order_id' => $order['id'],
            'total_amount' => (float)$order['final_total']
        ]);
    }

    public function confirmPayment()
    {
        header('Content-Type: application/json');
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

        if ($order['payment_method'] == 'transfer' && !empty($order['payment_expiry'])) {
            $expiryTime = strtotime($order['payment_expiry']);
            if ($expiryTime < time()) {
                echo json_encode(['success' => false, 'message' => 'Quá thời gian thanh toán (15 phút). Vui lòng đặt lại đơn hàng.']);
                return;
            }
        }

        $updated = $orderModel->updatePaymentStatus($orderId, 'paid');
        if (!$updated) {
            echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái đơn hàng thất bại']);
            return;
        }

        $paymentModel = new Payment();
        $paymentData = [
            'order_id' => $orderId,
            'amount'   => $order['final_total'],
            'method'   => $order['payment_method'] ?? 'transfer',
            'status'   => 'completed'
        ];
        $inserted = $paymentModel->create($paymentData);
        if (!$inserted) {
            $orderModel->updatePaymentStatus($orderId, 'unpaid');
            echo json_encode(['success' => false, 'message' => 'Ghi nhận thanh toán thất bại, vui lòng thử lại']);
            return;
        }

        if (isset($_SESSION['pending_order_cart_ids']) && isset($_SESSION['pending_order_id']) && $_SESSION['pending_order_id'] == $orderId) {
            $cartIds = $_SESSION['pending_order_cart_ids'];
            $cartModel = new Cart();
            foreach ($cartIds as $cartId) {
                $cartModel->removeById((int)$cartId, $userId);
            }
            unset($_SESSION['pending_order_cart_ids']);
            unset($_SESSION['pending_order_id']);
        }

        echo json_encode(['success' => true, 'message' => 'Xác nhận thanh toán thành công']);
    }
}