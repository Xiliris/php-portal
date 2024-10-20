<?php
require __DIR__ . '/../../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (empty($id)) {
        $response["message"] = "ID is required.";
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM celebrity_event_data WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response["success"] = true;
            $response["message"] = "Event data retrieved successfully.";
            $response["data"] = [$result];
        } else {
            $response["message"] = "Event not found.";
        }
    } catch (PDOException $e) {
        $response["message"] = "Error retrieving data: " . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
