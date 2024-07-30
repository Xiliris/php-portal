<?php 
    require __DIR__ . '/../../config.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $response['message'] = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_password, $user['password'])) {
            if ($new_password !== $confirm_password) {
                $response['message'] = "Passwords do not match.";
            } else if (password_verify($new_password, $user['password'])) {
                $response['message'] = "New password cannot be the same as the old password.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare("UPDATE users SET password = ?, changed = 1 WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]); 

                if ($stmt->rowCount() === 1) {
                    $response['success'] = true;
                    $response['message'] = "Password changed successfully!";
                } else {
                    $response['message'] = "Failed to change password. Please try again later.";
                }
            }
        } else {
            $response['message'] = "Invalid password.";
        }
    }
    echo json_encode($response);
}
?>
