<?php
require __DIR__ . '/../../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventId = $_GET['id'];

    if (!$eventId) {
        $response["message"] = "Valid Event ID is required";
        echo json_encode($response);
        exit;
    }

    try {
        // Fetch event data first
        $stmt = $pdo->prepare("SELECT * FROM celebrity_event_data WHERE slug = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            $response["message"] = "Event not found";
            echo json_encode($response);
            exit;
        }

        // Check if the event has been published
        $currentDateTime = new DateTime();
        $publishDate = new DateTime($event['publish_date']);

        if ($publishDate > $currentDateTime) {
            $response["message"] = "The event has not been published yet.";
            echo json_encode($response);
            exit;
        }

        // Fetch only video paths from celebrity_event_videos
        $stmt = $pdo->prepare("SELECT video_path FROM celebrity_event_videos WHERE event_id = ?");
        $stmt->execute([$event["id"]]);
        $videoPaths = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($videoPaths)) {
            $response["message"] = "No video files found for the specified event";
            echo json_encode($response);
            exit;
        }

        $response["success"] = true;
        $response["message"] = "Video paths retrieved successfully";
        $response["data"] = $videoPaths;
    } catch (PDOException $e) {
        $response["message"] = "Database error: " . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    echo json_encode($response);
}
