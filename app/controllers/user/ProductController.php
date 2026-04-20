<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
{
    $categoryId = $_GET['category'] ?? null;
    $gender     = $_GET['gender'] ?? 'all';
    $priceRange = $_GET['price'] ?? 'all';
    $sort       = $_GET['sort'] ?? 'default';

    $productModel = new Product();
    $products = $productModel->filterProducts($categoryId, $gender, $priceRange, $sort);

    // Lấy thông tin breadcrumb nếu có category
    $breadcrumb = [];
    if ($categoryId) {
        $categoryModel = new \App\Models\Category();
        $breadcrumb = $categoryModel->getBreadcrumb($categoryId);
    }

    $this->view('user/products', [
        'products'       => $products,
        'current_gender' => $gender,
        'current_price'  => $priceRange,
        'current_cat'    => $categoryId,
        'breadcrumb'     => $breadcrumb
    ]);
}

   public function show($id)
{
    $productModel = new Product();
    $product = $productModel->getById($id);
    if (!$product) {
        http_response_code(404);
        echo "Sản phẩm không tồn tại";
        exit;
    }
    // Lấy sản phẩm cùng danh mục (dựa trên category_id)
    $related = [];
    if (!empty($product['category_id'])) {
        $related = $productModel->getRelatedProducts($product['category_id'], $id, 4);
    }
    $this->view('user/product_detail', [
        'product' => $product,
        'relatedProducts' => $related
    ]);
}

   public function filterApi()
{
    $category = $_GET['category'] ?? null;
    $gender = $_GET['gender'] ?? 'all';
    $price = $_GET['price'] ?? 'all';

    $productModel = new Product();
    $products = $productModel->filterProducts($category, $gender, $price, 'default');

    header('Content-Type: application/json');
    echo json_encode($products);
    exit;
}
}