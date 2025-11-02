<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/BaseDAO.php';

final class CategoriesDAO extends BaseDAO {
    public function __construct(){ parent::__construct(Database::conn()); }

    public function create(string $name, ?int $parentId): int {
        $stmt = $this->db->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parentId]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, parent_id FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function list(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT id, name, parent_id FROM categories ORDER BY id DESC LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $limit,  PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, ?string $name = null, ?int $parentId = null): bool {
        $sets = []; $vals = [];
        if ($name !== null)     { $sets[] = "name = ?";      $vals[] = $name; }
        if ($parentId !== null) { $sets[] = "parent_id = ?"; $vals[] = $parentId; }
        if (!$sets) return false;
        $vals[] = $id;
        $sql = "UPDATE categories SET " . implode(", ", $sets) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($vals);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    }
}
