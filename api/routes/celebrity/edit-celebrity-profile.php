<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => "", "data" => []];

$uploadDir = __DIR__ . '/../../storage/celebrity/image/';
$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
$baseUrl = $protocol . '://' . $_SERVER["SERVER_NAME"] . '/api/storage/celebrity/image';

$maxFileSize = 2 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        $publish_date = $_POST['publish_date'] ?? null;

        if (empty($id) || empty($name) || empty($description) || empty($publish_date)) {
            $response["message"] = "All fields are required.";
            echo json_encode($response);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE celebrity_profile SET name = :name, description = :description, publish_date = :publish_date WHERE id = :id");
            $stmt->execute(['name' => $name, 'description' => $description, 'publish_date' => $publish_date, 'id' => $id]);

            $fileUploadSuccess = true;

            if (isset($_FILES['image'])) {
                if ($_FILES['image']['size'] > $maxFileSize) {
                    $response["message"] = "Image file size exceeds the limit of 2MB.";
                    echo json_encode($response);
                    exit;
                }

                $imageData = handleFileUpload($_FILES['image'], $uploadDir, $baseUrl, ['image/jpeg', 'image/png', 'image/gif']);
                if (is_array($imageData)) {
                    $stmt = $pdo->prepare("UPDATE celebrity_profile SET image_path = :image_path WHERE id = :id");
                    $stmt->execute(['image_path' => $imageData['path'], 'id' => $id]);
                } else {
                    $response["message"] = $imageData;
                    $fileUploadSuccess = false;
                }
            }

            if ($fileUploadSuccess) {
                $response["success"] = true;
                $response["message"] = "Profile updated successfully.";
            } else {
                $response["success"] = false;
            }
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $response["message"] = "Error updating profile: " . $e->getMessage();
        }
    } else {
        $response["message"] = "ID is required.";
    }
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM celebrity_profile ORDER BY created_at ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            $response["message"] = "No profiles found.";
        } else {
            $response["success"] = true;
            $response["message"] = "Data retrieved successfully.";
            $response["data"] = $results;
        }
    } catch (PDOException $e) {
        $response["message"] = "Error retrieving data: " . $e->getMessage();
    }
}

function handleFileUpload($file, $uploadDir, $urlPath, $allowedMimeTypes)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $file['error']);
        return "File upload error: " . $file['error'];
    }

    $fileMimeType = mime_content_type($file['tmp_name']);
    error_log("Uploaded file: " . $file['name'] . " | Detected MIME type: " . $fileMimeType);

    if (!in_array($fileMimeType, $allowedMimeTypes)) {
        error_log("Invalid file type detected: " . $fileMimeType);
        return "Invalid file type: " . $fileMimeType;
    }

    $filename = basename($file['name']);
    $uniqueId = uniqid();
    $targetFilePath = $uploadDir . $uniqueId . "_" . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return [
            'path' => $urlPath . '/' . $uniqueId . "_" . $filename,
            'type' => pathinfo($filename, PATHINFO_EXTENSION),
            'original_name' => $filename
        ];
    }

    return "Failed to move uploaded file.";
}

echo json_encode($response);
