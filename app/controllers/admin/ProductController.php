<?php

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use Core\Controller;
use App\Middlewares\AdminMiddleware;

class ProductController extends Controller
{
    protected $productModel;
    protected $categoryModel;
    protected $uploadPath;

    public function __construct()
    {
        AdminMiddleware::check();
        $this->productModel = new Product();
        $this->categoryModel = new Category();

        if (defined('UPLOAD_PATH')) {
            $this->uploadPath = UPLOAD_PATH;
        } else {
            $this->uploadPath = __DIR__ . '/../../../public/uploads/';
        }
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ==================== HIỂN THỊ VIEW  ==================== */
    public function index()
    {
        $products = $this->getProductsWithExtraInfo();
        $parentCategories = $this->categoryModel->getParents();

        ob_start();
        require_once __DIR__ . '/../../../views/admin/products/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Quản lý sản phẩm';
        $activeMenu = 'products';
        require_once __DIR__ . '/../../../views/layouts/admin.php';
    }

    public function create()
    {
        $parentCategories = $this->categoryModel->getParents();
        $this->render('admin/products/create', ['parentCategories' => $parentCategories]);
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect('/admin/products');
        }
        $product = $this->productModel->getById($id);
        if (!$product) {
            $this->redirect('/admin/products');
        }
        $parentCategories = $this->categoryModel->getParents();
        $this->render('admin/products/edit', [
            'product' => $product,
            'parentCategories' => $parentCategories
        ]);
    }

    /* ==================== API JSON  ==================== */
    public function apiGetList()
    {
        $products = $this->getProductsWithExtraInfo();
        return $this->json(['success' => true, 'data' => $products]);
    }

