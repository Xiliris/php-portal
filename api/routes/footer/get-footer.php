<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER["REQUEST_METHOD"] == 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM footer");
    $stmt->execute();
    $footer = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($footer) {
        $response['success'] = true;
        $response['message'] = "Footer found.";
        $response['data'] = $footer;
    } else {
        $response['message'] = "Footer not found.";
    }

    echo json_encode($response);
}
?>