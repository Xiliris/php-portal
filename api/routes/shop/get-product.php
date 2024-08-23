<?php
require __DIR__ . '/../../config.php';

$response = ["data" => [], "message" => ""];

try {
    $stmt = $pdo->query("SELECT id, name, description, price, number, image_path FROM shop");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products) {
        $response['data'] = $products;
    }
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
