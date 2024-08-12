<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = htmlspecialchars($_POST['link']);

    if (empty($link)) {
        $response['message'] = "Link cannot be empty.";
        echo json_encode($response);
        exit;
    }

    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024;

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileMimeType = mime_content_type($fileTmpPath);

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $response['message'] = "Invalid file type. Only JPEG, PNG, and GIF files are allowed.";
            echo json_encode($response);
            exit;
        }

        if ($fileSize > $maxFileSize) {
            $response['message'] = "File size exceeds the 2MB limit.";
            echo json_encode($response);
            exit;
        }

        $directory = __DIR__ . '/../../../storage/partners/';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = new Upload($_FILES['image']);
        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process($directory);
            if ($handle->processed) {
                $imagePath = 'http://php-portal.local/storage/partners/' . $handle->file_dst_name;
                $handle->clean();
            } else {
                $response['message'] = "File upload failed: " . $handle->error;
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = "File upload failed: " . $handle->error;
            echo json_encode($response);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO partners (link, image_path) VALUES (?, ?)");
        $stmt->execute([$link, $imagePath]);

        $lastInsertId = $pdo->lastInsertId();

        $response['success'] = true;
        $response['message'] = "Partner added successfully!";
        $response['data'] = [
            "id" => $lastInsertId,
            "link" => $link,
            "image_path" => $imagePath
        ];
    } catch (Exception $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

echo json_encode($response);
