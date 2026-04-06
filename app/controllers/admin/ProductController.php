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
        $productModel = new Product();
        $products = $productModel->getAllWithCategory();
        
        return $this->render('admin/products/index', [
            'products' => $products
        ]);
    }
    
    public function apiGetList()
{
    $productModel = new Product();
    $products = $productModel->getAllWithCategory();
    
    // Lấy BASE_URL từ config (nếu có) hoặc dùng đường dẫn cứng
    $baseUrl = 'http://localhost:8080/LoopFit/public';
    
    $formattedProducts = [];
    foreach ($products as $p) {
        $formattedProducts[] = [
            "id" => (int)$p['id'],
            "name" => $p['name'],
            "price" => (float)$p['price'],
            "description" => $p['description'],
            "image" => $p['image'] ? $baseUrl . '/uploads/' . $p['image'] : null,
            "stock" => (int)$p['stock'],
            "category" => [
                "id" => (int)$p['category_id'],
                "name" => $p['category_name']
            ],
            "created_at" => $p['created_at']
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
            return $this->json([
                "success" => false,
                "error" => "Method không hợp lệ"
            ]);
        }
        
        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $price = $_POST['price'] ?? 0;
        $category_id = $_POST['category_id'] ?? 0;
        $description = $_POST['description'] ?? '';
        $stock = $_POST['stock'] ?? 0;
        
        if ($id <= 0 || $name == '' || $price <= 0) {
            return $this->json([
                "success" => false,
                "error" => "Dữ liệu không hợp lệ"
            ]);
        }
        
        $productModel = new Product();
        $old = $productModel->getImageById($id);
        $image_name = $old['image'];
        
        if (!empty($_FILES['image']['name'])) {
            $target_dir = UPLOAD_PATH;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $new_image_name = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $new_image_name;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                if (!empty($old['image']) && file_exists($target_dir . $old['image'])) {
                    unlink($target_dir . $old['image']);
                }
                $image_name = $new_image_name;
            }
        }
        
        $result = $productModel->update($id, $name, $price, $description, $image_name, $stock, $category_id);
        
        if ($result) {
            return $this->json([
                "success" => true,
                "message" => "Cập nhật thành công"
            ]);
        } else {
            return $this->json([
                "success" => false,
                "error" => "Cập nhật thất bại"
            ]);
        }
    }
    
    public function create()
    {
        return $this->render('admin/products/create.php');
    }
    
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        
        $name = trim($_POST['name'] ?? '');
        $price = $_POST['price'] ?? 0;
        $category_id = $_POST['category_id'] ?? 0;
        $description = $_POST['description'] ?? '';
        $stock = $_POST['stock'] ?? 0;
        $image_name = null;
        
        if ($name == '' || $price <= 0) {
            return $this->json(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
        }
        
        if (!empty($_FILES['image']['name'])) {
            $target_dir = UPLOAD_PATH;
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image_name = time() . "_" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
        }
        
        $productModel = new \App\Models\Product();
        $result = $productModel->create($name, $price, $description, $image_name, $stock, $category_id);
        
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
    return $this->render('admin/products/edit.php', ['product' => $product]);
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