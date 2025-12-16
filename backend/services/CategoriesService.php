<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/CategoriesDAO.php';

final class CategoriesService
{
    private CategoriesDAO $dao;

    public function __construct()
    {
        $this->dao = new CategoriesDAO();
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
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('name is required');
        }

        $parentId = null;
        if (array_key_exists('parent_id', $data) && $data['parent_id'] !== '' && $data['parent_id'] !== null) {
            $parentId = (int)$data['parent_id'];
        }

        return $this->dao->create($name, $parentId);
    }

    public function update(int $id, array $data): bool
    {
        $name = array_key_exists('name', $data)
            ? trim((string)$data['name'])
            : null;

        $parentId = null;
        if (array_key_exists('parent_id', $data)) {
            $parentId = ($data['parent_id'] === '' || $data['parent_id'] === null)
                ? null
                : (int)$data['parent_id'];
        }

        return $this->dao->update($id, $name, $parentId);
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }
}
