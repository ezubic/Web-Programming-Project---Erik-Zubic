<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/BaseDAO.php';

final class OrderItemsDAO extends BaseDAO {
    public function __construct(){ parent::__construct(Database::conn()); }

    public function create(int $orderId, int $productId, int $qty, float $unitPrice): int {
        $stmt = $this->db->prepare(
            "INSERT INTO order_items (order_id, product_id, qty, unit_price) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$orderId, $productId, $qty, $unitPrice]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT id, order_id, product_id, qty, unit_price FROM order_items WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listByOrder(int $orderId): array {
        $stmt = $this->db->prepare(
            "SELECT id, order_id, product_id, qty, unit_price
             FROM order_items WHERE order_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function update(int $id, ?int $qty = null, ?float $unitPrice = null): bool {
        $sets = []; $vals = [];
        if ($qty !== null)       { $sets[] = "qty = ?";        $vals[] = $qty; }
        if ($unitPrice !== null) { $sets[] = "unit_price = ?"; $vals[] = $unitPrice; }
        if (!$sets) return false;
        $vals[] = $id;
        $sql = "UPDATE order_items SET " . implode(", ", $sets) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($vals);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM order_items WHERE id = ?")->execute([$id]);
    }
}
