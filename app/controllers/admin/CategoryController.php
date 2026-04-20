<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Middlewares\AdminMiddleware;
use App\Models\Category;

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
    header('Content-Type: application/json');
    
    $categoryModel = new Category();
    
    // Lấy danh mục cha (parent_id = 0 hoặc NULL)
    $parents = $categoryModel->getParents();
    
    $options = [];
    
    foreach ($parents as $parent) {
        // Thêm danh mục cha
        $options[] = [
            'id' => $parent['id'],
            'name' => '📁 ' . $parent['name'],
            'parent_id' => 0
        ];
        
        // Lấy danh mục con
        $children = $categoryModel->getChildren($parent['id']);
        foreach ($children as $child) {
            $options[] = [
                'id' => $child['id'],
                'name' => '  ├─ ' . $child['name'],
                'parent_id' => $parent['id']
            ];
        }
    }
    
    return $this->json(['success' => true, 'data' => $options]);
}
    
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        
        $name = trim($_POST['name'] ?? '');
        $slug = $this->slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $icon = $_POST['icon'] ?? null;
        
        if ($name == '') {
            return $this->json(['success' => false, 'error' => 'Tên danh mục không được để trống']);
        }
        
        $categoryModel = new Category();
        $result = $categoryModel->create($name, $slug, $parentId, $icon);
        
        return $this->json(['success' => $result, 'message' => $result ? 'Thêm thành công' : 'Thêm thất bại']);
    }
    
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = $this->slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $icon = $_POST['icon'] ?? null;
        
        if ($id <= 0 || $name == '') {
            return $this->json(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
        }
        
        $categoryModel = new Category();
        $result = $categoryModel->update($id, $name, $slug, $parentId, $icon);
        
        return $this->json(['success' => $result, 'message' => $result ? 'Cập nhật thành công' : 'Cập nhật thất bại']);
    }
    
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $categoryModel = new Category();
        
        $count = $categoryModel->countProducts($id);
        if ($count > 0) {
            return $this->json(['success' => false, 'error' => 'Không thể xóa vì còn sản phẩm thuộc danh mục này']);
        }
        
        $result = $categoryModel->delete($id);
        return $this->json(['success' => $result, 'message' => $result ? 'Xóa thành công' : 'Xóa thất bại']);
    }
    
    private function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return $text ?: 'n-a';
    }
}