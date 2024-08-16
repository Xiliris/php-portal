<?php
require __DIR__ . '/../../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventId = intval($_GET['id']);

    if (empty($eventId)) {
        $response["message"] = "Event ID is required";
        echo json_encode($response);
        exit;
    }

    try {
        // Step 1: Check if documents exist for the given event_id
        $stmt = $pdo->prepare("SELECT document_path, doc_type FROM celebrity_event_documents WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $eventId]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($documents)) {
            $response["message"] = "No documents found for the given Event ID.";
            echo json_encode($response);
            exit;
        }

        // Step 2: Fetch event data based on event_id
        $stmt = $pdo->prepare("SELECT publish_date FROM celebrity_event_data WHERE id = :event_id LIMIT 1");
        $stmt->execute(['event_id' => $eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            $response["message"] = "Event not found for the given Event ID.";
            echo json_encode($response);
            exit;
        }

        // Step 3: Check if the publish_date has passed
        $currentDateTime = new DateTime();
        $publishDate = new DateTime($event['publish_date']);

        if ($publishDate > $currentDateTime) {
            $response["message"] = "The event has not been published yet.";
            echo json_encode($response);
            exit;
        }

        // Step 4: Structure the response data
        $response["success"] = true;
        $response["message"] = "Data retrieved successfully";
        $response["data"] = [
            'event_id' => $eventId,
            'publish_date' => $event['publish_date'],
            'documents' => $documents // Includes both document_path and doc_type
        ];
    } catch (PDOException $e) {
        $response["message"] = "Error retrieving data: " . $e->getMessage();
    }
}

// Output response as JSON
header('Content-Type: application/json');
echo json_encode($response);
