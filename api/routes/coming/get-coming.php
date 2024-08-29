<?php
require __DIR__ . '/../../config.php';

$response = ["data" => [], "message" => ""];

try {
    $stmt = $pdo->query("SELECT id, title, text, image_path, video_path, publish_date FROM coming ORDER BY publish_date DESC");
    $coming = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($coming) {
        $response['data'] = $coming;
    }
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
