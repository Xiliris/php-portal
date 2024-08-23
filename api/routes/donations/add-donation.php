<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $header = htmlspecialchars($_POST['header']);
    $title = htmlspecialchars($_POST['title']);
    $buttonText = !empty($_POST['button']) ? htmlspecialchars($_POST['button']) : null;
    $link = !empty($_POST['link']) ? filter_var($_POST['link'], FILTER_SANITIZE_URL) : null;
    $description = htmlspecialchars($_POST['description']);

    $imagePath = null;
    $uploadDirectory = __DIR__ . '/../../storage/donations';

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }

    if (!empty($_FILES['image']['name'])) {
        $handle = new Upload($_FILES['image']);
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxFileSize = 5 * 1024 * 1024;

        if (!in_array($handle->file_src_mime, $allowedTypes)) {
            $response['message'] = "Invalid file type. Only JPEG and PNG files are allowed.";
            echo json_encode($response);
            exit;
        }

        if ($handle->file_src_size > $maxFileSize) {
            $response['message'] = "File size exceeds the maximum limit of 5MB.";
            echo json_encode($response);
            exit;
        }

        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process($uploadDirectory);
            if ($handle->processed) {
                $imagePath = '/api/storage/donations/' . $handle->file_dst_name;
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
        $stmt = $pdo->prepare("INSERT INTO donations (header, title, button_text, link, description, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$header, $title, $buttonText, $link, $description, $imagePath]);

        $lastInsertId = $pdo->lastInsertId();

        $response['success'] = true;
        $response['message'] = "Donation added successfully!";
        $response['data'] = ["id" => $lastInsertId, "header" => $header];
    } catch (Exception $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

echo json_encode($response);
