<?php
declare(strict_types=1);

final class Middleware
{
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function bootstrap(): void
    {
        self::logRequest();
        self::parseJsonBody();
        self::authenticateIfPresent();
    }

    public static function json(): array
    {
        $json = Flight::get('json');
        return is_array($json) ? $json : [];
    }

    private static function logRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $uri    = $_SERVER['REQUEST_URI'] ?? '';
        error_log(sprintf('[API] %s %s', $method, $uri));
    }

    private static function parseJsonBody(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        // Only parse JSON for write methods or when JSON content-type is present
        $shouldParse = in_array($method, ['POST','PUT','PATCH','DELETE'], true)
            || stripos($contentType, 'application/json') !== false;

        if (!$shouldParse) {
            Flight::set('json', []);
            return;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            Flight::set('json', []);
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            Flight::halt(400, json_encode([
                'error' => 'Bad Request',
                'message' => 'Invalid JSON body.'
            ]));
        }

        Flight::set('json', $data);
    }

    private static function authenticateIfPresent(): void
    {
        $token = self::getBearerToken();
        if (!$token) {
            Flight::set('user', null);
            return;
        }

        $payload = self::verifyToken($token);
        if (!$payload) {
            Flight::set('user', null);
            return;
        }

        Flight::set('user', $payload);
    }

    public static function requireAuth(): array
    {
        $user = Flight::get('user');
        if (!$user) {
            Flight::halt(401, json_encode([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid token.'
            ]));
        }
        return $user;
    }

    public static function createToken(array $payload): string
    {
        $ttl = (int)(self::$config['TOKEN_TTL'] ?? 3600);
        $now = time();

        $payload['iat'] = $now;
        $payload['exp'] = $now + $ttl;

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $segments = [];
        $segments[] = self::base64UrlEncode(json_encode($header));
        $segments[] = self::base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, (string)(self::$config['APP_SECRET'] ?? ''), true);

        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$h64, $p64, $s64] = $parts;

        $signingInput = $h64 . '.' . $p64;
        $expectedSig = hash_hmac('sha256', $signingInput, (string)(self::$config['APP_SECRET'] ?? ''), true);
        $sig = self::base64UrlDecode($s64);

        if (!is_string($sig) || !hash_equals($expectedSig, $sig)) {
            return null;
        }

        $payloadJson = self::base64UrlDecode($p64);
        if (!is_string($payloadJson)) return null;

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) return null;

        $exp = $payload['exp'] ?? null;
        if (!is_int($exp) && !ctype_digit((string)$exp)) return null;
        if ((int)$exp < time()) return null;

        return $payload;
    }

    private static function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
        if (!$header && function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) $header = $headers['Authorization'];
        }

        if (!is_string($header) || $header === '') return null;

        if (preg_match('/^Bearer\s+(.*)$/i', trim($header), $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): ?string
    {
        $b64 = strtr($data, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) $b64 .= str_repeat('=', 4 - $pad);
        $decoded = base64_decode($b64, true);
        return $decoded === false ? null : $decoded;
    }
}
