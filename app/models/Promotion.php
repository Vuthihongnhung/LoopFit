<?php
namespace App\Models;

use Core\Model;

class Promotion extends Model
{
    /* ===== Lấy danh sách tất cả các mã khuyến mãi ===== */
    public function getAll()
    {
        $sql = "SELECT * FROM promotions ORDER BY id DESC";
        $result = $this->conn->query($sql);
        if (!$result) return [];
        $promotions = [];
        while ($row = $result->fetch_assoc()) {
            $promotions[] = $row;
        }
        return $promotions;
    }

    /* ===== Kiểm tra xem mã code đã tồn tại trong hệ thống chưa (dùng khi thêm mới) ===== */
    public function checkExists($code)
    {
        $stmt = $this->conn->prepare("SELECT id FROM promotions WHERE code=?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /* ===== Tạo mới một mã khuyến mãi ===== */
    public function create($code, $discount_percent, $discount_amount, $min_order_value, $usage_limit, $start, $end)
    {
        $stmt = $this->conn->prepare("INSERT INTO promotions (code, discount_percent, discount_amount, min_order_value, usage_limit, used_count, start_date, end_date) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
        $stmt->bind_param("sddiiss", $code, $discount_percent, $discount_amount, $min_order_value, $usage_limit, $start, $end);
        return $stmt->execute();
    }

    /* ===== Lấy ID của bản ghi vừa được insert (gọi sau create) ===== */
    public function getLastId()
    {
        return $this->conn->insert_id;
    }

    /* ===== Xóa mã khuyến mãi theo ID ===== */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM promotions WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /* ===== Lấy danh sách các voucher đang hoạt động (hiện tại nằm trong khoảng start_date - end_date) ===== */
    public function getActiveVouchers()
    {
        $sql = "SELECT * FROM promotions 
                WHERE start_date <= NOW() AND end_date >= NOW()
                ORDER BY 
                    CASE 
                        WHEN discount_percent IS NOT NULL AND discount_percent > 0 THEN discount_percent 
                        ELSE 0 
                    END DESC,
                    discount_amount DESC";
        $result = $this->conn->query($sql);
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /* ===== Lấy thông tin voucher theo mã code (chỉ lấy nếu đang trong thời gian hiệu lực) ===== */
    public function getVoucherByCode($code)
    {
        $code = $this->conn->real_escape_string($code);
        $sql = "SELECT * FROM promotions 
                WHERE code = '$code' 
                  AND start_date <= NOW() 
                  AND end_date >= NOW()";
        $result = $this->conn->query($sql);
        if (!$result || $result->num_rows === 0) return null;
        return $result->fetch_assoc();
    }

    /* ===== Tăng số lượt đã sử dụng (used_count) của voucher lên 1 ===== */
    public function incrementUsedCount($code)
    {
        $stmt = $this->conn->prepare("UPDATE promotions SET used_count = used_count + 1 WHERE code = ?");
        $stmt->bind_param("s", $code);
        return $stmt->execute();
    }

    /* ===== Cập nhật thông tin một mã khuyến mãi ===== */
    public function update($id, $code, $discount_percent, $discount_amount, $min_order_value, $usage_limit, $start, $end)
    {
        $stmt = $this->conn->prepare("UPDATE promotions SET code=?, discount_percent=?, discount_amount=?, min_order_value=?, usage_limit=?, start_date=?, end_date=? WHERE id=?");
        $stmt->bind_param("sddiissi", $code, $discount_percent, $discount_amount, $min_order_value, $usage_limit, $start, $end, $id);
        return $stmt->execute();
    }

    /* ===== Lấy danh sách khuyến mãi kèm theo số lượt đã được sử dụng (thông qua bảng orders) ===== */
    public function getAllWithUsage()
    {
        $sql = "SELECT 
                    p.*,
                    COUNT(o.id) as used_count
                FROM promotions p
                LEFT JOIN orders o ON o.promotion_id = p.id
                GROUP BY p.id
                ORDER BY p.id DESC";
        $result = $this->conn->query($sql);
        if (!$result) return [];
        $promotions = [];
        while ($row = $result->fetch_assoc()) {
            $promotions[] = $row;
        }
        return $promotions;
    }
}
?>