<?php
namespace App\Models;

use Core\Model;

class Payment extends Model
{
    public function create($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO payments (order_id, amount, method, status, paid_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("idss", $data['order_id'], $data['amount'], $data['method'], $data['status']);
        return $stmt->execute();
    }
}