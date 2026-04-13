<?php
namespace App\Models;

use Core\Model;

class Order extends Model
{
    protected $conn;
    
    public function __construct()
    {
        $this->conn = $this->getConnection(); // hoặc Database::getConnection()
    }
    
    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM orders");
        return $result->fetch_assoc()['total'];
    }
    
    public function getRevenue()
    {
        $result = $this->conn->query("SELECT SUM(total_price) as total FROM orders WHERE status='returned'");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    public function getAll($limit = null)
    {
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.id DESC";
        if ($limit) $sql .= " LIMIT $limit";
        $result = $this->conn->query($sql);
        $orders = [];
        while ($row = $result->fetch_assoc()) $orders[] = $row;
        return $orders;
    }
    
    public function getDetail($orderId)
    {
        $stmt = $this->conn->prepare("SELECT o.*, u.name as user_name, u.address 
                                      FROM orders o 
                                      LEFT JOIN users u ON o.user_id = u.id 
                                      WHERE o.id=?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        $stmtItems = $this->conn->prepare("SELECT od.*, p.name as product_name 
                                           FROM order_details od 
                                           LEFT JOIN products p ON od.product_id = p.id 
                                           WHERE od.order_id=?");
        $stmtItems->bind_param("i", $orderId);
        $stmtItems->execute();
        $items = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return ['order' => $order, 'items' => $items];
    }
    
    public function updateStatus($orderId, $status)
    {
        $stmt = $this->conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }
    public function create($userId, $rentDate, $returnDate, $totalPrice, $status = 'pending')
{
    $sql = "INSERT INTO orders (user_id, rent_date, return_date, total_price, status)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("issds", $userId, $rentDate, $returnDate, $totalPrice, $status);

    if ($stmt->execute()) {
        return $this->conn->insert_id;
    }

    return false;
}

public function getByUser($userId)
{
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

}