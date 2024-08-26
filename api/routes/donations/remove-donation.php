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
                    $filePathRelative = str_replace('/api/storage/donations/', '', $imagePath);
                    $filePathRelative = ltrim($filePathRelative, '/');
                    $directory = __DIR__ . '/../../storage/donations/';
                    $fileFullPath = $directory . $filePathRelative;

                    if (file_exists($fileFullPath)) {
                        if (unlink($fileFullPath)) {
                            $response['success'] = true;
                            $response['message'] = "Donation deleted successfully!";
                        } else {
                            $response['message'] = "Failed to delete file.";
                        }
                    } else {
                        $response['message'] = "File does not exist: " . $fileFullPath;
                    }
                } else {
                    $response['success'] = true;
                    $response['message'] = "Donation deleted successfully, no image to remove.";
                }
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
