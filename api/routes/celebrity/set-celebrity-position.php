<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === 'POST') {

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $position = isset($_POST['position']) ? (int)$_POST['position'] : 0;

    if (empty($id)) {
        $response['message'] = "Id is required.";
    } else if (empty($position)) {
        $response['message'] = "Position is required.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM celebrity_home_position WHERE position = ?");
            $stmt->execute([$position]);

            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE celebrity_home_position SET celebrity_profile_id = ? WHERE position = ?");
                $stmt->execute([$id, $position]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO celebrity_home_position (celebrity_profile_id, position) VALUES (?, ?)");
                $stmt->execute([$id, $position]);
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = "Position set successfully.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $response['message'] = "Database error: " . $e->getMessage();
        }
    }

    echo json_encode($response);
}
?>
