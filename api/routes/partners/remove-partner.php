<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM partners WHERE id = ?");
            $stmt->execute([$id]);
            $partner = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagePath = $partner['image_path'] ?? null;
            $stmt = $pdo->prepare("DELETE FROM partners WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                if ($imagePath) {
                    $filename = basename($imagePath);
                    $fileFullPath = __DIR__ . '/../../storage/partners/' . $filename;
                    
                    if (file_exists($fileFullPath)) {
                        unlink($fileFullPath);
                    }
                }
                $response['success'] = true;
                $response['message'] = "Partner deleted successfully!";
            } else {
                $response['message'] = "Partner not found.";
            }
        } catch (Exception $e) {
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Invalid partner ID.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
