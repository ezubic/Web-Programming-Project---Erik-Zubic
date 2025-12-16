<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/BaseDAO.php';

final class ProductsDAO extends BaseDAO {
    public function __construct(){ parent::__construct(Database::conn()); }

    public function create(?int $categoryId, string $name, float $price): int {
        $stmt = $this->db->prepare(
            "INSERT INTO products (category_id, name, price) VALUES (?, ?, ?)"
        );
        $stmt->execute([$categoryId, $name, $price]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, category_id, name, price FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function list(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT id, category_id, name, price FROM products ORDER BY id DESC LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $limit,  PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, ?int $categoryId = null, ?string $name = null, ?float $price = null): bool {
        $sets = []; $vals = [];
        if ($categoryId !== null) { $sets[] = "category_id = ?"; $vals[] = $categoryId; }
        if ($name !== null)       { $sets[] = "name = ?";        $vals[] = $name; }
        if ($price !== null)      { $sets[] = "price = ?";       $vals[] = $price; }
        if (!$sets) return false;
        $vals[] = $id;
        $sql = "UPDATE products SET " . implode(", ", $sets) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($vals);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    }
}
