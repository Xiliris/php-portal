<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $text = htmlspecialchars($_POST['text']);
    $imagePath = null;
    $videoPath = null;

    if (empty($title) || empty($text)) {
        $response['message'] = "Title and text are required.";
        echo json_encode($response);
        exit;
    }

    if (!empty($_FILES['image']['name'])) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileMimeType = mime_content_type($fileTmpPath);

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $response['message'] = "Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.";
            echo json_encode($response);
            exit;
        }

        $directory = __DIR__ . '/../../storage/news';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = new Upload($_FILES['image']);
        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process($directory);
            if ($handle->processed) {
                $imagePath = '/api/storage/news/' . $handle->file_dst_name;
                $handle->clean();
            } else {
                $response['message'] = "Image upload failed: " . $handle->error;
                echo json_encode($response);
                exit;
            }
        }
    } elseif (!empty($_FILES['video']['name'])) {
        $allowedMimeTypes = ['video/mp4', 'video/webm'];
        $fileTmpPath = $_FILES['video']['tmp_name'];
        $fileMimeType = mime_content_type($fileTmpPath);

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $response['message'] = "Invalid video type. Only MP4 and WebM are allowed.";
            echo json_encode($response);
            exit;
        }

        $directory = __DIR__ . '/../../storage/news';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = new Upload($_FILES['video']);
        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process($directory);
            if ($handle->processed) {
                $videoPath = '/api/storage/news/' . $handle->file_dst_name;
                $handle->clean();
            } else {
                $response['message'] = "Video upload failed: " . $handle->error;
                echo json_encode($response);
                exit;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO news (title, text, image_path, video_path, publish_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $text, $imagePath, $videoPath, date('Y-m-d H:i:s')]);

        $response['success'] = true;
        $response['message'] = "News added successfully!";
    } catch (Exception $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

echo json_encode($response);
