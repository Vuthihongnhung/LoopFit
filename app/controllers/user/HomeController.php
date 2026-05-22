<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy số lượng giỏ hàng 
        $this->share('cartCount', $this->getGlobalCartCount());

        // Lấy sản phẩm nổi bật 
        $productModel = new Product();
        $featuredProducts = $productModel->getFeatured(8); 

        return $this->view('user/home', [
            'featuredProducts' => $featuredProducts
        ]);
    }
}