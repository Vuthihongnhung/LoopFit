<?php

require_once __DIR__ . '/../models/Product.php';

class ProductController
{
    public function index()
    {
        $product = new Product();
        echo json_encode($product->getAll(), JSON_UNESCAPED_UNICODE);
    }

    public function show($id)
    {
        $product = new Product();
        $data = $product->getById($id);

        if (!$data) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}