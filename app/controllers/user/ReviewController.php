<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;

class ReviewController extends Controller
{
    /* ===== Hiển thị form đánh giá sản phẩm sau khi đơn hàng hoàn tất ===== */
    public function showForm($order_id, $product_id)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $user_id = $_SESSION['user']['id'] ?? null;
        if (!$user_id) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để đánh giá.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $orderModel = new Order();
        $order = $orderModel->getById($order_id);
        if (!$order) {
            $_SESSION['error'] = "Đơn hàng #{$order_id} không tồn tại.";
            header('Location: ' . BASE_URL . '/orders');
            exit;
        }
        if ($order['user_id'] != $user_id) {
            $_SESSION['error'] = 'Bạn không có quyền đánh giá đơn hàng này.';
            header('Location: ' . BASE_URL . '/orders');
            exit;
        }
        if ($order['status'] !== 'completed') {
            $_SESSION['error'] = 'Chỉ có thể đánh giá khi đơn hàng đã hoàn tất. Trạng thái hiện tại: ' . $order['status'];
            header('Location: ' . BASE_URL . '/orders');
            exit;
        }

        $reviewModel = new Review();
        if ($reviewModel->hasReviewed($user_id, $product_id)) {
            $_SESSION['error'] = 'Bạn đã đánh giá sản phẩm này rồi.';
            header('Location: ' . BASE_URL . '/order/' . $order_id);
            exit;
        }

        $orderItemModel = new OrderItem();
        $item = $orderItemModel->getByOrderAndProduct($order_id, $product_id);
        if (!$item) {
            $_SESSION['error'] = "Sản phẩm không thuộc đơn hàng #{$order_id}.";
            header('Location: ' . BASE_URL . '/orders');
            exit;
        }

        return $this->view('user/review_form', [
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_name' => $item['product_name'] ?? 'Sản phẩm',
            'product_image' => $item['product_image'] ?? ''
        ]);
    }

    /* ===== Xử lý submit đánh giá ===== */
    public function submit()
    {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user_id = $_SESSION['user']['id'] ?? null;
        if (!$user_id) {
            echo json_encode(['success' => false, 'error' => 'Vui lòng đăng nhập.']);
            return;
        }

        $order_id = (int)($_POST['order_id'] ?? 0);
        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if (!$order_id || !$product_id || $rating < 1 || $rating > 5 || empty($comment)) {
            echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getById($order_id);
        if (!$order || $order['user_id'] != $user_id || $order['status'] !== 'completed') {
            echo json_encode(['success' => false, 'error' => 'Đơn hàng chưa hoàn tất hoặc không hợp lệ.']);
            return;
        }

        $reviewModel = new Review();
        if ($reviewModel->hasReviewed($user_id, $product_id)) {
            echo json_encode(['success' => false, 'error' => 'Bạn đã đánh giá sản phẩm này rồi.']);
            return;
        }

        $result = $reviewModel->add($product_id, $user_id, $rating, $comment);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Lỗi lưu đánh giá.']);
        }
    }

    public function add()
    {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user_id = $_SESSION['user']['id'] ?? null;
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
            return;
        }

        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if (!$product_id || $rating < 1 || $rating > 5 || empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        $orderItemModel = new OrderItem();
        $hasOrderedCompleted = $orderItemModel->hasUserBoughtProductCompleted($user_id, $product_id);
        if (!$hasOrderedCompleted) {
            echo json_encode(['success' => false, 'message' => 'Bạn chỉ có thể đánh giá sau khi đã thuê và hoàn tất đơn hàng có sản phẩm này.']);
            return;
        }
        // Kiểm tra đã đánh giá chưa
        $reviewModel = new Review();
        if ($reviewModel->hasReviewed($user_id, $product_id)) {
            echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi.']);
            return;
        }

        $result = $reviewModel->add($product_id, $user_id, $rating, $comment);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi lưu đánh giá.']);
        }
    }
}