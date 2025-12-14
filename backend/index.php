<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/middleware/Middleware.php';
require_once __DIR__ . '/middleware/Authorization.php';
require_once __DIR__ . '/services/AuthService.php';

Middleware::init($config);

require_once __DIR__ . '/services/UsersService.php';
require_once __DIR__ . '/services/CategoriesService.php';
require_once __DIR__ . '/services/ProductsService.php';
require_once __DIR__ . '/services/OrdersService.php';
require_once __DIR__ . '/services/OrderItemsService.php';

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    
// Middleware bootstrap (logging, JSON parsing, token decoding)
Flight::before('start', function () {
    Middleware::bootstrap();
});
exit;
}

// Helper: decode JSON body
function jsonBody(): array
{
    return Middleware::json();
}

// Global error handler
Flight::map('error', function (Throwable $e) {
    $code = $e instanceof InvalidArgumentException ? 400 : 500;
    Flight::json([
        'error'   => $e->getMessage(),
        'type'    => (new ReflectionClass($e))->getShortName(),
    ], $code);
});

// 404
Flight::map('notFound', function () {
    Flight::json(['error' => 'Not found'], 404);
});

// Health check
Flight::route('GET /health', function () {
    Flight::json(['ok' => true]);
});

/**
 * AUTH
 */
Flight::route('POST /auth/register', function () use ($config) {
    $service = new AuthService($config);
    $result = $service->register(jsonBody());
    Flight::json($result);
});

Flight::route('POST /auth/login', function () use ($config) {
    $service = new AuthService($config);
    $result = $service->login(jsonBody());
    Flight::json($result);
});


/**
 * USERS CRUD
 */
Flight::route('GET /users', function () {
    Authorization::requireAdmin();

    $service = new UsersService();
    $limit   = (int)($_GET['limit'] ?? 50);
    $offset  = (int)($_GET['offset'] ?? 0);
    Flight::json($service->list($limit, $offset));
});

Flight::route('GET /users/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new UsersService();
    $user = $service->get($id);
    if (!$user) {
        Flight::json(['error' => 'User not found'], 404);
        return;
    }
    Flight::json($user);
});

Flight::route('POST /users', function () {
    Authorization::requireAdmin();

    $service = new UsersService();
    $id = $service->create(jsonBody());
    Flight::json(['id' => $id], 201);
});

Flight::route('PUT /users/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new UsersService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('PATCH /users/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new UsersService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('DELETE /users/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new UsersService();
    $ok = $service->delete($id);
    Flight::json(['success' => $ok]);
});

/**
 * CATEGORIES CRUD
 */
Flight::route('GET /categories', function () {
    Authorization::requireAuth();

    $service = new CategoriesService();
    $limit   = (int)($_GET['limit'] ?? 50);
    $offset  = (int)($_GET['offset'] ?? 0);
    Flight::json($service->list($limit, $offset));
});

Flight::route('GET /categories/@id', function (int $id) {
    Authorization::requireAuth();

    $service = new CategoriesService();
    $cat = $service->get($id);
    if (!$cat) {
        Flight::json(['error' => 'Category not found'], 404);
        return;
    }
    Flight::json($cat);
});

Flight::route('POST /categories', function () {
    Authorization::requireAdmin();

    $service = new CategoriesService();
    $id = $service->create(jsonBody());
    Flight::json(['id' => $id], 201);
});

Flight::route('PUT /categories/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new CategoriesService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('PATCH /categories/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new CategoriesService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('DELETE /categories/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new CategoriesService();
    $ok = $service->delete($id);
    Flight::json(['success' => $ok]);
});

/**
 * PRODUCTS CRUD
 */
Flight::route('GET /products', function () {
    Authorization::requireAuth();

    $service = new ProductsService();
    $limit   = (int)($_GET['limit'] ?? 50);
    $offset  = (int)($_GET['offset'] ?? 0);
    Flight::json($service->list($limit, $offset));
});

Flight::route('GET /products/@id', function (int $id) {
    Authorization::requireAuth();

    $service = new ProductsService();
    $product = $service->get($id);
    if (!$product) {
        Flight::json(['error' => 'Product not found'], 404);
        return;
    }
    Flight::json($product);
});

