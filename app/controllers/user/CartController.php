<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;
use App\Models\Cart;

class CartController extends Controller
{
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ===== Lấy ID người dùng từ session ===== */
    private function getUserId()
    {
        return $_SESSION['user']['id'] ?? 0;
    }

    /* ===== API: Thêm sản phẩm vào giỏ hàng ===== */
    public function add()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm giỏ hàng'], 401);
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $size = trim($_POST['size'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $rentDate = $_POST['rent_date'] ?? '';
        $returnDate = $_POST['return_date'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 1);

        if (!$productId || !$size || !$rentDate || !$returnDate || $quantity <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
        }

        $productModel = new Product();
        $product = $productModel->getById($productId);
        if (!$product) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        // Tính giá thực tế sau giảm
        $price = (float)$product['price'];
        $discount = (int)($product['discount_percent'] ?? 0);
        if ($discount > 0) {
            $price = $price * (100 - $discount) / 100;
        }

        $cartModel = new Cart();
        $result = $cartModel->addItem($userId, $productId, $size, $color, $rentDate, $returnDate, $quantity, $price);
        if ($result) {
            $totalItems = $cartModel->countByUser($userId);
            return $this->jsonResponse(['success' => true, 'message' => 'Đã thêm vào giỏ', 'cartCount' => $totalItems]);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Thêm thất bại'], 500);
        }
    }

    /* ===== Hiển thị trang giỏ hàng ===== */
    public function index()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->view('user/cart', ['cart' => []]);
            return;
        }

        $cartModel = new Cart();
        $cartItems = $cartModel->getByUser($userId);

        $productModel = new Product();
        foreach ($cartItems as &$item) {
            if (!isset($item['deposit_price']) || $item['deposit_price'] == 0) {
                $product = $productModel->getById($item['product_id']);
                $item['deposit_price'] = $product['deposit_price'] ?? ($product['price'] * 0.3);
                $item['image'] = $product['image'] ?? '';
            }
            // Tính số ngày thuê: từ rent_date đến return_date, cộng thêm 1
            $rent = new \DateTime($item['rent_date']);
            $return = new \DateTime($item['return_date']);
            $diff = $rent->diff($return);
            $days = $diff->days + 1;
            if ($days < 1) $days = 1;
            $item['days'] = $days;
        }
        $this->view('user/cart', ['cart' => $cartItems]);
    }

    /* ===== API: Cập nhật số lượng sản phẩm trong giỏ ===== */
    public function update()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }

        $cartId = (int)($_POST['cart_id'] ?? 0);
        $change = (int)($_POST['change'] ?? 0);
        if (!$cartId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu cart_id'], 400);
        }

        $cartModel = new Cart();
        $item = $cartModel->findById($cartId, $userId);
        if (!$item) {
            return $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ'], 404);
        }

        $newQty = $item['quantity'] + $change;
        if ($newQty <= 0) {
            // Xóa khỏi giỏ
            $result = $cartModel->removeById($cartId, $userId);
            if ($result) {
                return $this->jsonResponse(['success' => true, 'new_quantity' => 0]);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Xóa thất bại'], 500);
            }
        } else {
            // Cập nhật số lượng
            $result = $cartModel->updateQuantityById($cartId, $userId, $newQty);
            if ($result) {
                return $this->jsonResponse(['success' => true, 'new_quantity' => $newQty]);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại'], 500);
            }
        }
    }

    /* ===== API: Xóa một sản phẩm khỏi giỏ hàng ===== */
    public function remove()
    {
        $userId = $this->getUserId();
        if (!$userId) return $this->jsonResponse(['success' => false, 'message' => 'Chưa đăng nhập'], 401);

        $cartId = (int)($_POST['cart_id'] ?? 0);
        if (!$cartId) return $this->jsonResponse(['success' => false, 'message' => 'Thiếu cart_id'], 400);

        $cartModel = new Cart();
        $result = $cartModel->removeById($cartId, $userId);
        return $this->jsonResponse(['success' => $result]);
    }

    /* ===== API: Lấy số lượng sản phẩm hiện có trong giỏ (dùng cho badge ở header) ===== */
    public function count()
    {
        $userId = $this->getUserId();
        if (!$userId) return $this->jsonResponse(['count' => 0]);
        $cartModel = new Cart();
        $count = $cartModel->countByUser($userId);
        return $this->jsonResponse(['count' => $count]);
    }

    /* ===== API: Cập nhật thuộc tính của một sản phẩm trong giỏ (size, color, ngày thuê/trả) ===== */
    public function updateItem()
    {
        header('Content-Type: application/json');
        
        $userId = $this->getUserId();
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $cartId = (int)($input['cart_id'] ?? 0);
        $newSize = trim($input['size'] ?? '');
        $newColor = trim($input['color'] ?? '');
        $newRentDate = $input['rent_date'] ?? null;
        $newReturnDate = $input['return_date'] ?? null;
        
        if ($cartId <= 0 || empty($newSize) || empty($newColor)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        
        // Validate ngày
        if ($newRentDate && $newReturnDate) {
            $today = date('Y-m-d');
            if ($newRentDate < $today) {
                echo json_encode(['success' => false, 'message' => 'Ngày thuê không thể trong quá khứ']);
                return;
            }
            if ($newReturnDate <= $newRentDate) {
                echo json_encode(['success' => false, 'message' => 'Ngày trả phải sau ngày thuê']);
                return;
            }
        }
        
        $cartModel = new Cart();
        $updated = $cartModel->updateItem($cartId, $userId, $newSize, $newColor, $newRentDate, $newReturnDate);
        
        if ($updated) {
            // Tính số ngày mới để trả về
            $days = 1;
            if ($newRentDate && $newReturnDate) {
                $rent = new \DateTime($newRentDate);
                $return = new \DateTime($newReturnDate);
                $diff = $rent->diff($return);
                $days = $diff->days + 1;
                if ($days < 1) $days = 1;
            }
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công', 'days' => $days]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại hoặc sản phẩm không tồn tại']);
        }
    }
}