    public function apiGetOne()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            return $this->json(['success' => false, 'error' => 'Thiếu ID sản phẩm']);
        }
        $product = $this->productModel->getById($id);
        if (!$product) {
            return $this->json(['success' => false, 'error' => 'Sản phẩm không tồn tại']);
        }
        // Đảm bảo trả về cấu trúc {success: true, data: product}
        return $this->json(['success' => true, 'data' => $product]);
    }

    /* ==================== CRUD ==================== */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }

        $data = $this->getProductDataFromPost();
        if (!$data['valid']) {
            return $this->json(['success' => false, 'error' => $data['error']]);
        }

        $imageNames = $this->uploadImages($_FILES['images'] ?? null);
        if ($imageNames === false) {
            return $this->json(['success' => false, 'error' => 'Upload ảnh thất bại']);
        }

        $rented_count = (int)($_POST['rented_count'] ?? 0);

        $result = $this->productModel->create(
            $data['name'], $data['price'], $data['discount_percent'], $data['deposit_price'],
            $data['description'], $imageNames, $data['stock'], $data['category_id'],
            $data['sizes'], $data['colors'], $data['gender'], $rented_count
        );

        if ($result) {
            return $this->json(['success' => true, 'message' => 'Thêm sản phẩm thành công']);
        } else {
            return $this->json(['success' => false, 'error' => 'Thêm thất bại']);
        }
    }

    public function update()
    {
        // Kiểm tra phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ'], 405);
        }

        // Lấy và kiểm tra ID
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'ID sản phẩm không hợp lệ'], 400);
        }

        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->getById($id);
        if (!$product) {
            return $this->json(['success' => false, 'error' => 'Sản phẩm không tồn tại'], 404);
        }

        // Lấy và validate dữ liệu từ form
        $data = $this->getProductDataFromPost();
        if (!$data['valid']) {
            return $this->json(['success' => false, 'error' => $data['error']], 400);
        }

        // Xử lý upload ảnh mới 
        $oldImages = $_POST['old_images'] ?? '';
        $newImages = $this->uploadImages($_FILES['images'] ?? null, true, $oldImages);
        if ($newImages === false) {
            return $this->json(['success' => false, 'error' => 'Upload ảnh thất bại'], 500);
        }
        $finalImages = $newImages !== '' ? $newImages : $oldImages;
        $rented_count = (int)($_POST['rented_count'] ?? 0);

        // Cập nhật sản phẩm
        $result = $this->productModel->update(
            $id,
            $data['name'],
            $data['price'],
            $data['discount_percent'],
            $data['deposit_price'],
            $data['description'],
            $finalImages,
            $data['stock'],        
            $data['category_id'],
            $data['sizes'],
            $data['colors'],
            $data['gender'],
            $rented_count
        );

        if ($result) {
            return $this->json(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);
        } else {
            // Lấy lỗi từ model nếu có
            $error = $this->productModel->getLastError() ?? 'Cập nhật thất bại';
            return $this->json(['success' => false, 'error' => $error], 500);
        }
    }
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method không hợp lệ']);
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'ID không hợp lệ']);
        }

        $product = $this->productModel->getById($id);
        if (!$product) {
            return $this->json(['success' => false, 'error' => 'Sản phẩm không tồn tại']);
        }

        // Xóa ảnh
        if (!empty($product['image'])) {
            $images = explode(',', $product['image']);
            foreach ($images as $img) {
                $img = trim($img);
                if ($img && file_exists($this->uploadPath . $img)) {
                    unlink($this->uploadPath . $img);
                }
            }
        }

        $result = $this->productModel->deleteCascade($id);
        if ($result === true) {
            return $this->json(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
        } else {
            return $this->json(['success' => false, 'error' => is_string($result) ? $result : 'Xóa thất bại']);
        }
    }

    /* ==================== HÀM HỖ TRỢ ==================== */

    /**
     * Lấy danh sách sản phẩm kèm thông tin:
     * - image_url (đường dẫn đầy đủ ảnh đầu tiên)
     * - parent_category_id
     * - current_rented (số lượng đang thuê tính từ các đơn hàng thực tế)
     * - stock_available (stock - current_rented)
     */
    private function getProductsWithExtraInfo()
    {
        $products = $this->productModel->getAllWithCategory();
        $baseUrl = BASE_URL ?? '/LoopFit/public';

        $allCats = $this->categoryModel->getAll();
        $catParentMap = [];
        foreach ($allCats as $cat) {
            $catParentMap[$cat['id']] = $cat['parent_id'] ?? 0;
        }

        foreach ($products as &$p) {
            $firstImage = '';
            if (!empty($p['image'])) {
                $imagesArr = explode(',', $p['image']);
                $firstImage = trim($imagesArr[0]);
                if ($firstImage && !preg_match('/\.(jpg|jpeg|png|gif)$/i', $firstImage)) {
                    $firstImage .= '.jpg';
                }
            }
            $p['image_url'] = $firstImage ? $baseUrl . '/uploads/' . $firstImage : null;
            $p['discount_percent'] = (int)($p['discount_percent'] ?? 0);
            $p['parent_category_id'] = $catParentMap[$p['category_id']] ?? 0;

            // DÙNG rented_quantity có sẵn (đã được cập nhật khi xác nhận/trả hàng)
            $p['current_rented'] = (int)($p['rented_quantity'] ?? 0);
            $p['stock_available'] = $p['stock'] - $p['current_rented'];

            $p['current_rented'] = max(0, (int)($p['rented_quantity'] ?? 0));
            $p['stock_available'] = max(0, $p['stock'] - $p['current_rented']);
        }
        return $products;
    }

    /**
     * Lấy dữ liệu từ $_POST 
     */
    private function getProductDataFromPost()
    {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $discount_percent = (int)($_POST['discount_percent'] ?? 0);
        $deposit_price = (float)($_POST['deposit_price'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = $_POST['description'] ?? '';
        $stock = (int)($_POST['stock'] ?? 0);
        $sizes = isset($_POST['sizes']) ? (is_array($_POST['sizes']) ? implode(',', $_POST['sizes']) : $_POST['sizes']) : '';
        $colors = isset($_POST['colors']) ? (is_array($_POST['colors']) ? implode(',', $_POST['colors']) : $_POST['colors']) : '';
        $gender = $_POST['gender'] ?? 'unisex';

        if ($name === '' || $price <= 0 || $category_id <= 0) {
            return ['valid' => false, 'error' => 'Tên, giá và danh mục là bắt buộc'];
        }
        if ($discount_percent < 0 || $discount_percent > 100) {
            return ['valid' => false, 'error' => 'Giảm giá phải từ 0-100%'];
        }
        if ($deposit_price < 0) {
            return ['valid' => false, 'error' => 'Giá cọc không thể âm'];
        }
        if ($stock < 0) {
            return ['valid' => false, 'error' => 'Tồn kho không thể âm'];
        }

        return [
            'valid' => true,
            'name' => $name,
            'price' => $price,
            'discount_percent' => $discount_percent,
            'deposit_price' => $deposit_price,
            'category_id' => $category_id,
            'description' => $description,
            'stock' => $stock,
            'sizes' => $sizes,
            'colors' => $colors,
            'gender' => $gender
        ];
    }

    /**
     * Upload nhiều ảnh, trả về chuỗi tên file cách nhau dấu phẩy
     * @param array|null $files Mảng $_FILES['images']
     * @param bool $deleteOld Có xóa ảnh cũ không (chỉ dùng khi update)
     * @param string $oldImagesString Chuỗi ảnh cũ để xóa nếu $deleteOld=true
     * @return string|false
     */
    private function uploadImages($files, $deleteOld = false, $oldImagesString = '')
    {
        if (empty($files) || empty($files['name'][0])) {
            return ''; // không có ảnh mới
        }

        $uploaded = [];
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] !== UPLOAD_ERR_OK) continue;
            $fileName = time() . '_' . uniqid() . '_' . basename($files['name'][$key]);
            if (move_uploaded_file($tmpName, $this->uploadPath . $fileName)) {
                $uploaded[] = $fileName;
            } else {
                return false;
            }
        }

        if ($deleteOld && !empty($oldImagesString)) {
            $oldArr = explode(',', $oldImagesString);
            foreach ($oldArr as $old) {
                $old = trim($old);
                if ($old && file_exists($this->uploadPath . $old)) {
                    unlink($this->uploadPath . $old);
                }
            }
        }

        return implode(',', $uploaded);
    }

    private function redirect($url)
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
}