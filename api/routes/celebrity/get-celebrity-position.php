<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER["REQUEST_METHOD"] === 'GET') {
    try {
        // Query to select all positions with the corresponding celebrity profiles
        $stmt = $pdo->query("
            SELECT chp.position, cp.id, cp.name, cp.image_path
            FROM celebrity_home_position chp
            JOIN celebrity_profile cp ON chp.celebrity_profile_id = cp.id
            ORDER BY chp.position ASC
        ");

        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($positions) {
            $response['success'] = true;
            $response['data'] = $positions;
        } else {
            $response['message'] = "No positions found.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }

    echo json_encode($response);
}
?>
