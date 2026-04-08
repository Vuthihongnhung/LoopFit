<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
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

    public function add()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        if ($productId <= 0 || $quantity <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ'
            ], 400);
        }

        $productModel = new Product();
        $product = $productModel->getById($productId);

        if (!$product) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        if ((int) $product['stock'] <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Sản phẩm đã hết hàng'
            ], 400);
        }

        $cartModel = new Cart();
        $existingItem = $cartModel->findItem($userId, $productId);
        $currentQty = $existingItem ? (int) $existingItem['quantity'] : 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > (int) $product['stock']) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho'
            ], 400);
        }

        $result = $cartModel->add($userId, $productId, $quantity);

        if (!$result) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Không thể thêm vào giỏ hàng'
            ], 500);
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng'
        ]);
    }

    public function update()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

        if ($productId <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'product_id không hợp lệ'
            ], 400);
        }

        $cartModel = new Cart();
        $cartItem = $cartModel->findItem($userId, $productId);

        if (!$cartItem) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Sản phẩm không có trong giỏ hàng'
            ], 404);
        }

        if ($quantity <= 0) {
            $result = $cartModel->remove($userId, $productId);

            if (!$result) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Không thể xoá sản phẩm khỏi giỏ hàng'
                ], 500);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Đã xoá sản phẩm khỏi giỏ hàng'
            ]);
        }

        $productModel = new Product();
        $product = $productModel->getById($productId);

        if (!$product) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        if ($quantity > (int) $product['stock']) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho'
            ], 400);
        }

        $result = $cartModel->updateQuantity($userId, $productId, $quantity);

        if (!$result) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Không thể cập nhật giỏ hàng'
            ], 500);
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Cập nhật giỏ hàng thành công'
        ]);
    }

    public function remove()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Phương thức không hợp lệ'
            ], 405);
        }

        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

        if ($productId <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'product_id không hợp lệ'
            ], 400);
        }

        $cartModel = new Cart();
        $cartItem = $cartModel->findItem($userId, $productId);

        if (!$cartItem) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Sản phẩm không có trong giỏ hàng'
            ], 404);
        }

        $result = $cartModel->remove($userId, $productId);

        if (!$result) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Không thể xoá sản phẩm khỏi giỏ hàng'
            ], 500);
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Đã xoá sản phẩm khỏi giỏ hàng'
        ]);
    }
}