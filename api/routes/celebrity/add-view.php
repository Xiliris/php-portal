<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $eventId = $_POST['id'];

    if (empty($eventId)) {
        $response['message'] = "Event ID is required.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE celebrity_event_data SET views = views + 1 WHERE id = ?");
            $stmt->execute([$eventId]);

            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = "Views incremented successfully.";
            } else {
                $pdo->rollBack();
                $response['message'] = "Event not found.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $response['message'] = "Database error: " . $e->getMessage();
        }
    }

    echo json_encode($response);
}
