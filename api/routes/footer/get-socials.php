<?php
require __DIR__ . '/../../config.php'; // Ensure the path to config.php is correct

$response = ["success" => false, "data" => [], "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Prepare the SQL statement to select all records from the socials table
        $stmt = $pdo->prepare("SELECT name, link FROM socials");
        $stmt->execute();

        // Fetch all results as an associative array
        $socials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($socials) {
            $response['success'] = true;
            $response['data'] = $socials;
            $response['message'] = 'Social media links retrieved successfully.';
        } else {
            $response['message'] = 'No social media links found.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
