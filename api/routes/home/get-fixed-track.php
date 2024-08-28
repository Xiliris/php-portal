<?php
require __DIR__ . '/../../config.php'; // Ensure the path to config.php is correct

$response = ["success" => false, "data" => null, "message" => ""];

try {
    // Prepare the statement to get the record
    $stmt = $pdo->prepare("SELECT content, time, moving, enabled FROM fixed_track WHERE id = 1");

    // Execute the statement
    $stmt->execute();

    // Fetch the record
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        $response['success'] = true;
        $response['data'] = $record;
    } else {
        $response['message'] = 'Record not found.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);
