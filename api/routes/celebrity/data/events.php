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
        // Query the celebrity_profile by slug
        $stmt = $pdo->prepare("SELECT * FROM celebrity_profile WHERE slug = :slug");
        $stmt->execute(['slug' => $id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the profile was found
        if (!$profile) {
            $response["message"] = "No profile found for the given slug.";
            echo json_encode($response);
            exit;
        }

        // Prepare and execute query to search by celebrity_profile_id
        $stmt = $pdo->prepare("SELECT * FROM celebrity_event_data WHERE celebrity_profile_id = :id ORDER BY created_at ASC");
        $stmt->execute(['id' => $profile["id"]]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentDateTime = new DateTime();

        // Filter results based on whether publish_date has passed
        $filteredResults = array_filter($results, function ($event) use ($currentDateTime) {
            $publishDate = new DateTime($event['publish_date']);
            return $publishDate <= $currentDateTime;
        });

        // Check if any results were found
        if (empty($filteredResults)) {
            $response["message"] = "No events found for the given profile or all events are in the future.";
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