Flight::route('POST /products', function () {
    Authorization::requireAdmin();

    $service = new ProductsService();
    $id = $service->create(jsonBody());
    Flight::json(['id' => $id], 201);
});

Flight::route('PUT /products/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new ProductsService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('PATCH /products/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new ProductsService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('DELETE /products/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new ProductsService();
    $ok = $service->delete($id);
    Flight::json(['success' => $ok]);
});

/**
 * ORDERS CRUD
 */
Flight::route('GET /orders', function () {
    Authorization::requireAdmin();

    $service = new OrdersService();
    $limit   = (int)($_GET['limit'] ?? 50);
    $offset  = (int)($_GET['offset'] ?? 0);
    Flight::json($service->list($limit, $offset));
});

Flight::route('GET /orders/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrdersService();
    $order = $service->get($id);
    if (!$order) {
        Flight::json(['error' => 'Order not found'], 404);
        return;
    }
    Flight::json($order);
});

Flight::route('POST /orders', function () {
    Authorization::requireAuth();

    $service = new OrdersService();
    $id = $service->create(jsonBody());
    Flight::json(['id' => $id], 201);
});

Flight::route('PUT /orders/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrdersService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('PATCH /orders/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrdersService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('DELETE /orders/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrdersService();
    $ok = $service->delete($id);
    Flight::json(['success' => $ok]);
});

/**
 * ORDER ITEMS CRUD
 */
Flight::route('GET /order_items', function () {
    Authorization::requireAdmin();

    $service = new OrderItemsService();
    if (isset($_GET['order_id'])) {
        $orderId = (int)$_GET['order_id'];
        Flight::json($service->listByOrder($orderId));
        return;
    }
    Flight::json(['error' => 'Provide order_id query parameter'], 400);
});

Flight::route('GET /order_items/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrderItemsService();
    $item = $service->get($id);
    if (!$item) {
        Flight::json(['error' => 'Order item not found'], 404);
        return;
    }
    Flight::json($item);
});

Flight::route('POST /order_items', function () {
    Authorization::requireAuth();

    $service = new OrderItemsService();
    $id = $service->create(jsonBody());
    Flight::json(['id' => $id], 201);
});

Flight::route('PUT /order_items/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrderItemsService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('PATCH /order_items/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrderItemsService();
    $ok = $service->update($id, jsonBody());
    Flight::json(['success' => $ok]);
});

Flight::route('DELETE /order_items/@id', function (int $id) {
    Authorization::requireAdmin();

    $service = new OrderItemsService();
    $ok = $service->delete($id);
    Flight::json(['success' => $ok]);
});

/**
 * Presentation layer: simple products page rendered with Flight
 */
Flight::route('GET /products-page', function () {
    $service = new ProductsService();
    $products = $service->list(100, 0);

    header('Content-Type: text/html; charset=utf-8');

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Products</title>';
    echo '<style>body{font-family:Arial, sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;}';
    echo 'th,td{border:1px solid #ccc;padding:8px;text-align:left;}th{background:#f5f5f5;}</style>';
    echo '</head><body>';
    echo '<h1>Products</h1>';
    echo '<table><tr><th>ID</th><th>Name</th><th>Price</th><th>Category ID</th></tr>';

    foreach ($products as $p) {
        $id   = htmlspecialchars((string)$p['id'], ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8');
        $price = htmlspecialchars((string)$p['price'], ENT_QUOTES, 'UTF-8');
        $catId = htmlspecialchars((string)($p['category_id'] ?? ''), ENT_QUOTES, 'UTF-8');

        echo "<tr><td>{$id}</td><td>{$name}</td><td>{$price}</td><td>{$catId}</td></tr>";
    }

    echo '</table></body></html>';
});

/**
 * OpenAPI JSON and Swagger UI
 */
Flight::route('GET /openapi.json', function () {
    header('Content-Type: application/json; charset=utf-8');
    readfile(__DIR__ . '/openapi.json');
});

Flight::route('GET /docs', function () {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/swagger.html');
});

Flight::start();
