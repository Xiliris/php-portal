<?php
require __DIR__ . '/../../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventId = $_GET['name'];

    $stmt = $pdo->prepare("SELECT * FROM celebrity_event_documents WHERE original_name = ?");
    $stmt->execute([$eventId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $response["success"] = true;
        $response["message"] = "Document retrieved successfully.";
        $response["data"] = $result; 
    } else {
        $response["message"] = "No document found with that name.";
    }
}

echo json_encode($response);
