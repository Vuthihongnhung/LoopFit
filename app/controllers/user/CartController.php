<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    protected $cartModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
    }

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

    // =====================
    // ADD TO CART (dùng product_id OK)
    // =====================
    public function add()
{
    $userId = $this->getUserId();

    if (!$userId) {
        return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity  = (int) ($_POST['quantity'] ?? 1);
    $size      = $_POST['size'] ?? null;
    $rentDate  = $_POST['rent_date'] ?? null;
    $returnDate = $_POST['return_date'] ?? null;

    if ($productId <= 0 || $quantity <= 0) {
        return $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], 400);
    }

    // 👉 validate thêm
    if (!$size || !$rentDate || !$returnDate) {
        return $this->jsonResponse(['success' => false, 'message' => 'Thiếu thông tin (size/ngày)'], 400);
    }

    $productModel = new Product();
    $product = $productModel->getById($productId);

    if (!$product) {
        return $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
    }

    if ($quantity > (int)$product['stock']) {
        return $this->jsonResponse(['success' => false, 'message' => 'Vượt quá tồn kho'], 400);
    }

    // 🔥 QUAN TRỌNG: truyền thêm size + date
    $this->cartModel->add($userId, $productId, $quantity, $size, $rentDate, $returnDate);

    return $this->jsonResponse([
        'success' => true,
        'message' => 'Đã thêm vào giỏ hàng',
        'cart_count' => $this->getCartCount($userId)
    ]);
}

    // =====================
    // UPDATE QUANTITY (DÙNG cart_id)
    // =====================
    public function update()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $cartId  = (int) ($_POST['cart_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);

        if ($cartId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'cart_id không hợp lệ'], 400);
        }

        $cartItem = $this->cartModel->findById($cartId, $userId);

        if (!$cartItem) {
            return $this->jsonResponse(['success' => false, 'message' => 'Item không tồn tại trong giỏ'], 404);
        }

        // nếu quantity = 0 → xóa luôn
        if ($quantity <= 0) {
            $this->cartModel->removeById($cartId, $userId);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Đã xoá sản phẩm',
                'cart_count' => $this->getCartCount($userId)
            ]);
        }

        // check stock
        $productModel = new Product();
        $product = $productModel->getById($cartItem['product_id']);

        if (!$product) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        if ($quantity > (int)$product['stock']) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vượt quá tồn kho'], 400);
        }

        $this->cartModel->updateQuantityById($cartId, $userId, $quantity);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Cập nhật thành công',
            'cart_count' => $this->getCartCount($userId)
        ]);
    }

    // =====================
    // REMOVE (DÙNG cart_id)
    // =====================
    public function remove()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $cartId = (int) ($_POST['cart_id'] ?? 0);

        if ($cartId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'cart_id không hợp lệ'], 400);
        }

        $cartItem = $this->cartModel->findById($cartId, $userId);

        if (!$cartItem) {
            return $this->jsonResponse(['success' => false, 'message' => 'Item không tồn tại'], 404);
        }

        $this->cartModel->removeById($cartId, $userId);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Đã xoá',
            'cart_count' => $this->getCartCount($userId)
        ]);
    }

    // =====================
    // VIEW CART
    // =====================
    public function index()
    {
        $userId = $this->getUserId();

        $cartItems = $this->cartModel->getByUser($userId);

        $this->view('user.cart', [
    'cartItems' => $cartItems
]);
    }

    private function getCartCount($userId)
    {
        return $this->cartModel->countByUser($userId);
    }
}