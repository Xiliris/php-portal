<?php
require __DIR__ . '/../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($name) || empty($surname) || empty($email) || empty($password) || empty($confirm_password)) {
        $response['message'] = "Please fill in all fields.";
    } else {
        if ($password !== $confirm_password) {
            $response['message'] = "Passwords do not match.";
        } else {

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $userByEmail = $stmt->fetch();

            if ($userByEmail) {
                $response['message'] = "User with this email already exists.";
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $userByUsername = $stmt->fetch();

                if ($userByUsername) {
                    $response['message'] = "Username already exists.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    $stmt = $pdo->prepare("INSERT INTO users (username, name, surname, email, password) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $name, $surname, $email, $hashed_password]);

                    if ($stmt->rowCount() === 1) {
                        $response['success'] = true;
                        $response['message'] = "Registration successful!";
                    } else {
                        $response['message'] = "Failed to register. Please try again later.";
                    }
                }
            }
        }
    }
    echo json_encode($response);
}
?>
