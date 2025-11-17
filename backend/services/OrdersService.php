<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/OrdersDAO.php';

final class OrdersService
{
    private OrdersDAO $dao;

    public function __construct()
    {
        $this->dao = new OrdersDAO();
    }

    public function list(int $limit = 50, int $offset = 0): array
    {
        return $this->dao->list($limit, $offset);
    }

    public function get(int $id): ?array
    {
        return $this->dao->findById($id);
    }

    public function create(array $data): int
    {
        $userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $total  = isset($data['total']) ? (float)$data['total'] : 0.0;
        $status = trim((string)($data['status'] ?? 'pending'));
        if ($status === '') {
            $status = 'pending';
        }

        return $this->dao->create($userId, $total, $status);
    }

    public function update(int $id, array $data): bool
    {
        $userId = array_key_exists('user_id', $data)
            ? ($data['user_id'] === '' || $data['user_id'] === null ? null : (int)$data['user_id'])
            : null;

        $total = array_key_exists('total', $data)
            ? (float)$data['total']
            : null;

        $status = array_key_exists('status', $data)
            ? trim((string)$data['status'])
            : null;

        return $this->dao->update($id, $userId, $total, $status);
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }
}
