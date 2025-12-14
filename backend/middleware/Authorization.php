<?php
declare(strict_types=1);

final class Authorization
{
    public static function requireAuth(): array
    {
        return Middleware::requireAuth();
    }

    public static function requireAnyRole(array $roles): array
    {
        $user = self::requireAuth();
        $role = $user['role'] ?? null;

        if (!$role || !in_array($role, $roles, true)) {
            Flight::halt(403, json_encode([
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.'
            ]));
        }

        return $user;
    }

    public static function requireAdmin(): array
    {
        return self::requireAnyRole(['admin']);
    }
}
