<?php
require __DIR__ . '/../../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (empty($id)) {
        $response["message"] = "ID is required";
        echo json_encode($response);
        exit;
    }

    try {
        // Prepare and execute query to search by celebrity_profile_id
        $stmt = $pdo->prepare("SELECT * FROM celebrity_event_data WHERE celebrity_profile_id = :slug ORDER BY created_at ASC");
        $stmt->execute(['slug' => $id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentDateTime = new DateTime();

        // Filter results based on whether publish_date has passed
        $filteredResults = array_filter($results, function ($event) use ($currentDateTime) {
            $publishDate = new DateTime($event['publish_date']);
            return $publishDate <= $currentDateTime;
        });

        // Check if any results were found
        if (empty($filteredResults)) {
            $response["message"] = "No events found for the given ID or all events are in the future.";
        } else {
            $response["success"] = true;
            $response["message"] = "Data retrieved successfully";
            $response["data"] = $filteredResults;
        }
    } catch (PDOException $e) {
        $response["message"] = "Error retrieving data: " . $e->getMessage();
    }
}

// Output response as JSON
header('Content-Type: application/json');
echo json_encode($response);
