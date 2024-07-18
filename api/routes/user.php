<?php
require __DIR__ . '/../config.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false, "message" => "", "user" => []];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            $response['success'] = true;
            $response['message'] = "User found.";
            $response['user'] = [
                "id" => $user['id'],
                "username" => $user['username'],
                "email" => $user['email'],
                "role" => $user['role']
            ];
        } else {
            $response['message'] = "User not found.";
        }
    } else {
        $response['message'] = "User not logged in.";
    }
    echo json_encode($response);
}
?>