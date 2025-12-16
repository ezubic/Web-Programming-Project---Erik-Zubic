<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/UsersDAO.php';

final class UsersService
{
    private UsersDAO $dao;

    public function __construct()
    {
        $this->dao = new UsersDAO();
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
        $email = trim((string)($data['email'] ?? ''));
        $passwordHash = trim((string)($data['password_hash'] ?? ''));

        if ($email === '' || $passwordHash === '') {
            throw new InvalidArgumentException('email and password_hash are required');
        }

        return $this->dao->create($email, $passwordHash);
    }

    public function update(int $id, array $data): bool
    {
        $email = array_key_exists('email', $data)
            ? trim((string)$data['email'])
            : null;

        $passwordHash = array_key_exists('password_hash', $data)
            ? trim((string)$data['password_hash'])
            : null;

        return $this->dao->update($id, $email, $passwordHash);
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }
}
