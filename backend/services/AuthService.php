<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/UsersDAO.php';

final class AuthService
{
    private UsersDAO $usersDao;
    private array $config;

    public function __construct(array $config)
    {
        $this->usersDao = new UsersDAO();
        $this->config = $config;
    }

    public function register(array $data): array
    {
        $email = isset($data['email']) ? strtolower(trim((string)$data['email'])) : '';
        $password = isset($data['password']) ? (string)$data['password'] : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Valid email is required.');
        }
        if (strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }

        $existing = $this->usersDao->findByEmail($email);
        if ($existing) {
            throw new InvalidArgumentException('Email is already registered.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        if ($hash === false) {
            throw new RuntimeException('Could not hash password.');
        }

        $id = $this->usersDao->create($email, $hash);

        $role = $this->resolveRoleByEmail($email);

        $token = Middleware::createToken([
            'id' => $id,
            'email' => $email,
            'role' => $role
        ]);

        return [
            'user' => ['id' => $id, 'email' => $email, 'role' => $role],
            'token' => $token
        ];
    }

    public function login(array $data): array
    {
        $email = isset($data['email']) ? strtolower(trim((string)$data['email'])) : '';
        $password = isset($data['password']) ? (string)$data['password'] : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Valid email is required.');
        }
        if ($password === '') {
            throw new InvalidArgumentException('Password is required.');
        }

        $user = $this->usersDao->findByEmail($email);
        if (!$user || !isset($user['password_hash'])) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        if (!password_verify($password, (string)$user['password_hash'])) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        $role = $this->resolveRoleByEmail($email);

        $token = Middleware::createToken([
            'id' => (int)$user['id'],
            'email' => $email,
            'role' => $role
        ]);

        return [
            'user' => ['id' => (int)$user['id'], 'email' => $email, 'role' => $role],
            'token' => $token
        ];
    }

    private function resolveRoleByEmail(string $email): string
    {
        $admins = $this->config['ADMIN_EMAILS'] ?? [];
        if (is_array($admins) && in_array($email, $admins, true)) {
            return 'admin';
        }
        return 'user';
    }
}
