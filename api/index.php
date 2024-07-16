<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'routes.php';
require_once 'login.php';
require_once 'register.php';

function router($method, $route, $callback) {
    $request_uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $request_method = $_SERVER['REQUEST_METHOD'];

    if ($request_uri === $route && strtolower($request_method) === strtolower($method)) {
        call_user_func($callback);
        exit();
    }
}

router('GET', '/php-portal/api/routes', 'routes');
router('GET', '/php-portal/api/login', 'login');
router('POST', '/php-portal/api/register', 'register');

http_response_code(404);
echo json_encode(array('error' => '404 Not Found'));
?>
