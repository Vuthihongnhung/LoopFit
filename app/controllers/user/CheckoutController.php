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

    /* ===== API: Lấy danh sách địa chỉ của người dùng ===== */
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

    /* ===== API: Thêm địa chỉ mới cho người dùng ===== */
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

    /* ===== Hiển thị trang thanh toán ===== */
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
            $cartItem = $cartModel->findById((int)$cartId, $userId);
            if (!$cartItem) continue;

            $product = $productModel->getById($cartItem['product_id']);
            if (!$product) continue;

            $rentDate = $cartItem['rent_date'];
            $returnDate = $cartItem['return_date'];
            $diff = (new \DateTime($rentDate))->diff(new \DateTime($returnDate));
            $days = max(1, $diff->days + 1);

            $pricePerDay = (float)($cartItem['price'] ?? $product['price']);
            $depositPerItem = (float)($product['deposit_price'] ?? $product['price'] * 0.3);
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

    /* ===== API: Áp dụng mã giảm giá ===== */
    public function applyVoucher()
    {
        header('Content-Type: application/json');
        
        $code = $_POST['code'] ?? '';
        $totalRent = (float)($_POST['total_amount'] ?? 0);
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã']);
            return;
        }
        
        $model = new Promotion();
        $voucher = $model->getVoucherByCode($code);
        
        if (!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Mã không hợp lệ']);
            return;
        }
        
        // Kiểm tra số lượt sử dụng còn lại
        if ($voucher['usage_limit'] > 0 && $voucher['used_count'] >= $voucher['usage_limit']) {
            echo json_encode(['success' => false, 'message' => 'Mã đã hết lượt sử dụng']);
            return;
        }
        
        // Kiểm tra thời gian hiệu lực
        $now = date('Y-m-d H:i:s');
        if ($voucher['start_date'] && $voucher['start_date'] > $now) {
            echo json_encode(['success' => false, 'message' => 'Mã chưa có hiệu lực']);
            return;
        }
        if ($voucher['end_date'] && $voucher['end_date'] < $now) {
            echo json_encode(['success' => false, 'message' => 'Mã đã hết hạn']);
            return;
        }
        
        // Kiểm tra điều kiện đơn hàng tối thiểu
        $minOrderValue = (float)($voucher['min_order_value'] ?? 0);
        if ($minOrderValue > 0 && $totalRent < $minOrderValue) {
            echo json_encode([
                'success' => false,
                'message' => 'Đơn hàng tối thiểu ' . number_format($minOrderValue) . 'đ mới được áp dụng mã này.'
            ]);
            return;
        }
        
        $discount = 0;
        if (!empty($voucher['discount_percent']) && $voucher['discount_percent'] > 0) {
            $discount = $totalRent * ($voucher['discount_percent'] / 100);
            if (!empty($voucher['max_discount']) && $discount > $voucher['max_discount']) {
                $discount = $voucher['max_discount'];
            }
        } elseif (!empty($voucher['discount_amount']) && $voucher['discount_amount'] > 0) {
            $discount = min($voucher['discount_amount'], $totalRent);
        }
        
        $_SESSION['voucher_discount'] = $discount;
        $_SESSION['voucher_code'] = $code;
        $_SESSION['voucher_id'] = $voucher['id'];
        
        echo json_encode([
            'success' => true,
            'discount' => $discount,
            'message' => 'Áp dụng thành công! Giảm ' . number_format($discount) . 'đ'
        ]);
    }

    /* ===== Xử lý tạo đơn hàng ===== */
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
        
        $cartIdArray = is_array($cartIds) ? $cartIds : explode(',', $cartIds);
        $cartModel = new Cart();
        $productModel = new Product();
        $items = [];
        
        $standardRentDate = null;
        $standardReturnDate = null;
        
        // Lấy thông tin từng sản phẩm trong giỏ, đồng thời kiểm tra cùng ngày thuê/trả
        foreach ($cartIdArray as $cartId) {
            $cartItem = $cartModel->findById((int)$cartId, $userId);
            if (!$cartItem) continue;
            
            $product = $productModel->getById($cartItem['product_id']);
            if (!$product) continue;
            
            $rentDate = $cartItem['rent_date'];
            $returnDate = $cartItem['return_date'];
            
            if ($standardRentDate === null) {
                $standardRentDate = $rentDate;
                $standardReturnDate = $returnDate;
            } else {
                if ($rentDate != $standardRentDate || $returnDate != $standardReturnDate) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Các sản phẩm được chọn phải có cùng ngày thuê và ngày trả. Vui lòng kiểm tra lại giỏ hàng.'
                    ]);
                    return;
                }
            }
        }
        
        if ($standardRentDate === null) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống hoặc sản phẩm không hợp lệ']);
            return;
        }
        
        // ==================== KIỂM TRA TỒN KHO THEO THỜI GIAN ====================
        // Tạo mảng dữ liệu chi tiết các sản phẩm và kiểm tra số lượng khả dụng
        $itemsData = [];
        $totalRent = 0;
        $totalDeposit = 0;
        
        foreach ($cartIdArray as $cartId) {
            $cartItem = $cartModel->findById((int)$cartId, $userId);
            if (!$cartItem) continue;
            
            $product = $productModel->getById($cartItem['product_id']);
            if (!$product) continue;
            
            $usedPrice = (float)($cartItem['price'] ?? $product['price']);
            $depositPrice = (float)($product['deposit_price'] ?? $product['price'] * 0.3);
            
            $rentDateObj = new \DateTime($cartItem['rent_date']);
            $returnDateObj = new \DateTime($cartItem['return_date']);
            $days = max(1, $rentDateObj->diff($returnDateObj)->days + 1);
            $quantity = (int)$cartItem['quantity'];
            
            // Kiểm tra số lượng khả dụng trong khoảng thời gian này
            $available = $productModel->getAvailableQuantity($product['id'], $cartItem['rent_date'], $cartItem['return_date']);
            if ($available < $quantity) {
                echo json_encode([
                    'success' => false,
                    'message' => "Sản phẩm \"{$product['name']}\" chỉ còn {$available} cái có thể thuê trong khoảng {$cartItem['rent_date']} - {$cartItem['return_date']}. Vui lòng giảm số lượng hoặc chọn ngày khác."
                ]);
                return;
            }
            
            $subtotal = $usedPrice * $quantity * $days;
            $depositTotal = $depositPrice * $quantity;
            
            $itemsData[] = [
                'cart_id'      => $cartItem['id'],
                'product_id'   => $product['id'],
                'quantity'     => $quantity,
                'size'         => $cartItem['size'],
                'color'        => $cartItem['color'],
                'rent_date'    => $cartItem['rent_date'],
                'return_date'  => $cartItem['return_date'],
                'days'         => $days,
                'price_per_day'=> $usedPrice,
                'deposit_price'=> $depositPrice,
                'subtotal'     => $subtotal,
                'deposit_total'=> $depositTotal,
                'product_name' => $product['name'],
                'product_image'=> $product['image']
            ];
            
            $totalRent += $subtotal;
            $totalDeposit += $depositTotal;
        }
        
        if (empty($itemsData)) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            return;
        }
        
        if ($discount > $totalRent) $discount = $totalRent;
        
        $finalTotal = $totalRent + $totalDeposit + $shippingFee - $discount;
        if ($finalTotal < 0) $finalTotal = 0;
        
        $paymentExpiry = null;
        if ($paymentMethod === 'transfer') {
            $paymentExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        }
        
        $addressModel = new Address();
        $address = $addressModel->getById($addressId);
        if (!$address || $address['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Địa chỉ không hợp lệ']);
            return;
        }
        
        // Tạo đơn hàng (trạng thái pending, chưa trừ kho)
        $orderModel = new Order();
        $orderData = [
            'user_id'         => $userId,
            'address_id'      => $addressId,
            'rent_date'       => $standardRentDate,
            'return_date'     => $standardReturnDate,
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
            'payment_expiry'  => $paymentExpiry,
            'promotion_id'    => $_SESSION['voucher_id'] ?? null,
            'late_fee'        => 0,
            'late_fee_status' => 'unpaid',
            'late_fee_paid_at'=> null
        ];
        
        error_log("Order data: " . print_r($orderData, true));
        
        $result = $orderModel->create($orderData);
        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => 'Không thể tạo đơn hàng: ' . $result['message']]);
            return;
        }
        $orderId = $result['id'];
        
        // Lưu chi tiết đơn hàng 
        $orderItemModel = new OrderItem();
        foreach ($itemsData as $item) {
            $itemData = [
                'order_id'         => $orderId,
                'product_id'       => $item['product_id'],
                'quantity'         => $item['quantity'],
                'price'            => $item['price_per_day'],
                'deposit_per_item' => $item['deposit_price'],
                'color'            => $item['color'],
                'size'             => $item['size'],
                'rental_start'     => $item['rent_date'],
                'rental_end'       => $item['return_date'],
                'subtotal'         => $item['subtotal']
            ];
            $ok = $orderItemModel->create($itemData);
            if (!$ok) {
                $orderModel->delete($orderId);
                echo json_encode(['success' => false, 'message' => 'Lỗi lưu chi tiết đơn hàng']);
                return;
            }
        }
        
        // Cập nhật used_count cho promotion đã dùng
        $voucherCode = $_SESSION['voucher_code'] ?? null;
        if ($voucherCode) {
            $promoModel = new Promotion();
            $promoModel->incrementUsedCount($voucherCode);
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
        unset($_SESSION['voucher_id']);
        
        $expiryMs = $paymentExpiry ? strtotime($paymentExpiry) * 1000 : null;
        
        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'amount' => $finalTotal,
            'expiry' => $expiryMs,
            'payment_method' => $paymentMethod
        ]);
    }

    /* ===== Hiển thị trang QR code cho thanh toán chuyển khoản ===== */
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

    /* ===== API: Xác nhận thanh toán (sau khi người dùng quét QR) ===== */
    public function confirmPayment()
    {
        header('Content-Type: application/json');
        $userId = $this->getUserId();
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }
        $orderId = (int)($_POST['order_id'] ?? 0);
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

        $updated = $orderModel->updatePaymentStatus($orderId, 'pending_verification');
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

        // Xóa giỏ hàng sau khi xác nhận thanh toán
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