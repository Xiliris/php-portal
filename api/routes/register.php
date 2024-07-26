<?php
require __DIR__ . '/../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $response['message'] = "Please fill in all fields.";
    } else {
        if ($password !== $confirm_password) {
            $response['message'] = "Passwords do not match.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $userByUsername = $stmt->fetch();

            if ($userByUsername) {
                $response['message'] = "Username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare("INSERT INTO users (username, role, password) VALUES (?, ?, ?)");
                $stmt->execute([strtolower($username), strtolower($role), $hashed_password]);

                if ($stmt->rowCount() === 1) {
                    $response['success'] = true;
                    $response['message'] = "Registration successful!";
                } else {
                    $response['message'] = "Failed to register. Please try again later.";
                }
            }
        }
    }
    echo json_encode($response);
}
?>
