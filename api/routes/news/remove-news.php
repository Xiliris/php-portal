<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT image_path, video_path FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagePath = $news['image_path'] ?? null;
            $videoPath = $news['video_path'] ?? null;

            $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                if ($imagePath) {
                    $filename = basename($imagePath);
                    $fileFullPath = __DIR__ . '/../../storage/news/' . $filename;
                    if (file_exists($fileFullPath)) {
                        unlink($fileFullPath);
                    }
                }

                if ($videoPath) {
                    $filename = basename($videoPath);
                    $fileFullPath = __DIR__ . '/../../storage/news/' . $filename;
                    if (file_exists($fileFullPath)) {
                        unlink($fileFullPath);
                    }
                }

                $response['success'] = true;
                $response['message'] = "News deleted successfully!";
            } else {
                $response['message'] = "News not found.";
            }
        } catch (Exception $e) {
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Invalid news ID.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
