<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $link = htmlspecialchars($_POST['link']);
    $description = htmlspecialchars($_POST['description']);

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

    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $handle = new Upload($_FILES['image']);
        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process(__DIR__ . '/../../../storage/footer/');
            if ($handle->processed) {
                $imagePath = 'http://php-portal.local/storage/footer/' . $handle->file_dst_name;
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
?>
