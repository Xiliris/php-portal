<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM shop WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $imagePath = $product['image_path'] ?? null;
                $stmt = $pdo->prepare("DELETE FROM shop WHERE id = ?");
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    if ($imagePath) {
                        $directory = __DIR__ . '/../../storage/shop';
                        $filePathRelative = str_replace('/api/storage/shop', '', $imagePath);
                        $fileFullPath = $directory . $filePathRelative;

                        if (file_exists($fileFullPath)) {
                            if (unlink($fileFullPath)) {
                                $response['message'] = "File deleted successfully.";
                            } else {
                                $response['message'] = "Failed to delete file.";
                            }
                        } else {
                            $response['message'] = "File does not exist: " . $fileFullPath;
                        }
                    }
                    $response['success'] = true;
                    $response['message'] = "Product deleted successfully!";
                } else {
                    $response['message'] = "Product not found.";
                }
            } else {
                $response['message'] = "Product not found.";
            }
        } catch (Exception $e) {
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Invalid product ID.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
