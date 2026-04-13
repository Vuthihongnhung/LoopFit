<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $style       = $_GET['style'] ?? null;
        $gender      = $_GET['gender'] ?? 'all';
        $price_range = $_GET['price'] ?? 'all';
        $category_id = $_GET['category'] ?? null;

        $productModel = new Product(); 
        
        $products = $productModel->getFilteredProducts(
            $category_id,
            $gender,
            $price_range,
            $style
        );

        // ✅ DÙNG view() thay vì render()
        return $this->view('user.products', [
            'products'       => $products,
            'current_style'  => $style,
            'current_gender' => $gender,
            'current_price'  => $price_range,
            'current_cat'    => $category_id
        ]);
    }

    public function show($id)
{
    $productModel = new Product();
    $product = $productModel->getById($id);

    // 🔥 CHẶN NULL
    if (!$product) {
        die('Không tìm thấy sản phẩm');
    }

    return $this->view('user.product_detail', [
        'product' => $product
    ]);
}

public function filterApi()
{
    header('Content-Type: application/json');

    $gender = $_GET['gender'] ?? 'all';
    $price  = $_GET['price'] ?? 'all';

    $productModel = new Product();

    $products = $productModel->getFilteredProducts(
        null,
        $gender,
        $price,
        null
    );

    echo json_encode($products);
    exit; // 🔥 QUAN TRỌNG
}
}