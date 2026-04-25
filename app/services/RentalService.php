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

    public function isDateValid($rentDate, $returnDate): bool
    {
        return strtotime($rentDate) !== false
            && strtotime($returnDate) !== false
            && strtotime($rentDate) <= strtotime($returnDate);
    }

    public function calculateRentalDays($rentDate, $returnDate): int
    {
        if (!$this->isDateValid($rentDate, $returnDate)) {
            return 0;
        }
        return (new \DateTime($rentDate))->diff(new \DateTime($returnDate))->days + 1;
    }

    public function calculateLateFee($expectedReturnDate, $actualReturnDate, float $dailyLateFee = 50000): float
    {
        if (empty($expectedReturnDate) || empty($actualReturnDate)) {
            return 0;
        }

        $expected = new \DateTime($expectedReturnDate);
        $actual = new \DateTime($actualReturnDate);

        if ($actual <= $expected) {
            return 0;
        }

        return $expected->diff($actual)->days * $dailyLateFee;
    }

    public function hasConflict($productId, $rentDate, $returnDate, $requestedQty = 1): bool
    {
        if (!$this->isDateValid($rentDate, $returnDate)) {
            return true;
        }

        $sqlStock = "SELECT stock FROM products WHERE id = ?";
        $stmtStock = $this->conn->prepare($sqlStock);
        $stmtStock->bind_param("i", $productId);
        $stmtStock->execute();
        $product = $stmtStock->get_result()->fetch_assoc();
        $totalStock = (int)($product['stock'] ?? 0);

        $sqlRented = "SELECT COALESCE(SUM(od.quantity), 0) AS rented_total
                      FROM order_details od
                      JOIN orders o ON od.order_id = o.id
                      WHERE od.product_id = ?
                        AND o.status IN ('pending', 'confirmed', 'renting')
                        AND COALESCE(od.rent_date, o.rent_date) <= ?
                        AND COALESCE(od.return_date, o.return_date) >= ?";

        $stmtRented = $this->conn->prepare($sqlRented);
        $stmtRented->bind_param("iss", $productId, $returnDate, $rentDate);
        $stmtRented->execute();
        $rentedTotal = (int)($stmtRented->get_result()->fetch_assoc()['rented_total'] ?? 0);

        return ($rentedTotal + (int)$requestedQty) > $totalStock;
    }

    public function checkCartConflict($cartItems, $rentDate = null, $returnDate = null): array
    {
        foreach ($cartItems as $item) {
            $qty = (int)($item['quantity'] ?? 1);
            $itemRentDate = $rentDate ?: ($item['rent_date'] ?? null);
            $itemReturnDate = $returnDate ?: ($item['return_date'] ?? null);

            if ($this->hasConflict($item['product_id'], $itemRentDate, $itemReturnDate, $qty)) {
                return [
                    'success' => false,
                    'message' => 'Sản phẩm "' . ($item['name'] ?? ('#' . $item['product_id'])) . '" không đủ số lượng trong khoảng thời gian đã chọn.'
                ];
            }
        }

        return ['success' => true];
    }
}
