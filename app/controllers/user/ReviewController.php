<?php
namespace App\Controllers\User;

use App\Controllers\Controller;
use App\Models\Review;
use App\Models\OrderItem;

class ReviewController extends Controller
{
    public function add() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
            return;
        }
        $user_id = $_SESSION['user_id'];
        $product_id = $_POST['product_id'] ?? 0;
        $rating = $_POST['rating'] ?? 0;
        $comment = $_POST['comment'] ?? '';

        if (!$product_id || $rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        // Kiểm tra xem user đã từng thuê sản phẩm này chưa
        $orderItemModel = new OrderItem();
        $hasOrdered = $orderItemModel->hasUserBoughtProduct($user_id, $product_id);
        if (!$hasOrdered) {
            echo json_encode(['success' => false, 'message' => 'Bạn chỉ có thể đánh giá sau khi đã thuê sản phẩm này.']);
            return;
        }

        // Kiểm tra xem đã đánh giá chưa (mỗi user chỉ được đánh giá 1 lần)
        $reviewModel = new Review();
        if ($reviewModel->hasReviewed($user_id, $product_id)) {
            echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi.']);
            return;
        }

        $reviewModel->add($product_id, $user_id, $rating, $comment);
        echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
    }
}