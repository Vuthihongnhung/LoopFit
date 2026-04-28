<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\OrderItem;
use App\Models\Category; // Thêm use Category

class ProductController extends Controller
{
    public function index()
{
    $searchKeyword = $_GET['search'] ?? '';
    $categoryId = $_GET['category'] ?? null;
    $gender     = $_GET['gender'] ?? 'all';
    $priceRange = $_GET['price'] ?? 'all';
    $sort       = $_GET['sort'] ?? 'default';

    $productModel = new Product();
    $categoryModel = new Category();

    // Nếu có từ khóa tìm kiếm -> ưu tiên tìm kiếm
    if (!empty($searchKeyword)) {
        $products = $productModel->searchByName($searchKeyword);
        // Không cần breadcrumb, giữ nguyên các bộ lọc khác (có thể không áp dụng)
        $this->view('user/products', [
            'products'       => $products,
            'current_gender' => $gender,
            'current_price'  => $priceRange,
            'current_cat'    => $categoryId,
            'breadcrumb'     => [],
            'searchKeyword'  => $searchKeyword
        ]);
        return;
    }

    // Xử lý category (nếu có categoryId và nó là category cha)
    $categoryIds = [];
    if ($categoryId) {
        $categoryIds = $categoryModel->getAllDescendants($categoryId);
        if (empty($categoryIds)) {
            $categoryIds = [$categoryId];
        }
    }

    // Truy vấn sản phẩm
    if (!empty($categoryIds)) {
        $products = $productModel->getProductsByCategories($categoryIds, $gender, $priceRange, $sort);
    } else {
        $products = $productModel->filterProducts(null, $gender, $priceRange, $sort);
    }

    // Breadcrumb
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
        'searchKeyword'  => ''
    ]);
}

    // Các method khác giữ nguyên (show, filterApi, searchApi...)
    public function show($id)
    {
        $productModel = new Product();
        $product = $productModel->getWithRating($id);
        if (!$product) {
            http_response_code(404);
            echo "Sản phẩm không tồn tại";
            exit;
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
            $userCanReview = $orderItemModel->hasUserBoughtProduct($_SESSION['user_id'], $id);
        }

        $this->view('user/product_detail', [
            'product' => $product,
            'relatedProducts' => $related,
            'reviews' => $reviews,
            'userCanReview' => $userCanReview
        ]);
    }

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
        // Lấy tất cả id của danh mục cha và các con
        $categoryIds = $categoryModel->getAllDescendants($categoryId);
        if (empty($categoryIds)) {
            $categoryIds = [$categoryId];
        }
    }

    // Gọi method filterProducts (hỗ trợ mảng category và search)
    $products = $productModel->filterProducts($categoryIds, $gender, $price, $sort, $search);

    header('Content-Type: application/json');
    echo json_encode($products);
    exit;
}

    public function searchApi()
    {
        $q = $_GET['q'] ?? '';
        $productModel = new Product();
        $products = $productModel->searchByName($q);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }
}