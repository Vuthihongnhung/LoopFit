<?php
namespace App\Services;

use Core\Database;

class RentalService
{
    protected $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function isDateValid($rentDate, $returnDate)
    {
        return strtotime($rentDate) <= strtotime($returnDate);
    }

    public function hasConflict($productId, $rentDate, $returnDate)
    {
        $sql = "SELECT COUNT(*) as total
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                WHERE od.product_id = ?
                  AND o.status IN ('pending', 'confirmed', 'renting')
                  AND o.rent_date <= ?
                  AND o.return_date >= ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $productId, $returnDate, $rentDate);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        return ($row['total'] ?? 0) > 0;
    }

    public function checkCartConflict($cartItems, $rentDate, $returnDate)
    {
        foreach ($cartItems as $item) {
            if ($this->hasConflict($item['product_id'], $rentDate, $returnDate)) {
                return [
                    'success' => false,
                    'message' => 'Sản phẩm "' . $item['name'] . '" đã được thuê trong khoảng thời gian này.'
                ];
            }
        }

        return [
            'success' => true
        ];
    }
}