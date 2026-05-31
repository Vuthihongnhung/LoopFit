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

        // Tính final_price cho từng sản phẩm
        foreach ($featuredProducts as &$p) {
            $p['final_price'] = $productModel->getFinalPrice($p['id'], $p['price'], $p['category_id'] ?? null);
        }

        return $this->view('user/home', [
            'featuredProducts' => $featuredProducts
        ]);
    }
}