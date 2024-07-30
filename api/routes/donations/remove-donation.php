<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM donations WHERE id = ?");
            $stmt->execute([$id]);
            $donation = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagePath = $donation['image_path'] ?? null;

            $stmt = $pdo->prepare("DELETE FROM donations WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                if ($imagePath) {
                    $imagePath = str_replace('http://php-portal.local', __DIR__ . '/../../../', $imagePath);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $response['success'] = true;
                $response['message'] = "Donation deleted successfully!";
            } else {
                $response['message'] = "Donation not found.";
            }
        } catch (Exception $e) {
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Invalid donation ID.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>
