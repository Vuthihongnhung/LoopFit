<?php
namespace App\Models;

use Core\Model;

class Promotion extends Model
{
    private ?array $columns = null;

    private function columns(): array
    {
        if ($this->columns !== null) return $this->columns;
        $this->columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM promotions");
        if ($result) {
            while ($row = $result->fetch_assoc()) $this->columns[] = $row['Field'];
        }
        return $this->columns;
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    public function getAll()
    {
        $result = $this->conn->query("SELECT * FROM promotions ORDER BY id DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function checkExists($code)
    {
        $stmt = $this->conn->prepare("SELECT id FROM promotions WHERE code=?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function create($code, $discount, $start, $end)
    {
        $stmt = $this->conn->prepare("INSERT INTO promotions (code, discount_percent, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $code, $discount, $start, $end);
        return $stmt->execute();
    }

    public function getLastId() { return $this->conn->insert_id; }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM promotions WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getActiveVouchers()
    {
        $orderParts = ["CASE WHEN discount_percent IS NOT NULL AND discount_percent > 0 THEN discount_percent ELSE 0 END DESC"];
        if ($this->hasColumn('discount_amount')) $orderParts[] = 'discount_amount DESC';

        $sql = "SELECT * FROM promotions WHERE start_date <= NOW() AND end_date >= NOW() ORDER BY " . implode(', ', $orderParts);
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getVoucherByCode($code)
    {
        $stmt = $this->conn->prepare("SELECT * FROM promotions WHERE code = ? AND start_date <= NOW() AND end_date >= NOW() LIMIT 1");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }
}
