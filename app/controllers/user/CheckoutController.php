<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Address;
use App\Models\Promotion;
use App\Models\Order;
use App\Models\OrderDetail;
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

    public function index()
{
    $userId = $this->getUserId();
    if (!$userId) {
        header('Location: /LoopFit/public/login');
        exit;
    }

    $cartIds = isset($_GET['cart_ids']) ? explode(',', $_GET['cart_ids']) : [];
    if (empty($cartIds)) {
        header('Location: /LoopFit/public/cart');
        exit;
    }

    $cartModel = new Cart();
    $productModel = new Product();
    $items = [];
    $totalRent = 0;
    $totalDeposit = 0;

    foreach ($cartIds as $cartId) {
        $cartItem = $cartModel->findById($cartId, $userId);
        if (!$cartItem) continue;

        $product = $productModel->getById($cartItem['product_id']);
        $rentDate = $cartItem['rent_date'];
        $returnDate = $cartItem['return_date'];
        $days = (new \DateTime($rentDate))->diff(new \DateTime($returnDate))->days + 1;
        $subtotal = $product['price'] * $cartItem['quantity'] * $days;
        $deposit = ($product['deposit_price'] ?? $product['price'] * 0.3) * $cartItem['quantity'];

        $items[] = [
            'cart_id'      => $cartId,
            'product_id'   => $product['id'],
            'name'         => $product['name'],
            'price'        => $product['price'],
            'deposit_price'=> $product['deposit_price'] ?? $product['price'] * 0.3,
            'quantity'     => $cartItem['quantity'],
            'size'         => $cartItem['size'],
            'color'        => $cartItem['color'],
            'rent_date'    => $rentDate,
            'return_date'  => $returnDate,
            'days'         => $days,
            'subtotal'     => $subtotal,
            'deposit_total'=> $deposit,
            'image'        => $product['image']
        ];
        $totalRent += $subtotal;
        $totalDeposit += $deposit;
    }

    $addressModel = new Address();
    $addresses = $addressModel->getByUser($userId);

    $promotionModel = new Promotion();
    $vouchers = $promotionModel->getActiveVouchers();

    // Chỉ gọi view một lần với đầy đủ dữ liệu
    $this->view('user/checkout', [
        'items'         => $items,
        'total_rent'    => $totalRent,
        'total_deposit' => $totalDeposit,
        'addresses'     => $addresses,
        'vouchers'      => $vouchers
    ]);
}

    // API áp dụng mã giảm giá
    public function applyVoucher()
{
    header('Content-Type: application/json');
    
    $code = $_POST['code'] ?? '';
    $totalAmount = (float)($_POST['total_amount'] ?? 0);
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn mã']);
        return;
    }
    
    $model = new \App\Models\Promotion();
    $voucher = $model->getVoucherByCode($code);
    
    if (!$voucher) {
        echo json_encode(['success' => false, 'message' => 'Mã không hợp lệ hoặc đã hết hạn']);
        return;
    }
    
    $discount = 0;
    // Ưu tiên discount_percent nếu có
    if (!empty($voucher['discount_percent']) && $voucher['discount_percent'] > 0) {
        $discount = $totalAmount * $voucher['discount_percent'] / 100;
        // Nếu có giới hạn giảm tối đa (max_discount) thì áp dụng
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

    // Xử lý đặt hàng
    public function process() {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $addressId = (int)($_POST['address_id'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'cod';
    $notes = trim($_POST['notes'] ?? '');
    $discount = (float)($_POST['discount'] ?? 0);
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
    $cartModel = new \App\Models\Cart();
    $items = [];
    
    foreach ($cartIdArray as $cartId) {
        $item = $cartModel->findById((int)$cartId, $userId);
        if ($item) {
            $productModel = new \App\Models\Product();
            // SỬA LỖI Ở ĐÂY: getdById -> getById
            $product = $productModel->getById($item['product_id']);
            if ($product) {
                $item['name'] = $product['name'];
                $item['price'] = $product['price'];
                $item['deposit_price'] = $product['deposit_price'] ?? ($product['price'] * 0.3);
                $item['image'] = $product['image'];
                $items[] = $item;
            }
        }
    }
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        return;
    }
    
    // Tính toán chi tiết từng item
    $totalRent = 0;
    $totalDeposit = 0;
    foreach ($items as &$item) {
        $rentDate = new \DateTime($item['rent_date']);
        $returnDate = new \DateTime($item['return_date']);
        $days = $rentDate->diff($returnDate)->days;
        $days = max(1, $days);
        $price = (float)$item['price'];
        $depositPrice = (float)($item['deposit_price'] ?? $price * 0.3);
        
        $item['days'] = $days;
        $item['subtotal'] = $price * $item['quantity'] * $days;
        $item['deposit'] = $depositPrice * $item['quantity'];
        
        $totalRent += $item['subtotal'];
        $totalDeposit += $item['deposit'];
    }
    $finalTotal = $totalRent + $totalDeposit - $discount;
    if ($finalTotal < 0) $finalTotal = 0;
    
    // Xác thực discount (nếu có)
    if ($discount > 0 && (!isset($_SESSION['voucher_discount']) || $_SESSION['voucher_discount'] != $discount)) {
        echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ']);
        return;
    }
    
    // Tạo đơn hàng
    $orderModel = new \App\Models\Order();
    $orderId = $orderModel->create([
        'user_id' => $userId,
        'address_id' => $addressId,
        'payment_method' => $paymentMethod,
        'notes' => $notes,
        'total_rent' => $totalRent,
        'total_deposit' => $totalDeposit,
        'discount' => $discount,
        'final_total' => $finalTotal,
        'status' => 'pending'
    ]);
    
    if (!$orderId) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo đơn hàng']);
        return;
    }
    
    // Lưu chi tiết và xóa item đã đặt
    $orderItemModel = new \App\Models\OrderItem();
    $success = true;
    foreach ($items as $item) {
        $result = $orderItemModel->create([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'size' => $item['size'],
            'color' => $item['color'],
            'quantity' => $item['quantity'],
            'rent_date' => $item['rent_date'],
            'return_date' => $item['return_date'],
            'price' => $item['price'],
            'deposit' => $item['deposit'],
            'subtotal' => $item['subtotal']
        ]);
        if (!$result) {
            $success = false;
            break;
        }
        // Xóa item khỏi giỏ hàng
        $cartModel->removeById($item['id'], $userId);
    }
    
    if (!$success) {
        // Rollback: xóa đơn hàng
        $orderModel->delete($orderId);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu chi tiết đơn hàng']);
        return;
    }
    
    // Xóa session voucher
    unset($_SESSION['voucher_discount']);
    unset($_SESSION['voucher_code']);
    
    echo json_encode(['success' => true, 'order_id' => $orderId]);
}
}