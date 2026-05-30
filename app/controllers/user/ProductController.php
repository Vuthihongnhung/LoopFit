<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\OrderItem;
use App\Models\Category;

class ProductController extends Controller
{ 
    /* ===== Hiển thị trang danh sách sản phẩm ===== */
    public function index()
    {
    $searchKeyword = $_GET['search'] ?? '';
    $categoryId = $_GET['category'] ?? null;
    $gender     = $_GET['gender'] ?? 'all';
    $priceRange = $_GET['price'] ?? 'all';
    $sort       = $_GET['sort'] ?? 'default';
    $filter     = $_GET['filter'] ?? ''; // thêm: lọc đặc biệt (discount, ...)

    $productModel = new Product();
    $categoryModel = new Category();

    // Xử lý tìm kiếm từ khóa
    if (!empty($searchKeyword)) {
        $products = $productModel->searchByName($searchKeyword);
        $this->view('user/products', [
            'products'       => $products,
            'current_gender' => $gender,
            'current_price'  => $priceRange,
            'current_cat'    => $categoryId,
            'breadcrumb'     => [],
            'searchKeyword'  => $searchKeyword,
            'isDiscountPage' => false
        ]);
        return;
    }

    // Lọc sản phẩm giảm giá
    if ($filter === 'discount') {
    $products = $productModel->getDiscountedProducts();
    foreach ($products as &$p) {
        $p['final_price'] = $productModel->getFinalPrice($p['id'], $p['price'], $p['category_id'] ?? null);
    }
    $this->view('user/products', [
        'products'       => $products,
        'current_gender' => $gender,
        'current_price'  => $priceRange,
        'current_cat'    => $categoryId,
        'breadcrumb'     => [],
        'searchKeyword'  => '',
        'isDiscountPage' => true
    ]);
    return;
}

    // Lọc sản phẩm mới (hàng mới về)
    if ($filter === 'new') {
    $products = $productModel->getNewProducts();
    foreach ($products as &$p) {
        $p['final_price'] = $productModel->getFinalPrice($p['id'], $p['price'], $p['category_id'] ?? null);
    }
    $this->view('user/products', [
        'products'       => $products,
        'current_gender' => $gender,
        'current_price'  => $priceRange,
        'current_cat'    => $categoryId,
        'breadcrumb'     => [],
        'searchKeyword'  => '',
        'isDiscountPage' => false,
        'isNewPage'      => true
    ]);
    return;
}

    // Lọc theo danh mục (bao gồm cả danh mục con)
    $categoryIds = [];
    if ($categoryId) {
        // Lấy tất cả danh mục con của danh mục cha
        $categoryIds = $categoryModel->getAllDescendants($categoryId);
        if (empty($categoryIds)) {
            $categoryIds = [$categoryId];
        }
    }

    if (!empty($categoryIds)) {
        $products = $productModel->getProductsByCategories($categoryIds, $gender, $priceRange, $sort);
    } else {
        $products = $productModel->filterProducts(null, $gender, $priceRange, $sort);
    }

    // Tính giá sau bulk promotion cho từng sản phẩm
foreach ($products as &$p) {
    $p['final_price'] = $productModel->getFinalPrice($p['id'], $p['price'], $p['category_id'] ?? null);
}

    // Tạo breadcrumb (đường dẫn điều hướng) cho danh mục
    $breadcrumb = [];
    if ($categoryId) {
        $breadcrumb = $categoryModel->getBreadcrumb($categoryId);
    }

    $this->view('user/products', [
        'products'       => $products,
        'current_gender' => $gender,
        'current_price'  => $priceRange,
        'current_cat'    => $categoryId,
        'breadcrumb'     => $breadcrumb,
        'searchKeyword'  => '',
        'isDiscountPage' => false
    ]);
    }

