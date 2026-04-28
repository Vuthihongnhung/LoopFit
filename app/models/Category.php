<?php
namespace App\Models;

use Core\Model;

class Category extends Model
{
    public function getAll()
    {
        $result = $this->conn->query("SELECT * FROM categories ORDER BY id DESC");
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    }
    
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

     public function getBySlug($slug)
    {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getParents()
    {
        $sql = "SELECT * FROM categories WHERE parent_id = 0 ORDER BY display_order";
        $result = $this->conn->query($sql);
        $parents = [];
        while ($row = $result->fetch_assoc()) {
            $parents[] = $row;
        }
        return $parents;
    }
    
    public function getChildren($parentId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY display_order");
        $stmt->bind_param("i", $parentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $children = [];
        while ($row = $result->fetch_assoc()) {
            $children[] = $row;
        }
        return $children;
    }
    
   public function create($name, $slug, $parentId = 0, $icon = null, $displayOrder = 0)
    {
        $stmt = $this->conn->prepare("INSERT INTO categories (name, slug, parent_id, icon, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $name, $slug, $parentId, $icon, $displayOrder);
        return $stmt->execute();
    }
    
    public function update($id, $name, $slug, $parentId, $icon = null, $displayOrder = 0)
    {
        $stmt = $this->conn->prepare("UPDATE categories SET name=?, slug=?, parent_id=?, icon=?, display_order=? WHERE id=?");
        $stmt->bind_param("ssisii", $name, $slug, $parentId, $icon, $displayOrder, $id);
        return $stmt->execute();
    }
    
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function countProducts($categoryId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id=?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }

    public function getBreadcrumb($categoryId)
    {
        $breadcrumb = [];
        $currentId = $categoryId;
        while ($currentId) {
            $stmt = $this->conn->prepare("SELECT id, name, parent_id FROM categories WHERE id = ?");
            $stmt->bind_param("i", $currentId);
            $stmt->execute();
            $cat = $stmt->get_result()->fetch_assoc();
            if (!$cat) break;
            array_unshift($breadcrumb, ['id' => $cat['id'], 'name' => $cat['name']]);
            $currentId = $cat['parent_id'];
        }
        return $breadcrumb;
    }

    public function getAllDescendants($parentId) {
        $ids = [$parentId];
        $this->getChildrenRecursive($parentId, $ids);
        return $ids;
    }

    private function getChildrenRecursive($parentId, &$ids) {
        $stmt = $this->conn->prepare("SELECT id FROM categories WHERE parent_id = ?");
        $stmt->bind_param("i", $parentId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id'];
            $this->getChildrenRecursive($row['id'], $ids);
        }
    }
}