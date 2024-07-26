<?php
require __DIR__ . '/../config.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false, "message" => "", "users" => []];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();

        if ($users) {
            $response['success'] = true;
            $response['message'] = "Users found.";
            foreach ($users as $user) {
                $response['users'][] = [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "role" => $user['role'],
                    "changed" => $user['changed']
                ];
            }
        } else {
            $response['message'] = "Users not found.";
        }
    } else {
        $response['message'] = "User not logged in.";
    }
    echo json_encode($response);
}

?>