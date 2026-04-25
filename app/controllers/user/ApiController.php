<?php
namespace App\Controllers\User;

use Core\Controller;
use App\Models\Order;
use App\Services\PromotionService;

class ApiController extends Controller
{
    public function chartData()
    {
        $orderModel = new Order();
        return $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'monthly_revenue' => $orderModel->getMonthlyRevenue(6),
                'total_orders' => $orderModel->countAll(),
                'total_revenue' => $orderModel->getRevenue(),
            ]
        ]);
    }

    public function vouchers()
    {
        $service = new PromotionService();
        return $this->jsonResponse([
            'status' => 'success',
            'data' => $service->getActiveVouchers()
        ]);
    }

    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
