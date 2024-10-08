<?php
    require __DIR__ . '/../../config.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $response['message'] = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $response['success'] = true;
            $response['message'] = "Login successful! Welcome! " . $user['username'];
        } else {
            $response['message'] = "Invalid username or password.";
        }
    }
    echo json_encode($response);
}
?>
