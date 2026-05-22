<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Product;

class SearchController extends Controller
{
    /* ===== Hiển thị trang kết quả tìm kiếm ===== */
    public function index()
    {
        $keyword = trim($_GET['q'] ?? $_GET['keyword'] ?? '');
        $products = $this->searchProducts($keyword);

        if ($this->wantsJson()) {
            return $this->jsonResponse([
                'status' => 'success',
                'keyword' => $keyword,
                'data' => $products
            ]);
        }

        return $this->view('user/search', [
            'keyword' => $keyword,
            'products' => $products
        ]);
    }

    /* ===== API endpoint riêng cho tìm kiếm (luôn trả về JSON) ===== */
    public function api()
    {
        $keyword = trim($_GET['q'] ?? $_GET['keyword'] ?? '');
        return $this->jsonResponse([
            'status' => 'success',
            'keyword' => $keyword,
            'data' => $this->searchProducts($keyword)
        ]);
    }

    /* ===== Tìm kiếm sản phẩm theo từ khóa ===== */
    private function searchProducts(string $keyword): array
    {
        if ($keyword === '') {
            return [];
        }

        $productModel = new Product();
        if (method_exists($productModel, 'search')) {
            return $productModel->search($keyword);
        }

        return [];
    }

    private function wantsJson(): bool
    {
        return isset($_GET['format']) && $_GET['format'] === 'json'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
