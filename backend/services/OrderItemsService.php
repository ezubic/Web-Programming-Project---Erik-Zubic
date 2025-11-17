<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/OrderItemsDAO.php';

final class OrderItemsService
{
    private OrderItemsDAO $dao;

    public function __construct()
    {
        $this->dao = new OrderItemsDAO();
    }

    public function listByOrder(int $orderId): array
    {
        return $this->dao->listByOrder($orderId);
    }

    public function get(int $id): ?array
    {
        return $this->dao->findById($id);
    }

    public function create(array $data): int
    {
        $orderId   = (int)($data['order_id'] ?? 0);
        $productId = (int)($data['product_id'] ?? 0);
        $qty       = (int)($data['qty'] ?? 1);
        $unitPrice = isset($data['unit_price']) ? (float)$data['unit_price'] : 0.0;

        if ($orderId <= 0 || $productId <= 0) {
            throw new InvalidArgumentException('order_id and product_id are required');
        }
        if ($qty <= 0) {
            throw new InvalidArgumentException('qty must be > 0');
        }
        if ($unitPrice < 0) {
            throw new InvalidArgumentException('unit_price must be >= 0');
        }

        return $this->dao->create($orderId, $productId, $qty, $unitPrice);
    }

    public function update(int $id, array $data): bool
    {
        $qty = array_key_exists('qty', $data)
            ? (int)$data['qty']
            : null;

        $unitPrice = array_key_exists('unit_price', $data)
            ? (float)$data['unit_price']
            : null;

        return $this->dao->update($id, $qty, $unitPrice);
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }
}
