<?php
require __DIR__ . '/../config.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    if (empty($username)) {
        $response['message'] = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Prepare the statement to delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->rowCount() === 1) {
                $response['success'] = true;
                $response['message'] = "User removed successfully!";
            } else {
                $response['message'] = "Failed to remove user. Please try again later.";
            }
        } else {
            $response['message'] = "User not found.";
        }
    }
    echo json_encode($response);
}
