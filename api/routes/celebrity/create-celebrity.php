<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => "", "id" => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $publishDate = $_POST['publish_date'] ?? null;
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';

    if (empty($name) || empty($description)) {
        $response["message"] = "Please fill in all fields";
        echo json_encode($response);
        exit;
    }

    $imagePath = null;
    $storagePath = $protocol . '://' .  $_SERVER["SERVER_NAME"] . '/api/storage/celebrity/image/';

    if (!empty($_FILES['image']['name'])) {
        $handle = new Upload($_FILES['image']);
        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process(__DIR__ . '/../../storage/celebrity/image');
            if ($handle->processed) {
                $imagePath = $storagePath . $handle->file_dst_name;
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
        $stmt = $pdo->prepare("INSERT INTO celebrity_profile (name, description, image_path, publish_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $imagePath, $publishDate]);

        // Retrieve the last inserted ID
        $lastInsertedId = $pdo->lastInsertId();
        $response["success"] = true;
        $response["message"] = "Celebrity created successfully";
        $response["id"] = $lastInsertedId; // Include the ID in the response

    } catch (PDOException $e) {
        $response["message"] = "Error creating celebrity: " . $e->getMessage();
    }
}

echo json_encode($response);
exit;
