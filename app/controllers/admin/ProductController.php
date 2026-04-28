<?php
namespace App\Controllers\Admin;

use const UPLOAD_PATH;
use App\Models\Product;
use Core\Controller;
use App\Middlewares\AdminMiddleware;

class ProductController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::check();
    }
    
    public function index()
    {
        ob_start();
        require_once __DIR__ . '/../../../views/admin/products/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Quản lý sản phẩm';
        $activeMenu = 'products';
        require_once __DIR__ . '/../../../views/layouts/admin.php';
    }
    
    public function apiGetList()
{
    $productModel = new Product();
    
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        $product = $productModel->getById($id);
        if ($product) {
            $product['image'] = $product['image'] ? 'http://localhost:8080/LoopFit/public/uploads/' . $product['image'] : null;
        }
        return $this->json(['success' => true, 'data' => $product]);
    }
    
    $products = $productModel->getAllWithCategory();
    $baseUrl = 'http://localhost:8080/LoopFit/public';
    
    $formattedProducts = [];
foreach ($products as $p) {
    // Xử lý ảnh trước khi đưa vào mảng
    $image = $p['image'];
    if ($image && !preg_match('/\.(jpg|jpeg|png|gif)$/i', $image)) {
        $image .= '.jpg';
    }
    $imageUrl = $image ? $baseUrl . '/uploads/' . $image : null;

    $formattedProducts[] = [
        "id"            => (int)$p['id'],
        "name"          => $p['name'],
        "price"         => (float)$p['price'],
        "discount_percent" => (int)($p['discount_percent'] ?? 0),
        "deposit_price" => (float)($p['deposit_price'] ?? 0),
        "description"   => $p['description'],
        "image"         => $imageUrl,
        "stock"         => (int)$p['stock'],
        "category_id"   => (int)$p['category_id'],
        "category_name" => $p['category_name'],
        "colors"        => $p['colors'] ?? '',
        "sizes"         => $p['sizes'] ?? '',
        "gender"        => $p['gender'] ?? 'unisex',
        "rented_count"  => (int)($p['rented_count'] ?? 0),
        "created_at"    => $p['created_at']
    ];
}
    
    return $this->json([
        "success" => true,
        "data" => $formattedProducts
    ]);
}
    
   public function update()
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
    }
    
    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? 0;
    $discount_percent = (int)($_POST['discount_percent'] ?? 0);
    $deposit_price = $_POST['deposit_price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 0;
    $description = $_POST['description'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $rented_count = $_POST['rented_count'] ?? 0;
    $sizes = isset($_POST['sizes']) ? (is_array($_POST['sizes']) ? implode(',', $_POST['sizes']) : $_POST['sizes']) : '';
    $colors = isset($_POST['colors']) ? (is_array($_POST['colors']) ? implode(',', $_POST['colors']) : $_POST['colors']) : '';
    $gender = $_POST['gender'] ?? 'unisex';
    
    // ⭐ LẤY ẢNH CŨ TỪ FORM (đã được gửi lên từ edit.php)
    $old_image = $_POST['old_image'] ?? null;
    
    if ($id <= 0 || $name == '' || $price <= 0 || $category_id <= 0) {
        return $this->json(['success' => false, 'error' => "Dữ liệu không hợp lệ"]);
    }
    
    $productModel = new Product();
    
    // Mặc định giữ ảnh cũ
    $image_name = $old_image;
    
    // Xử lý upload ảnh mới (nếu có)
    if (!empty($_FILES['image']['name'])) {
        $target_dir = UPLOAD_PATH;
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        // Tạo tên file mới
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) {
            // Xóa ảnh cũ nếu có và không phải ảnh mặc định
            if ($old_image && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
        } else {
            // Upload thất bại, quay lại ảnh cũ
            $image_name = $old_image;
        }
    }
    
    $result = $productModel->update($id, $name, $price, $discount_percent, $deposit_price, $description, $image_name, $stock, $category_id, $sizes, $colors, $gender, $rented_count);
    
    if ($result) {
        return $this->json(['success' => true, 'message' => "Cập nhật thành công"]);
    } else {
        return $this->json(['success' => false, 'error' => "Cập nhật thất bại"]);
    }
}
    
    public function create()
    {
        return $this->render('admin/products/create');
    }
    
    public function store()
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
    }
    
    $name = trim($_POST['name'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $discount_percent = (int)($_POST['discount_percent'] ?? 0);
    $deposit_price = (int)($_POST['deposit_price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = $_POST['description'] ?? '';
    $stock = (int)($_POST['stock'] ?? 0);
    $rented_count = (int)($_POST['rented_count'] ?? 0);
    $sizes = $_POST['sizes'] ?? 'S,M,L,XL';
    $colors = $_POST['colors'] ?? 'Trắng,Đen,Đỏ';
    $gender = $_POST['gender'] ?? 'unisex';
    $image_name = null;
    
    if ($name == '' || $price <= 0 || $category_id <= 0) {
        return $this->json(['success' => false, 'error' => "Dữ liệu không hợp lệ"]);
    }
    
    if (!empty($_FILES['image']['name'])) {
        $target_dir = UPLOAD_PATH;
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
    }
    
    $productModel = new \App\Models\Product();
    $result = $productModel->create($name, $price, $discount_percent, $deposit_price, $description, $image_name, $stock, $category_id, $sizes, $colors, $gender, $rented_count);
    
    if ($result) {
        return $this->json(['success' => true, 'message' => 'Thêm sản phẩm thành công']);
    } else {
        return $this->json(['success' => false, 'error' => 'Thêm thất bại']);
    }
}
    
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            header('Location: http://localhost:8080/LoopFit/public/index.php?url=admin/products');
            exit();
        }
        $productModel = new \App\Models\Product();
        $product = $productModel->getById($id);
        if (!$product) {
            header('Location: http://localhost:8080/LoopFit/public/index.php?url=admin/products');
            exit();
        }
        return $this->render('admin/products/edit', ['product' => $product]);
    }
    
   public function delete()
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
    }
    $id = $_POST['id'] ?? 0;
    $productModel = new \App\Models\Product();
    $result = $productModel->delete($id);
    return $this->json(['success' => $result]);
}
}