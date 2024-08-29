<?php
require __DIR__ . '/../../config.php'; // Ensure the correct path to your config.php

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Change to GET if you are not posting any data
    try {
        // Fetch all celebrity profiles
        $stmt = $pdo->prepare("SELECT * FROM celebrity_profile");
        $stmt->execute();
        $celebrities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($celebrities) {
            $currentDateTime = new DateTime();
            
            // Initialize an array to store results with events
            $celebrityData = [];

            foreach ($celebrities as $celebrity) {
                // Fetch all event data associated with each celebrity
                $stmtEvents = $pdo->prepare("SELECT * FROM celebrity_event_data WHERE celebrity_profile_id = :id ORDER BY created_at ASC");
                $stmtEvents->execute(['id' => $celebrity['id']]);
                $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

                // Filter events based on whether publish_date has passed
                $filteredEvents = array_filter($events, function ($event) use ($currentDateTime) {
                    $publishDate = new DateTime($event['publish_date']);
                    return $publishDate <= $currentDateTime;
                });

                // Store each celebrity and their filtered events
                $celebrityData[] = [
                    'celebrity' => $celebrity,
                    'events' => $filteredEvents
                ];
            }

            $response['success'] = true;
            $response['message'] = "Celebrities and events retrieved successfully.";
            $response['data'] = $celebrityData;
        } else {
            $response['message'] = "No celebrities found.";
        }
    } catch (Exception $e) {
        $response['message'] = "Error retrieving data: " . $e->getMessage();
    }

    // Output response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