    /* ===== API: Lấy danh sách biến thể (size, màu sắc) của sản phẩm ===== */
    public function getVariants() {
        header('Content-Type: application/json');
        $productId = (int)($_GET['product_id'] ?? 0);
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu product_id']);
            return;
        }
        $productModel = new Product();
        $product = $productModel->getById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            return;
        }
        $sizes = explode(',', $product['sizes'] ?? 'S,M,L,XL');
        $colors = explode(',', $product['colors'] ?? 'Trắng,Đen,Đỏ');
        echo json_encode([
            'success' => true,
            'sizes' => array_map('trim', $sizes),
            'colors' => array_map('trim', $colors)
        ]);
    }

    public function getOptions() {
    header('Content-Type: application/json');
    $productId = (int)($_GET['id'] ?? 0);
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    $productModel = new Product();
    $product = $productModel->getById($productId);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    // Giả sử trong bảng products có cột sizes và colors 
    $sizes = array_map('trim', explode(',', $product['sizes'] ?? 'S,M,L,XL'));
    $colors = array_map('trim', explode(',', $product['colors'] ?? 'Trắng,Đen,Đỏ'));
    echo json_encode([
        'success' => true,
        'sizes' => $sizes,
        'colors' => $colors
    ]);
    }

    /* ===== Hiển thị chi tiết sản phẩm ===== */
    public function show($id)
    {
    $productModel = new Product();
    $product = $productModel->getWithRating($id);
    if (!$product) {
        http_response_code(404);
        echo "Sản phẩm không tồn tại";
        exit;
    }

    // Tính số lượng có sẵn và trạng thái tồn kho
    $stock = (int)($product['stock'] ?? 0);
    $rentedQuantity = (int)($product['rented_quantity'] ?? 0);
    $available = $stock - $rentedQuantity;

    if ($available > 0) {
        $stockStatus = 'instock';      // Còn hàng
    } elseif ($rentedQuantity > 0) {
        $stockStatus = 'rented';       // Đang được thuê hết
    } else {
        $stockStatus = 'outofstock';   // Hết hàng (stock = 0)
    }

    // Tính giá hiện tại (ưu tiên bulk promotion trước)
$bulkPrice = $productModel->getFinalPrice($id, $product['price'], $product['category_id'] ?? null);
$discountPercent = (int)($product['discount_percent'] ?? 0);
$hasBulkDiscount = false;

if ($bulkPrice != $product['price']) {
    // Có bulk promotion
    $currentPrice = $bulkPrice;
    $hasBulkDiscount = true;
} elseif ($discountPercent > 0) {
    // Chỉ có discount_percent
    $currentPrice = $product['price'] * (100 - $discountPercent) / 100;
    $hasBulkDiscount = false;
} else {
    $currentPrice = $product['price'];
    $hasBulkDiscount = false;
}

    $related = [];
    if (!empty($product['category_id'])) {
        $related = $productModel->getRelatedProducts($product['category_id'], $id, 4);
    }

    $reviewModel = new Review();
    $reviews = $reviewModel->getByProduct($id);

    $userCanReview = false;
    if (isset($_SESSION['user_id'])) {
        $orderItemModel = new OrderItem();
        $userCanReview = $orderItemModel->hasUserBoughtProduct($id, $_SESSION['user_id']);
    }

    $this->view('user/product_detail', [
    'product' => $product,
    'currentPrice' => $currentPrice,
    'hasBulkDiscount' => $hasBulkDiscount,
    'bulkDiscountPercent' => $hasBulkDiscount ? round((1 - $currentPrice / $product['price']) * 100) : 0,
    'relatedProducts' => $related,
    'reviews' => $reviews,
    'userCanReview' => $userCanReview,
    'available' => $available,           
    'stockStatus' => $stockStatus        
]);
    }

    /* ===== API: Lọc sản phẩm và trả về JSON ===== */
    public function filterApi()
{
    $categoryId = $_GET['category'] ?? null;
    $gender = $_GET['gender'] ?? 'all';
    $price = $_GET['price'] ?? 'all';
    $sort = $_GET['sort'] ?? 'default';
    $search = $_GET['search'] ?? '';

    $productModel = new Product();
    $categoryModel = new \App\Models\Category();

    $categoryIds = [];
    if ($categoryId) {
        $categoryIds = $categoryModel->getAllDescendants($categoryId);
        if (empty($categoryIds)) {
            $categoryIds = [$categoryId];
        }
    }

    $products = $productModel->filterProducts($categoryIds, $gender, $price, $sort, $search);

    // Tính final_price cho từng sản phẩm
    foreach ($products as &$p) {
        $p['final_price'] = $productModel->getFinalPrice($p['id'], $p['price'], $p['category_id'] ?? null);
    }

    header('Content-Type: application/json');
    echo json_encode($products);
    exit;
}

    /* ===== API: Tìm kiếm sản phẩm theo từ khóa (dùng cho dropdown search) ===== */
    public function searchApi()
    {
        $q = $_GET['q'] ?? '';
        $productModel = new Product();
        $products = $productModel->searchByName($q);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    /* ===== API kiểm tra số lượng sản phẩm khả dụng trong khoảng thời gian ===== */
    public function checkStock() {
    header('Content-Type: application/json');
    
    $productId = (int)($_GET['product_id'] ?? 0);
    $rentDate = $_GET['rent_date'] ?? '';
    $returnDate = $_GET['return_date'] ?? '';
    $quantity = (int)($_GET['quantity'] ?? 1);
    
    if (!$productId || !$rentDate || !$returnDate) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
        return;
    }
    
    $productModel = new Product();
    $available = $productModel->getAvailableQuantity($productId, $rentDate, $returnDate);
    
    echo json_encode([
        'success' => true,
        'available' => $available
    ]);
}
}