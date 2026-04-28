<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Middlewares\AdminMiddleware;

class ReviewController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        AdminMiddleware::check();
        $database = new \Core\Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Hiển thị danh sách đánh giá
     */
    public function index()
    {
        $sql = "SELECT r.*, u.name as user_name, u.email, p.name as product_name, p.image
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN products p ON r.product_id = p.id
                ORDER BY r.created_at DESC";
        
        $result = $this->db->query($sql);
        $reviews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        
        // Dùng view() thay vì render()
        $this->view('admin/reviews/index', [
            'reviews' => $reviews,
            'pageTitle' => 'Quản lý đánh giá',
            'activeMenu' => 'reviews'
        ]);
    }
    
    /**
     * API: Lấy danh sách đánh giá (JSON)
     */
    public function apiGetList()
    {
        header('Content-Type: application/json');
        
        $sql = "SELECT r.*, u.name as user_name, u.email, p.name as product_name
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN products p ON r.product_id = p.id
                ORDER BY r.created_at DESC";
        
        $result = $this->db->query($sql);
        $reviews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        
        echo json_encode([
            'success' => true,
            'data' => $reviews
        ]);
        exit;
    }
    
    /**
     * API: Xóa đánh giá
     */
    public function delete()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method không hợp lệ']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID không hợp lệ']);
            exit;
        }
        
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$id]);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Xóa đánh giá thành công']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Xóa thất bại']);
        }
        exit;
    }
    
    /**
     * API: Cập nhật trạng thái hiển thị (is_hidden)
     */
    public function toggleStatus()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method không hợp lệ']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $currentStatus = (int)($_POST['current_status'] ?? 0);
        $newStatus = $currentStatus == 0 ? 1 : 0;
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID không hợp lệ']);
            exit;
        }
        
        $sql = "UPDATE reviews SET is_hidden = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$newStatus, $id]);
        
        if ($success) {
            $message = $newStatus == 1 ? 'Đã ẩn đánh giá' : 'Đã hiện đánh giá';
            echo json_encode(['success' => true, 'message' => $message, 'is_hidden' => $newStatus]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Cập nhật thất bại']);
        }
        exit;
    }
}