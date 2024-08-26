<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (empty($id)) {
        $response["message"] = "ID is required";
        echo json_encode($response);
        exit;
    }

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Update the celebrity_event_data table
        $stmt = $pdo->prepare("UPDATE celebrity_event_data SET preview = 0 WHERE celebrity_profile_id = ?");
        $stmt->execute([$id]);

        // Update the celebrity_profile table
        $stmt = $pdo->prepare("UPDATE celebrity_profile SET preview = 0 WHERE id = ?");
        $stmt->execute([$id]);

        // Commit the transaction
        $pdo->commit();

        $response["success"] = true;
        $response["message"] = "Preview fields updated successfully";
    } catch (PDOException $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        $response["message"] = "Error updating preview fields: " . $e->getMessage();
    }
}

echo json_encode($response);
exit;
