<?php
declare(strict_types=1);

ini_set('display_errors','1'); error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/dao/UsersDAO.php';
require_once __DIR__ . '/dao/CategoriesDAO.php';
require_once __DIR__ . '/dao/ProductsDAO.php';
require_once __DIR__ . '/dao/OrdersDAO.php';
require_once __DIR__ . '/dao/OrderItemsDAO.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

function jsonBody(): array { return json_decode(file_get_contents('php://input'), true) ?? []; }

Flight::route('GET /health', function() { echo json_encode(['ok' => true]); });

/** USERS */
Flight::route('GET /users', function () {
    $dao = new UsersDAO();
    if (isset($_GET['id'])) { echo json_encode($dao->findById((int)$_GET['id'])); return; }
    echo json_encode($dao->list());
});
Flight::route('POST /users', function () {
    $dao = new UsersDAO(); $b = jsonBody();
    $id = $dao->create($b['email'], password_hash($b['password'], PASSWORD_BCRYPT));
    echo json_encode(['id' => $id]);
});
Flight::route('PUT|PATCH /users', function () {
    $dao = new UsersDAO(); $b = jsonBody();
    $ok = $dao->update((int)$b['id'], $b['email'] ?? null, isset($b['password']) ? password_hash($b['password'], PASSWORD_BCRYPT) : null);
    echo json_encode(['success' => $ok]);
});
Flight::route('DELETE /users', function () {
    $dao = new UsersDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->delete((int)$b['id'])]);
});

/** CATEGORIES */
Flight::route('GET /categories', function () {
    $dao = new CategoriesDAO();
    if (isset($_GET['id'])) { echo json_encode($dao->findById((int)$_GET['id'])); return; }
    echo json_encode($dao->list());
});
Flight::route('POST /categories', function () {
    $dao = new CategoriesDAO(); $b = jsonBody();
    echo json_encode(['id' => $dao->create($b['name'], $b['parent_id'] ?? null)]);
});
Flight::route('PUT|PATCH /categories', function () {
    $dao = new CategoriesDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->update((int)$b['id'], $b['name'] ?? null, $b['parent_id'] ?? null)]);
});
Flight::route('DELETE /categories', function () {
    $dao = new CategoriesDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->delete((int)$b['id'])]);
});

/** PRODUCTS */
Flight::route('GET /products', function () {
    $dao = new ProductsDAO();
    if (isset($_GET['id'])) { echo json_encode($dao->findById((int)$_GET['id'])); return; }
    echo json_encode($dao->list());
});
Flight::route('POST /products', function () {
    $dao = new ProductsDAO(); $b = jsonBody();
    echo json_encode(['id' => $dao->create($b['category_id'] ?? null, $b['name'], (float)$b['price'])]);
});
Flight::route('PUT|PATCH /products', function () {
    $dao = new ProductsDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->update((int)$b['id'], $b['category_id'] ?? null, $b['name'] ?? null, isset($b['price']) ? (float)$b['price'] : null)]);
});
Flight::route('DELETE /products', function () {
    $dao = new ProductsDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->delete((int)$b['id'])]);
});

/** ORDERS */
Flight::route('GET /orders', function () {
    $dao = new OrdersDAO();
    if (isset($_GET['id']))     { echo json_encode($dao->findById((int)$_GET['id'])); return; }
    if (isset($_GET['user_id'])){ echo json_encode($dao->listByUser((int)$_GET['user_id'])); return; }
    echo json_encode(['error' => 'Provide id or user_id']);
});
Flight::route('POST /orders', function () {
    $dao = new OrdersDAO(); $b = jsonBody();
    if (isset($b['items'])) {
        $id = $dao->createWithItems((int)$b['user_id'], $b['items']); // [[product_id, qty, price],...]
        echo json_encode(['id' => $id]); return;
    }
    echo json_encode(['id' => $dao->create((int)$b['user_id'], (float)($b['total'] ?? 0), $b['status'] ?? 'pending')]);
});
Flight::route('PUT|PATCH /orders', function () {
    $dao = new OrdersDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->update((int)$b['id'], isset($b['total']) ? (float)$b['total'] : null, $b['status'] ?? null)]);
});
Flight::route('DELETE /orders', function () {
    $dao = new OrdersDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->delete((int)$b['id'])]);
});

/** ORDER ITEMS */
Flight::route('GET /order_items', function () {
    $dao = new OrderItemsDAO();
    if (isset($_GET['order_id'])) { echo json_encode($dao->listByOrder((int)$_GET['order_id'])); return; }
    echo json_encode(['error' => 'Provide order_id']);
});
Flight::route('POST /order_items', function () {
    $dao = new OrderItemsDAO(); $b = jsonBody();
    echo json_encode(['id' => $dao->create((int)$b['order_id'], (int)$b['product_id'], (int)$b['qty'], (float)$b['unit_price'])]);
});
Flight::route('PUT|PATCH /order_items', function () {
    $dao = new OrderItemsDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->update((int)$b['id'], $b['qty'] ?? null, isset($b['unit_price']) ? (float)$b['unit_price'] : null)]);
});
Flight::route('DELETE /order_items', function () {
    $dao = new OrderItemsDAO(); $b = jsonBody();
    echo json_encode(['success' => $dao->delete((int)$b['id'])]);
});

Flight::start();
