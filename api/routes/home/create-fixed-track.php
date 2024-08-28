<?php
require __DIR__ . '/../../config.php'; 

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = isset($_POST['content']) ? trim($_POST['content']) : ''; 
    $time = isset($_POST['time']) ? intval($_POST['time']) : null;
    $moving = isset($_POST['moving']) ? filter_var($_POST['moving'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
    $enabled = isset($_POST['enabled']) ? filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : true; 

    if (empty($content)) { 
        $response['message'] = 'Content is required.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO fixed_track (id, content, time, moving, enabled) 
                VALUES (1, :content, :time, :moving, :enabled) 
                ON DUPLICATE KEY UPDATE 
                    content = VALUES(content), 
                    time = VALUES(time), 
                    moving = VALUES(moving), 
                    enabled = VALUES(enabled)");

            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':time', $time, PDO::PARAM_INT);
            $stmt->bindParam(':moving', $moving, PDO::PARAM_BOOL);
            $stmt->bindParam(':enabled', $enabled, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Record inserted or updated successfully.';
            } else {
                $pdo->rollBack();
                $response['message'] = 'Failed to insert or update record.';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

echo json_encode($response);
