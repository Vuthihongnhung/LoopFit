<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy số lượng giỏ hàng (nếu có hàm helper)
        $this->share('cartCount', $this->getGlobalCartCount());

        // Lấy sản phẩm nổi bật (ví dụ: 8 sản phẩm mới nhất hoặc có is_featured = 1)
        $productModel = new Product();
        $featuredProducts = $productModel->getFeatured(8); // cần method này trong Product model

        return $this->view('user/home', [
            'featuredProducts' => $featuredProducts
        ]);
    }
}