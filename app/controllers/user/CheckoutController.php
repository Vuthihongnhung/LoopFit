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
    public function process()
    {
        return $this->processCheckout();
    }

    public function processCheckout()
    {
        header('Content-Type: application/json; charset=utf-8');

        $userId = (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Vui lòng đăng nhập'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $addressId = (int)($_POST['address_id'] ?? 0);
        $paymentMethod = $this->normalizePaymentMethod($_POST['payment_method'] ?? 'cash');
        $notes = trim($_POST['notes'] ?? '');
        $discount = (float)($_POST['discount'] ?? 0);
        $cartIds = $_POST['cart_ids'] ?? '';

        if ($addressId <= 0) {
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Chưa chọn địa chỉ nhận hàng'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (empty($cartIds)) {
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Không có sản phẩm nào được chọn'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $cartIdArray = array_filter(array_map('intval', explode(',', $cartIds)));
        $cartModel = new \App\Models\Cart();
        $productModel = new \App\Models\Product();
        $items = [];

        foreach ($cartIdArray as $cartId) {
            $item = $cartModel->findById($cartId, $userId);
            if (!$item) continue;

            $product = $productModel->getById($item['product_id']);
            if (!$product) continue;

            $item['name'] = $product['name'];
            $item['price'] = (float)$product['price'];
            $item['deposit_price'] = (float)($product['deposit_price'] ?? ($product['price'] * 0.3));
            $item['image'] = $product['image'] ?? null;
            $items[] = $item;
        }

        if (empty($items)) {
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Giỏ hàng trống'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $rentalService = new \App\Services\RentalService();
        $conflict = $rentalService->checkCartConflict($items);
        if (!$conflict['success']) {
            echo json_encode(['status' => 'error', 'success' => false, 'message' => $conflict['message']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $totalRent = 0;
        $totalDeposit = 0;
        foreach ($items as &$item) {
            $days = $rentalService->calculateRentalDays($item['rent_date'], $item['return_date']);
            if ($days <= 0) {
                echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Ngày thuê/trả không hợp lệ'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $price = (float)$item['price'];
            $depositPrice = (float)$item['deposit_price'];
            $qty = (int)$item['quantity'];

            $item['days'] = $days;
            $item['subtotal'] = $price * $qty * $days;
            $item['deposit'] = $depositPrice * $qty;

            $totalRent += $item['subtotal'];
            $totalDeposit += $item['deposit'];
        }
        unset($item);

        if ($discount > 0 && (!isset($_SESSION['voucher_discount']) || (float)$_SESSION['voucher_discount'] !== $discount)) {
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Mã giảm giá không hợp lệ'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $finalTotal = max(0, $totalRent + $totalDeposit - $discount);
        $firstItem = $items[0];
        $lastItem = end($items);

        $orderModel = new \App\Models\Order();
        $orderId = $orderModel->create([
            'user_id' => $userId,
            'address_id' => $addressId,
            'rent_date' => $firstItem['rent_date'],
            'return_date' => $lastItem['return_date'],
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentMethod === 'cash' ? 'unpaid' : 'pending',
            'notes' => $notes,
            'total_rent' => $totalRent,
            'total_deposit' => $totalDeposit,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'total_price' => $finalTotal,
            'deposit_amount' => $totalDeposit,
            'status' => 'pending'
        ]);

        if (!$orderId) {
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Không thể tạo đơn hàng'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $orderItemModel = new \App\Models\OrderItem();
        foreach ($items as $item) {
            $ok = $orderItemModel->create([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'size' => $item['size'] ?? null,
                'color' => $item['color'] ?? null,
                'quantity' => $item['quantity'],
                'rent_date' => $item['rent_date'],
                'return_date' => $item['return_date'],
                'days' => $item['days'],
                'price' => $item['price'],
                'deposit' => $item['deposit'],
                'subtotal' => $item['subtotal']
            ]);

            if (!$ok) {
                $orderModel->delete($orderId);
                echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Lỗi khi lưu chi tiết đơn hàng'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $cartModel->removeById($item['id'], $userId);
        }

        $paymentModel = new \App\Models\Payment();
        $paymentModel->create([
            'order_id' => $orderId,
            'amount' => $finalTotal,
            'method' => $paymentMethod,
            'status' => $paymentMethod === 'cash' ? 'unpaid' : 'pending'
        ]);

        unset($_SESSION['voucher_discount'], $_SESSION['voucher_code']);

        if ($paymentMethod === 'cash') {
            echo json_encode([
                'status' => 'success',
                'method' => 'cash',
                'redirect' => '/order/success'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'method' => 'transfer',
            'order_id' => $orderId,
            'amount' => $finalTotal,
            'redirect' => '/payment/qr?order_id=' . $orderId
        ], JSON_UNESCAPED_UNICODE);
    }

    private function normalizePaymentMethod(string $method): string
    {
        $method = strtolower(trim($method));
        if (in_array($method, ['transfer', 'bank_transfer', 'chuyen_khoan', 'bank'], true)) {
            return 'transfer';
        }
        return 'cash';
    }
}
