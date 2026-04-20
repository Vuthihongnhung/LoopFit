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

    private function getUserId()
    {
        return $_SESSION['user']['id'] ?? 0;
    }

    public function add()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm giỏ hàng'], 401);
        }

        $productId = $_POST['product_id'] ?? 0;
        $size = $_POST['size'] ?? '';
        $color = $_POST['color'] ?? '';   // màu sắc nếu có
        $rentDate = $_POST['rent_date'] ?? '';
        $returnDate = $_POST['return_date'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 1);

        if (!$productId || !$size || !$rentDate || !$returnDate) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
        }

        $productModel = new Product();
        $product = $productModel->getById($productId);
        if (!$product) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $cartModel = new Cart();
        // Kiểm tra xem sản phẩm (cùng size, màu, ngày) đã tồn tại chưa? Đơn giản hóa: kiểm tra theo product_id + size + rent_date + return_date
        // Tuy nhiên, trong model Cart hiện tại có findItem chỉ dùng product_id, chưa đủ. Tôi sẽ tạm dùng add (insert mới) và sau đó nếu trùng thì cập nhật.
        // Nhưng để đơn giản, ta có thể insert mới, hoặc viết thêm phương thức tìm kiếm chính xác.
        // Tạm thời, chúng ta chấp nhận mỗi lần thêm là một dòng mới (không gộp). Nếu muốn gộp, cần sửa model.
        // Vì model Cart chưa có phương thức add với đầy đủ tham số (thiếu color, price). Ta sẽ sửa lại model Cart thêm phương thức addItem đầy đủ.
        // Nhưng để nhanh, tôi sẽ thêm một phương thức addItem trong model Cart (viết thêm).

        // Gọi phương thức addItem (sẽ viết dưới)
        $result = $cartModel->addItem($userId, $productId, $size, $color, $rentDate, $returnDate, $quantity, $product['price']);
        if ($result) {
            $totalItems = $cartModel->countByUser($userId);
            return $this->jsonResponse(['success' => true, 'message' => 'Đã thêm vào giỏ', 'cartCount' => $totalItems]);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Thêm thất bại'], 500);
        }
    }

    public function index()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->view('user/cart', ['cart' => []]);
            return;
        }

        $cartModel = new Cart();
        $cartItems = $cartModel->getByUser($userId);  // lấy từ model

        // Bổ sung thông tin deposit_price, days
        $productModel = new Product();
        foreach ($cartItems as &$item) {
            $product = $productModel->getById($item['product_id']);
            $item['deposit_price'] = $product['deposit_price'] ?? $product['price'] * 0.3;
            $item['image'] = $product['image'];
            $rent = new \DateTime($item['rent_date']);
            $return = new \DateTime($item['return_date']);
            $item['days'] = $rent->diff($return)->days + 1;
        }
        $this->view('user/cart', ['cart' => $cartItems]);
    }

    public function addItem($userId, $productId, $size, $color, $rentDate, $returnDate, $quantity, $price)
{
    // Kiểm tra xem đã tồn tại item giống hệt (cùng size, màu, ngày) chưa
    $stmt = $this->conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ? AND rent_date = ? AND return_date = ?");
    $stmt->bind_param("iissss", $userId, $productId, $size, $color, $rentDate, $returnDate);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $newQty, $existing['id']);
        return $stmt->execute();
    } else {
        $stmt = $this->conn->prepare("INSERT INTO cart (user_id, product_id, size, color, rent_date, return_date, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssii", $userId, $productId, $size, $color, $rentDate, $returnDate, $quantity, $price);
        return $stmt->execute();
    }
}

    public function update()
{
    $userId = $this->getUserId();
    if (!$userId) {
        return $this->jsonResponse(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
    }

    $cartId = $_POST['cart_id'] ?? 0;
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
        $result = $cartModel->removeById($cartId, $userId);
        if ($result) {
            return $this->jsonResponse(['success' => true, 'new_quantity' => 0]);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Xóa thất bại'], 500);
        }
    } else {
        $result = $cartModel->updateQuantityById($cartId, $userId, $newQty);
        if ($result) {
            return $this->jsonResponse(['success' => true, 'new_quantity' => $newQty]);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại'], 500);
        }
    }
}

    public function remove()
    {
        $userId = $this->getUserId();
        if (!$userId) return $this->jsonResponse(['success' => false, 'message' => 'Chưa đăng nhập'], 401);

        $cartId = $_POST['cart_id'] ?? 0;
        if (!$cartId) return $this->jsonResponse(['success' => false, 'message' => 'Thiếu cart_id'], 400);

        $cartModel = new Cart();
        $result = $cartModel->removeById($cartId, $userId);
        return $this->jsonResponse(['success' => $result]);
    }

    public function count()
    {
        $userId = $this->getUserId();
        if (!$userId) return $this->jsonResponse(['count' => 0]);
        $cartModel = new Cart();
        $count = $cartModel->countByUser($userId);
        return $this->jsonResponse(['count' => $count]);
    }
}