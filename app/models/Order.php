<?php
namespace App\Models;

use Core\Model;

class Order extends Model
{
    private ?array $columns = null;

    private function columns(): array
    {
        if ($this->columns !== null) return $this->columns;
        $this->columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM orders");
        if ($result) {
            while ($row = $result->fetch_assoc()) $this->columns[] = $row['Field'];
        }
        return $this->columns;
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    private function tableExists(string $table): bool
    {
        $safe = $this->conn->real_escape_string($table);
        $result = $this->conn->query("SHOW TABLES LIKE '$safe'");
        return $result && $result->num_rows > 0;
    }

    public function getAll()
    {
        $sql = "SELECT o.*, u.name AS user_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id)
    {
        $sql = "SELECT o.*, u.name AS user_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) die('Prepare getById order lỗi: ' . $this->conn->error);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPaymentInfo($id)
    {
        $select = "o.*, u.name AS user_name, u.email, u.phone";
        $joinPayment = '';
        $orderBy = 'o.id DESC';
        if ($this->tableExists('payments')) {
            $select .= ", p.method AS paid_method, p.status AS paid_status, p.amount AS paid_amount";
            $joinPayment = " LEFT JOIN payments p ON p.order_id = o.id";
            $orderBy = 'p.id DESC';
        }
        $sql = "SELECT $select FROM orders o LEFT JOIN users u ON o.user_id = u.id $joinPayment WHERE o.id = ? ORDER BY $orderBy LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return $this->getById($id);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data)
    {
        $map = [
            'user_id' => (int)($data['user_id'] ?? 0),
            'address_id' => (int)($data['address_id'] ?? 0),
            'rent_date' => $data['rent_date'] ?? null,
            'return_date' => $data['return_date'] ?? null,
            'total_price' => (float)($data['final_total'] ?? $data['total_price'] ?? 0),
            'deposit_amount' => (float)($data['total_deposit'] ?? $data['deposit_amount'] ?? 0),
            'total_rent' => (float)($data['total_rent'] ?? 0),
            'total_deposit' => (float)($data['total_deposit'] ?? 0),
            'discount' => (float)($data['discount'] ?? 0),
            'final_total' => (float)($data['final_total'] ?? $data['total_price'] ?? 0),
            'payment_method' => $data['payment_method'] ?? 'cash',
            'payment_status' => $data['payment_status'] ?? 'unpaid',
            'notes' => $data['notes'] ?? '',
            'status' => $data['status'] ?? 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $fields = [];
        $values = [];
        foreach ($map as $field => $value) {
            if ($this->hasColumn($field)) { $fields[] = $field; $values[] = $value; }
        }
        if (!$fields) return false;
        $sql = "INSERT INTO orders (" . implode(',', $fields) . ") VALUES (" . implode(',', array_fill(0, count($fields), '?')) . ")";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) die('Prepare create order lỗi: ' . $this->conn->error);
        $types = '';
        foreach ($values as $value) $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $stmt->bind_param($types, ...$values);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    public function getByUser($userId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC");
        if (!$stmt) die('Prepare getByUser lỗi: ' . $this->conn->error);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByUserId($userId) { return $this->getByUser($userId); }

    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if (!$stmt) die('Prepare updateStatus lỗi: ' . $this->conn->error);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function updatePaymentStatus($id, $status)
    {
        if (!$this->hasColumn('payment_status')) return true;
        $stmt = $this->conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM orders");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)$row['total'];
    }

    public function getRevenue()
    {
        $field = $this->hasColumn('final_total') ? 'final_total' : 'total_price';
        $result = $this->conn->query("SELECT COALESCE(SUM($field), 0) AS revenue FROM orders WHERE status NOT IN ('cancelled')");
        $row = $result ? $result->fetch_assoc() : ['revenue' => 0];
        return (float)$row['revenue'];
    }

    public function getMonthlyRevenue($months = 6)
    {
        $field = $this->hasColumn('final_total') ? 'final_total' : 'total_price';
        $stmt = $this->conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COALESCE(SUM($field), 0) AS revenue FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month ASC");
        $stmt->bind_param("i", $months);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
