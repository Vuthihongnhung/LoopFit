<?php
namespace App\Controllers\User;
use App\Models\Product;

use Core\Controller;
// Nạp file Product.php nếu autoloader chưa nhận
require_once dirname(__DIR__, 2) . '/models/Product.php';

class ProductController extends Controller {
    public function index() {
        $style       = $_GET['style'] ?? null;
        $gender      = $_GET['gender'] ?? 'all';
        $price_range = $_GET['price'] ?? 'all';
        $category_id = $_GET['category'] ?? null;

        // Khởi tạo class Product (vì Product.php không có namespace nên dùng dấu \)
        $productModel = new Product(); 
        
        $products = $productModel->getFilteredProducts($category_id, $gender, $price_range, $style);

        // Gọi đúng hàm render hoặc view tùy theo Controller.php của bạn
        $this->render('user/products', [
            'products'       => $products,
            'current_style'  => $style,
            'current_gender' => $gender,
            'current_price'  => $price_range,
            'current_cat'    => $category_id
        ]);
    }
}