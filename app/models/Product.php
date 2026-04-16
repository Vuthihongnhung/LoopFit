<?php

namespace App\Models;

use Core\Model;

class Product extends Model
{
    public function getAll()
    {
        $sql = "SELECT * FROM products ORDER BY id DESC";
        $result = $this->conn->query($sql);

        if (!$result) {
            return [];
        }

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }

    public function getAllWithCategory()
    {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";

        $result = $this->conn->query($sql);

        if (!$result) {
            return [];
        }

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }

    public function getById($id)
    {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ? 
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare getById lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getBySlug($slug)
    {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare getBySlug lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("s", $slug);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($data)
    {
        $name = trim($data['name'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $price = (float)($data['price'] ?? 0);
        $depositPrice = (float)($data['deposit_price'] ?? 0);
        $description = trim($data['description'] ?? '');
        $size = trim($data['size'] ?? '');
        $color = trim($data['color'] ?? '');
        $image = trim($data['image'] ?? '');
        $stock = (int)($data['stock'] ?? 0);
        $categoryId = !empty($data['category_id']) ? (int)$data['category_id'] : null;

        $sql = "INSERT INTO products
                    (name, slug, price, deposit_price, description, size, color, image, stock, category_id)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare create product lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param(
            "ssddssssii",
            $name,
            $slug,
            $price,
            $depositPrice,
            $description,
            $size,
            $color,
            $image,
            $stock,
            $categoryId
        );

        if (!$stmt->execute()) {
            die('Execute create product lỗi: ' . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    public function update($id, $data)
    {
        $name = trim($data['name'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $price = (float)($data['price'] ?? 0);
        $depositPrice = (float)($data['deposit_price'] ?? 0);
        $description = trim($data['description'] ?? '');
        $size = trim($data['size'] ?? '');
        $color = trim($data['color'] ?? '');
        $image = trim($data['image'] ?? '');
        $stock = (int)($data['stock'] ?? 0);
        $categoryId = !empty($data['category_id']) ? (int)$data['category_id'] : null;

        $sql = "UPDATE products
                SET name = ?,
                    slug = ?,
                    price = ?,
                    deposit_price = ?,
                    description = ?,
                    size = ?,
                    color = ?,
                    image = ?,
                    stock = ?,
                    category_id = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare update product lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param(
            "ssddssssiii",
            $name,
            $slug,
            $price,
            $depositPrice,
            $description,
            $size,
            $color,
            $image,
            $stock,
            $categoryId,
            $id
        );

        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare delete product lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function getRelatedProducts($categoryId, $excludeId = 0, $limit = 4)
    {
        $sql = "SELECT * 
                FROM products
                WHERE category_id = ? AND id != ?
                ORDER BY id DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("iii", $categoryId, $excludeId, $limit);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function filter($filters = [])
    {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['keyword'])) {
            $sql .= " AND p.name LIKE ?";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $types .= "s";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$filters['category_id'];
            $types .= "i";
        }

        if ($filters['min_price'] !== '' && $filters['min_price'] !== null) {
            $sql .= " AND p.price >= ?";
            $params[] = (float)$filters['min_price'];
            $types .= "d";
        }

        if ($filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $sql .= " AND p.price <= ?";
            $params[] = (float)$filters['max_price'];
            $types .= "d";
        }

        if (!empty($filters['size'])) {
            $sql .= " AND p.size = ?";
            $params[] = $filters['size'];
            $types .= "s";
        }

        if (!empty($filters['color'])) {
            $sql .= " AND p.color = ?";
            $params[] = $filters['color'];
            $types .= "s";
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}