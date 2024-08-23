<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $link = htmlspecialchars($_POST['link']);
    $description = htmlspecialchars($_POST['description']);
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';

    if (empty($title)) {
        $response['message'] = "Title cannot be empty.";
        echo json_encode($response);
        exit;
    }

    if (empty($link)) {
        $response['message'] = "Link cannot be empty.";
        echo json_encode($response);
        exit;
    }

    if (empty($description)) {
        $response['message'] = "Description cannot be empty.";
        echo json_encode($response);
        exit;
    }

    $storagePath = __DIR__ . '/../../storage/footer';
    $storageUrl = $protocol . '://' .  $_SERVER["SERVER_NAME"] . '/api/storage/footer/';
    $imagePath = null;

    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0755, true);
    }

    if (!empty($_FILES['image']['name'])) {
        $handle = new Upload($_FILES['image']);

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($handle->file_src_mime, $allowed_types)) {
            $response['message'] = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
            echo json_encode($response);
            exit;
        }

        $maxFileSize = 5 * 1024 * 1024;
        if ($handle->file_src_size > $maxFileSize) {
            $response['message'] = "File size exceeds the maximum limit of 5MB.";
            echo json_encode($response);
            exit;
        }

        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process($storagePath);
            if ($handle->processed) {
                $imagePath = $storageUrl . $handle->file_dst_name;

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
        $stmt = $pdo->prepare("INSERT INTO footer (title, link, image_path, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $link, $imagePath, $description]);

        $lastInsertId = $pdo->lastInsertId();

        $response['success'] = true;
        $response['message'] = "Footer added successfully!";
        $response['data'] = [
            "id" => $lastInsertId,
            "title" => $title,
            "link" => $link,
            "image_path" => $imagePath,
            "description" => $description
        ];
    } catch (Exception $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

echo json_encode($response);
