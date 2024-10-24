<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => "", "id" => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $publishDate = $_POST['publish_date'] ?? null;
    $slug = $_POST['slug'];
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';

    if (empty($name) || empty($description) || empty($slug)) {
        $response["message"] = "Please fill in all fields";
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM celebrity_profile WHERE name = ?");
        $stmt->execute([$name]);
        $celebrity = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($celebrity) {
    
            $response["message"] = "A celebrity with the same name already exists.";
            echo json_encode($response);
            exit;
        }
    } catch (PDOException $e) {
        $response["message"] = "Error checking celebrity: " . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    $imagePath = null;
    $storageDirectory = __DIR__ . '/../../storage/celebrity/image/';
    $storageUrl = $protocol . '://' . $_SERVER["SERVER_NAME"] . '/api/storage/celebrity/image/';

    if (!is_dir($storageDirectory)) {
        if (!mkdir($storageDirectory, 0777, true)) {
            $response['message'] = "Failed to create directory for image upload.";
            echo json_encode($response);
            exit;
        }
    }

    if (!empty($_FILES['image']['name'])) {
        $handle = new Upload($_FILES['image']);
        if ($handle->uploaded) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($handle->file_src_mime, $allowedTypes)) {
                $response['message'] = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed. Detected MIME type: " . $handle->file_src_mime;
                echo json_encode($response);
                exit;
            }

            $maxFileSize = 2 * 1024 * 1024;
            if ($handle->file_src_size > $maxFileSize) {
                $response['message'] = "File size exceeds the 2MB limit.";
                echo json_encode($response);
                exit;
            }

            $handle->file_new_name_body = uniqid();
            $handle->process($storageDirectory);
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
        // Insert a new celebrity profile including the slug
        $stmt = $pdo->prepare("INSERT INTO celebrity_profile (name, description, image_path, publish_date, slug) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $imagePath, $publishDate, $slug]);

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
