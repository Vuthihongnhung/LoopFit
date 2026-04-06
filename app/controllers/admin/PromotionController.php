<?php
namespace App\Controllers\Admin;

use App\Models\Promotion;
use Core\Controller;
use App\Middlewares\AdminMiddleware;

class PromotionController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::check();
    }
    
    public function index()
    {
        return $this->render('admin/promotions/index.php');
    }
    
    public function apiGetList()
    {
        $promotionModel = new Promotion();
        $promotions = $promotionModel->getAll();
        
        $formattedPromotions = [];
        foreach ($promotions as $row) {
            $now = date("Y-m-d H:i:s");
            
            if ($now < $row['start_date']) {
                $status = "upcoming";
            } elseif ($now > $row['end_date']) {
                $status = "expired";
            } else {
                $status = "active";
            }
            
            $formattedPromotions[] = [
                "id" => (int)$row["id"],
                "code" => $row["code"],
                "discount_percent" => (int)$row["discount_percent"],
                "start_date" => $row["start_date"],
                "end_date" => $row["end_date"],
                "status" => $status
            ];
        }
        
        return $this->json([
            "success" => true,
            "data" => $formattedPromotions
        ]);
    }
    
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->json([
                "success" => false,
                "error" => "Method không hợp lệ"
            ]);
        }
        
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discount = $_POST['discount_percent'] ?? 0;
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;
        
        if ($code == '' || $discount <= 0 || $discount > 100) {
            return $this->json([
                "success" => false,
                "error" => "Dữ liệu không hợp lệ"
            ]);
        }
        
        $promotionModel = new Promotion();
        
        if ($promotionModel->checkExists($code)) {
            return $this->json([
                "success" => false,
                "error" => "Mã giảm giá đã tồn tại"
            ]);
        }
        
        $result = $promotionModel->create($code, $discount, $start, $end);
        
        if ($result) {
            return $this->json([
                "success" => true,
                "message" => "Tạo mã thành công",
                "id" => $promotionModel->getLastId()
            ]);
        } else {
            return $this->json([
                "success" => false,
                "error" => "Tạo mã thất bại"
            ]);
        }
    }
}