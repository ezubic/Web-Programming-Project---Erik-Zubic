<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/BaseDAO.php';

final class OrdersDAO extends BaseDAO {
    public function __construct(){ parent::__construct(Database::conn()); }

    public function create(int $userId, float $total = 0.0, string $status = 'pending'): int {
        $stmt = $this->db->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $total, $status]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, user_id, total, status FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listByUser(int $userId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT id, user_id, total, status
             FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, ?float $total = null, ?string $status = null): bool {
        $sets = []; $vals = [];
        if ($total !== null)  { $sets[] = "total = ?";  $vals[] = $total; }
        if ($status !== null) { $sets[] = "status = ?"; $vals[] = $status; }
        if (!$sets) return false;
        $vals[] = $id;
        $sql = "UPDATE orders SET " . implode(", ", $sets) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($vals);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);
    }

    /** Example: create order with items atomically */
    public function createWithItems(int $userId, array $items): int {
        // $items = [ [product_id, qty, unit_price], ... ]
        $this->db->beginTransaction();
        try {
            $orderId = $this->create($userId, 0.0, 'pending');
            $sum = 0.0;
            $stmt = $this->db->prepare(
                "INSERT INTO order_items (order_id, product_id, qty, unit_price) VALUES (?, ?, ?, ?)"
            );
            foreach ($items as $it) {
                [$pid, $qty, $price] = $it;
                $stmt->execute([$orderId, (int)$pid, (int)$qty, (float)$price]);
                $sum += $qty * $price;
            }
            $this->update($orderId, $sum, 'confirmed');
            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
