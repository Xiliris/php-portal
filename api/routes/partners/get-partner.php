<?php
require __DIR__ . '/../../config.php';

$response = ["data" => [], "message" => ""];

try {
    $stmt = $pdo->query("SELECT id, link, image_path FROM partners");
    $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($partners) {
        $response['data'] = $partners;
    }
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
