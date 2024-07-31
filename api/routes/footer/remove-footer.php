<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM footer WHERE id = ?");
            $stmt->execute([$id]);
            $footer = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagePath = $footer['image_path'] ?? null;

            $stmt = $pdo->prepare("DELETE FROM footer WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {

                if ($imagePath) {
                    $imagePath = str_replace('http://php-portal.local', __DIR__ . '/../../../', $imagePath);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $response['success'] = true;
                $response['message'] = "Footer item deleted successfully!";
            } else {
                $response['message'] = "Footer item not found.";
            }
        } catch (Exception $e) {
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Invalid footer item ID.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>
