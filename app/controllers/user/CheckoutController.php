<?php

namespace App\Controllers\User;
use App\Models\Promotion;
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

    /* =========================
        1. CHECKOUT PAGE
    ========================= */
  public function index()
{
    $userId = $this->getUserId();

    if (!$userId) {
        header('Location: /LoopFit/public/login');
        exit;
    }

    $cartModel = new Cart();
    $items = $cartModel->getByUser($userId);

    if (empty($items)) {
        return $this->view('user.checkout', [
            'items' => [],
            'total' => 0,
            'addresses' => [],
            'promotions' => []
        ]);
    }

    $total = 0;

    foreach ($items as $key => $item) {

        $days = 1;

        if (!empty($item['rent_date']) && !empty($item['return_date'])) {
            $rent = strtotime($item['rent_date']);
            $return = strtotime($item['return_date']);
            $days = max(1, ceil(($return - $rent) / 86400));
        }

        $items[$key]['days'] = $days;
        $items[$key]['item_total'] = $item['price'] * $item['quantity'] * $days;

        $total += $items[$key]['item_total'];
    }

    // 🔥 LẤY PROMOTION
    $promotionModel = new Promotion();
    $promotions = $promotionModel->getAll();

    return $this->view('user.checkout', [
        'items' => $items,
        'total' => $total,
        'addresses' => [],
        'promotions' => $promotions
    ]);
}

    /* =========================
        2. PROCESS CHECKOUT
    ========================= */
    public function process()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            header('Location: /LoopFit/public/login');
            exit;
        }

        $addressId = $_POST['address_id'] ?? null;
        $promoCode = trim($_POST['promo_code'] ?? '');

        if (!$addressId) {
            die('Thiếu địa chỉ nhận hàng');
        }

        $cartModel = new Cart();
        $items = $cartModel->getByUser($userId);

        if (empty($items)) {
            die('Giỏ hàng trống');
        }

        $rentalService = new RentalService();

        $db = new Database();
        $conn = $db->getConnection();

        $orderModel = new Order();
        $orderItemModel = new OrderItem();

        try {
            $conn->begin_transaction();

            $total = 0;

            /* =========================
                1. VALIDATE + CALC TOTAL
            ========================= */
            foreach ($items as $item) {

                // check ngày
                if (!$rentalService->isDateValid($item['rent_date'], $item['return_date'])) {
                    throw new \Exception('Ngày thuê không hợp lệ: ' . $item['name']);
                }

                // check conflict
                if ($rentalService->hasConflict(
                    $item['product_id'],
                    $item['rent_date'],
                    $item['return_date']
                )) {
                    throw new \Exception('Sản phẩm đã có người thuê: ' . $item['name']);
                }

                $days = max(1, ceil(
                    (strtotime($item['return_date']) - strtotime($item['rent_date'])) / 86400
                ));

                $total += $item['price'] * $item['quantity'] * $days;
            }

            /* =========================
                2. APPLY PROMO (nếu có)
            ========================= */
            $discount = 0;

            if ($promoCode) {
                // TODO: nên load từ PromotionModel
                // demo tạm:
                $discount = 0; // bạn gắn logic % ở đây sau
            }

            $finalTotal = $total - $discount;

            /* =========================
                3. CREATE ORDER
            ========================= */
            $rentDates = array_column($items, 'rent_date');
            $returnDates = array_column($items, 'return_date');

            $rentDate = min($rentDates);
            $returnDate = max($returnDates);

            $orderId = $orderModel->create(
                $userId,
                $addressId,
                $rentDate,
                $returnDate,
                $finalTotal,
                'pending'
            );

            /* =========================
                4. CREATE ORDER ITEMS
            ========================= */
            foreach ($items as $item) {

                $days = max(1, ceil(
                    (strtotime($item['return_date']) - strtotime($item['rent_date'])) / 86400
                ));

                $orderItemModel->create(
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['size'] ?? null,
                    $item['color'] ?? null,
                    $item['rent_date'],
                    $item['return_date'],
                    $days
                );
            }

            /* =========================
                5. CLEAR CART
            ========================= */
            $cartModel->clear($userId);

            $conn->commit();

            header('Location: /LoopFit/public/orders');
            exit;

        } catch (\Exception $e) {
            $conn->rollback();
            die('Checkout lỗi: ' . $e->getMessage());
        }
    }
}