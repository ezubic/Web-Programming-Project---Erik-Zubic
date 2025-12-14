<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/BaseDAO.php';

final class UsersDAO extends BaseDAO {
    public function __construct() { parent::__construct(Database::conn()); }

    public function create(string $email, string $passwordHash): int {
        $sql = "INSERT INTO users (email, password_hash) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $passwordHash]);
        return (int)$this->db->lastInsertId();
    }

        public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, email, password_hash FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function list(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("SELECT id, email FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, ?string $email = null, ?string $passwordHash = null): bool {
        $sets = []; $vals = [];
        if ($email !== null)        { $sets[] = "email = ?";          $vals[] = $email; }
        if ($passwordHash !== null) { $sets[] = "password_hash = ?";   $vals[] = $passwordHash; }
        if (!$sets) return false;
        $vals[] = $id;
        $sql = "UPDATE users SET " . implode(", ", $sets) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($vals);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
}
