<?php
namespace App\Models;

use Core\Model;

class OrderItem extends Model
{
    private ?array $columns = null;

    private function columns(): array
    {
        if ($this->columns !== null) return $this->columns;
        $this->columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM order_details");
        if ($result) {
            while ($row = $result->fetch_assoc()) $this->columns[] = $row['Field'];
        }
        return $this->columns;
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    public function create($dataOrOrderId, $productId = null, $quantity = null, $price = null)
    {
        if (is_array($dataOrOrderId)) {
            return $this->createFromArray($dataOrOrderId);
        }

        $sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiid", $dataOrOrderId, $productId, $quantity, $price);
        return $stmt->execute();
    }

    private function createFromArray(array $data): bool
    {
        $map = [
            'order_id' => (int)($data['order_id'] ?? 0),
            'product_id' => (int)($data['product_id'] ?? 0),
            'quantity' => (int)($data['quantity'] ?? 1),
            'price' => (float)($data['price'] ?? 0),
            'color' => $data['color'] ?? null,
            'size' => $data['size'] ?? null,
            'rent_date' => $data['rent_date'] ?? null,
            'return_date' => $data['return_date'] ?? null,
            'days' => (int)($data['days'] ?? 1),
            'deposit' => (float)($data['deposit'] ?? 0),
            'subtotal' => (float)($data['subtotal'] ?? 0),
        ];

        $fields = [];
        $values = [];
        foreach ($map as $field => $value) {
            if ($this->hasColumn($field)) { $fields[] = $field; $values[] = $value; }
        }
        if (!$fields) return false;

        $sql = "INSERT INTO order_details (" . implode(',', $fields) . ") VALUES (" . implode(',', array_fill(0, count($fields), '?')) . ")";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $types = '';
        foreach ($values as $value) $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function getByOrder($orderId)
    {
        $sql = "SELECT od.*, p.name, p.image FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function add($data)
    {
        return $this->createFromArray($data);
    }
}
