<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "data" => [], "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT name, link, svg_path FROM socials");
        $stmt->execute();

        $socials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($socials) {
            $defaultIconClass = 'fa-solid fa-globe';

            foreach ($socials as &$social) {
                if ($social['svg_path']) {
                    $social['svg_path'] = '/api/storage/socials/' . basename($social['svg_path']);
                } else {
                    $social['svg_path'] = $defaultIconClass;
                }
            }

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

header('Content-Type: application/json');
echo json_encode($response);
