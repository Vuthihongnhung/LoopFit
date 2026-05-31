<?php
namespace App\Controllers\Admin;

use App\Models\BulkPromotion;
use App\Models\BulkPromotionItem;
use Core\Controller;
use App\Middlewares\AdminMiddleware;

class BulkPromotionController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::check();
    }

    /* ===== API: Lấy danh sách tất cả chương trình khuyến mãi ===== */
    public function apiGetList()
    {
        $bulkPromoModel = new BulkPromotion();
        $promotions = $bulkPromoModel->getAll();

        $formattedPromotions = [];
        foreach ($promotions as $row) {
            $formattedPromotions[] = [
                "id" => (int)$row["id"],
                "name" => $row["name"],
                "promotion_type" => $row["promotion_type"],
                "discount_value" => (int)$row["discount_value"],
                "fixed_price" => (float)($row["fixed_price"] ?? 0),
                "scope_type" => $row["scope_type"],
                "start_date" => $row["start_date"],
                "end_date" => $row["end_date"],
                "is_active" => (int)$row["is_active"],
                "product_count" => (int)($row["product_count"] ?? 0)
            ];
        }

        return $this->json([
            "success" => true,
            "data" => $formattedPromotions
        ]);
    }

    /* ===== API: Thêm mới chương trình khuyến mãi ===== */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }

        $name = trim($_POST['name'] ?? '');
        $promotion_type = $_POST['promotion_type'] ?? 'percentage';
        $discount_value = (int)($_POST['discount_value'] ?? 0);
        $fixed_price = (float)($_POST['fixed_price'] ?? 0);
        $scope_type = $_POST['scope_type'] ?? 'all_products';
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Lấy category_id nếu scope_type là category
        $category_ids = [];
        $category_id = null;
        if ($scope_type === 'category') {
            $category_ids = $_POST['category_ids'] ?? [];
            $category_id = !empty($category_ids) ? (int)$category_ids[0] : null;
        }

        // Validation
        if (empty($name)) {
            return $this->json(['success' => false, 'error' => 'Tên chương trình không được để trống']);
        }

        if ($promotion_type === 'percentage' && ($discount_value <= 0 || $discount_value > 100)) {
            return $this->json(['success' => false, 'error' => 'Phần trăm giảm phải từ 1 đến 100']);
        }

        if ($promotion_type === 'fixed_price' && $fixed_price <= 0) {
            return $this->json(['success' => false, 'error' => 'Giá cố định phải lớn hơn 0']);
        }

        if (empty($start_date) || empty($end_date)) {
            return $this->json(['success' => false, 'error' => 'Ngày bắt đầu và kết thúc không được để trống']);
        }

        $bulkPromoModel = new BulkPromotion();
        $result = $bulkPromoModel->create($name, $promotion_type, $discount_value, $fixed_price, $scope_type, $start_date, $end_date, $is_active, $category_id);
        if ($result) {
    $newId = $bulkPromoModel->getLastId();
    
    // Lưu danh sách category_ids
    if ($scope_type === 'category' && !empty($category_ids)) {
        $bulkPromoModel->saveCategories($newId, $category_ids);
    }
    // Lưu danh sách sản phẩm cụ thể
$product_ids = $_POST['product_ids'] ?? [];
if ($scope_type === 'specific_products' && !empty($product_ids)) {
    $bulkPromoModel->saveProducts($newId, $product_ids);
}
    return $this->json(['success' => true, 'message' => 'Tạo chương trình thành công', 'id' => $newId]);
} else {
            return $this->json(['success' => false, 'error' => 'Tạo chương trình thất bại']);
        }
    }

    /* ===== API: Cập nhật chương trình khuyến mãi ===== */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $promotion_type = $_POST['promotion_type'] ?? 'percentage';
        $discount_value = (int)($_POST['discount_value'] ?? 0);
        $fixed_price = (float)($_POST['fixed_price'] ?? 0);
        $scope_type = $_POST['scope_type'] ?? 'all_products';
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Lấy category_id nếu scope_type là category
        $category_ids = [];
        $category_id = null;
        if ($scope_type === 'category') {
            $category_ids = $_POST['category_ids'] ?? [];
            $category_id = !empty($category_ids) ? (int)$category_ids[0] : null;
        }

        // Validation
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'ID không hợp lệ']);
        }

        if (empty($name)) {
            return $this->json(['success' => false, 'error' => 'Tên chương trình không được để trống']);
        }

        if ($promotion_type === 'percentage' && ($discount_value <= 0 || $discount_value > 100)) {
            return $this->json(['success' => false, 'error' => 'Phần trăm giảm phải từ 1 đến 100']);
        }

        if ($promotion_type === 'fixed_price' && $fixed_price <= 0) {
            return $this->json(['success' => false, 'error' => 'Giá cố định phải lớn hơn 0']);
        }

        $bulkPromoModel = new BulkPromotion();
        $promo = $bulkPromoModel->getById($id);

        if (!$promo) {
            return $this->json(['success' => false, 'error' => 'Chương trình khuyến mãi không tồn tại']);
        }

        $result = $bulkPromoModel->update($id, $name, $promotion_type, $discount_value, $fixed_price, $scope_type, $start_date, $end_date, $is_active, $category_id);

        if ($result) {
    // Cập nhật danh sách category_ids
    if ($scope_type === 'category' && isset($category_ids) && !empty($category_ids)) {
        $bulkPromoModel->saveCategories($id, $category_ids);
    } elseif ($scope_type === 'category' && empty($category_ids)) {
        $bulkPromoModel->saveCategories($id, []);
    }
    // Cập nhật danh sách sản phẩm cụ thể
$product_ids = $_POST['product_ids'] ?? [];
if ($scope_type === 'specific_products') {
    $bulkPromoModel->saveProducts($id, $product_ids);
}
    return $this->json(['success' => true, 'message' => 'Cập nhật chương trình thành công']);
} else {
            return $this->json(['success' => false, 'error' => 'Cập nhật chương trình thất bại']);
        }
    }

    /* ===== API: Xóa chương trình khuyến mãi ===== */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'ID không hợp lệ']);
        }

        $bulkPromoModel = new BulkPromotion();
        $promo = $bulkPromoModel->getById($id);

        if (!$promo) {
            return $this->json(['success' => false, 'error' => 'Chương trình khuyến mãi không tồn tại']);
        }

        $result = $bulkPromoModel->delete($id);

        if ($result) {
            return $this->json(['success' => true, 'message' => 'Xóa chương trình thành công']);
        } else {
            return $this->json(['success' => false, 'error' => 'Xóa chương trình thất bại']);
        }
    }
}