<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/ProductsDAO.php';

final class ProductsService
{
    private ProductsDAO $dao;

    public function __construct()
    {
        $this->dao = new ProductsDAO();
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
        $name  = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('name is required');
        }

        $categoryId = null;
        if (array_key_exists('category_id', $data) && $data['category_id'] !== '' && $data['category_id'] !== null) {
            $categoryId = (int)$data['category_id'];
        }

        $price = isset($data['price']) ? (float)$data['price'] : 0.0;
        if ($price < 0) {
            throw new InvalidArgumentException('price must be non-negative');
        }

        return $this->dao->create($categoryId, $name, $price);
    }

    public function update(int $id, array $data): bool
    {
        $categoryId = null;
        if (array_key_exists('category_id', $data)) {
            $categoryId = ($data['category_id'] === '' || $data['category_id'] === null)
                ? null
                : (int)$data['category_id'];
        }

        $name = array_key_exists('name', $data)
            ? trim((string)$data['name'])
            : null;

        $price = array_key_exists('price', $data)
            ? (float)$data['price']
            : null;

        return $this->dao->update($id, $categoryId, $name, $price);
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }
}
