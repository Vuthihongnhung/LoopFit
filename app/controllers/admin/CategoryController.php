<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Middlewares\AdminMiddleware;
use App\Models\Category;
error_reporting(E_ALL & ~E_NOTICE);
class CategoryController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::check();
    }
    
    public function index()
    {
        ob_start();
        require_once __DIR__ . '/../../../views/admin/categories/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Quản lý danh mục';
        $activeMenu = 'categories';
        require_once __DIR__ . '/../../../views/layouts/admin.php';
    }
    
    public function apiGetList()
    {
        // Xóa buffer trước khi gửi JSON
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $categoryModel = new Category();
        $parents = $categoryModel->getParents();
        
        $result = [];
        foreach ($parents as $parent) {
            $item = [
                'id' => $parent['id'],
                'name' => $parent['name'],
                'slug' => $parent['slug'],
                'icon' => $parent['icon'],
                'parent_id' => 0,
                'children' => []
            ];
            $children = $categoryModel->getChildren($parent['id']);
            foreach ($children as $child) {
                $item['children'][] = [
                    'id' => $child['id'],
                    'name' => $child['name'],
                    'slug' => $child['slug'],
                    'icon' => $child['icon'],
                    'parent_id' => $parent['id']
                ];
            }
            $result[] = $item;
        }
        
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }
    
    public function store()
    {
        // Xóa buffer trước khi gửi JSON
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method không hợp lệ']);
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $slug = $this->slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $icon = $_POST['icon'] ?? null;
        
        if ($name == '') {
            echo json_encode(['success' => false, 'error' => 'Tên danh mục không được để trống']);
            exit;
        }
        
        $categoryModel = new Category();
        $result = $categoryModel->create($name, $slug, $parentId, $icon, 0);
        
        echo json_encode([
            'success' => $result, 
            'message' => $result ? 'Thêm thành công' : 'Thêm thất bại'
        ]);
        exit;
    }
    
    public function update()
    {
        // Xóa buffer trước khi gửi JSON
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method không hợp lệ']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = $this->slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $icon = $_POST['icon'] ?? null;
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        if ($id <= 0 || $name == '') {
            echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
            exit;
        }
        
        $categoryModel = new Category();
        $result = $categoryModel->update($id, $name, $slug, $parentId, $icon, $displayOrder);
        
        echo json_encode([
            'success' => $result, 
            'message' => $result ? 'Cập nhật thành công' : 'Cập nhật thất bại'
        ]);
        exit;
    }
    
    public function delete()
    {
        // Xóa buffer trước khi gửi JSON
        if (ob_get_level() > 0) {
            ob_clean();
        }
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
        
        $categoryModel = new Category();
        
        $count = $categoryModel->countProducts($id);
        if ($count > 0) {
            echo json_encode([
                'success' => false, 
                'error' => "Không thể xóa vì còn {$count} sản phẩm thuộc danh mục này"
            ]);
            exit;
        }
        
        $result = $categoryModel->delete($id);
        
        echo json_encode([
            'success' => $result, 
            'message' => $result ? 'Xóa thành công' : 'Xóa thất bại'
        ]);
        exit;
    }
    
    private function slugify($text)
{
    // Chuyển sang lowercase
    $text = mb_strtolower($text, 'UTF-8');
    
    // Mảng thay thế tiếng Việt
    $replacements = [
        'đ' => 'd',
        'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
        'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
        'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
        'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
        'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
        'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
        'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
        'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
        'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
        'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
    ];
    
    // Thay thế ký tự có dấu
    $text = strtr($text, $replacements);
    
    // Thay thế khoảng trắng và ký tự đặc biệt bằng dấu gạch ngang
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    
    // Xóa dấu gạch ngang thừa
    $text = trim($text, '-');
    
    return $text ?: 'n-a';
}
}