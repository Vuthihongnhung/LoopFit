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

    /**
     * Kiểm tra xem sản phẩm có đủ số lượng trong kho cho khoảng thời gian này không
     */
    public function hasConflict($productId, $rentDate, $returnDate, $requestedQty = 1)
    {
        // 1. Lấy tổng số lượng sản phẩm đang có trong kho (Total Stock)
        $sqlStock = "SELECT stock FROM products WHERE id = ?";
        $stmtStock = $this->conn->prepare($sqlStock);
        $stmtStock->bind_param("i", $productId);
        $stmtStock->execute();
        $product = $stmtStock->get_result()->fetch_assoc();
        $totalStock = $product['stock'] ?? 0;

        // 2. Tính tổng số lượng đã được thuê (Status: pending, confirmed, renting) trong khoảng thời gian này
        // Công thức Overlap: (Ngày thuê cũ <= Ngày trả mới) AND (Ngày trả cũ >= Ngày thuê mới)
        $sqlRented = "SELECT SUM(od.quantity) as rented_total
                      FROM order_details od
                      JOIN orders o ON od.order_id = o.id
                      WHERE od.product_id = ?
                        AND o.status IN ('pending', 'confirmed', 'renting')
                        AND o.rent_date <= ? 
                        AND o.return_date >= ?";
        
        $stmtRented = $this->conn->prepare($sqlRented);
        $stmtRented->bind_param("iss", $productId, $returnDate, $rentDate);
        $stmtRented->execute();
        $rentedTotal = $stmtRented->get_result()->fetch_assoc()['rented_total'] ?? 0;

        // 3. Kiểm tra: Nếu (Đã thuê + Số lượng muốn thuê mới) > Tổng kho => Xung đột (Hết đồ)
        return ($rentedTotal + $requestedQty) > $totalStock;
    }

    /**
     * Duyệt qua danh sách giỏ hàng để kiểm tra từng món
     */
    public function checkCartConflict($cartItems, $rentDate, $returnDate)
    {
        foreach ($cartItems as $item) {
            // Lấy quantity từ giỏ hàng truyền vào
            $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
            
            if ($this->hasConflict($item['product_id'], $rentDate, $returnDate, $qty)) {
                return [
                    'success' => false,
                    'message' => 'Sản phẩm "' . $item['name'] . '" không đủ số lượng trong khoảng thời gian từ ' . $rentDate . ' đến ' . $returnDate . '.'
                ];
            }
        }

        return ['success' => true];
    }
}