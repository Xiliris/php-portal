<?php
require __DIR__ . '/../../config.php';

$response = ["data" => [], "message" => ""];

try {
    $stmt = $pdo->query("SELECT id, title, text, image_path, video_path, publish_date FROM news ORDER BY publish_date DESC");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($news) {
        $response['data'] = $news;
    }
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
