<?php

namespace App\Models;

use Core\Model;

class FAQ extends Model
{
    protected $table = 'faq';
    
    /**
     * Tìm kiếm FAQ theo từ khóa hoặc lấy tất cả nếu không có từ khóa
     */
    public function searchByKeyword($keyword = '')
    {
        // Nếu không có từ khóa hoặc từ khóa quá ngắn, lấy tất cả FAQ đang hoạt động
        if (empty($keyword) || strlen($keyword) < 2) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_active = 1 
                    ORDER BY views DESC 
                    LIMIT 10";
            $result = $this->conn->query($sql);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
        
        // Có từ khóa, tìm kiếm theo câu hỏi hoặc từ khóa liên quan
        $searchTerm = '%' . $keyword . '%';
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND (question LIKE ? OR keywords LIKE ?)
                ORDER BY views DESC 
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Lấy tất cả FAQ đang hoạt động
     */
    public function getAllActive()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                ORDER BY views DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Tăng lượt xem cho FAQ
     */
    public function incrementView($id)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET views = views + 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}