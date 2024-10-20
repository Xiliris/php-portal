<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => "", "data" => []];

$uploadDirs = [
    'image' => __DIR__ . '/../../storage/celebrity/data/images/',
    'video' => __DIR__ . '/../../storage/celebrity/data/videos/',
    'audio' => __DIR__ . '/../../storage/celebrity/data/audio/',
    'document' => __DIR__ . '/../../storage/celebrity/data/documents/',
];

$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
$baseUrl = $protocol . '://' . $_SERVER["SERVER_NAME"] . '/api/storage/celebrity/data';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $title = $_POST['title'] ?? null;
        $description = $_POST['description'] ?? null;
        $publish_date = $_POST['publish_date'] ?? null;

        if (empty($id) || empty($title) || empty($description) || empty($publish_date)) {
            $response["message"] = "All fields are required.";
            echo json_encode($response);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE celebrity_event_data SET title = :title, description = :description, publish_date = :publish_date WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'publish_date' => $publish_date, 'id' => $id]);

            $uploadSuccess = true;

            if (isset($_FILES['image'])) {
                foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
                    $fileData = [
                        'name' => $_FILES['image']['name'][$key],
                        'type' => $_FILES['image']['type'][$key],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['image']['error'][$key],
                        'size' => $_FILES['image']['size'][$key]
                    ];
                    $imageData = handleFileUpload($fileData, $uploadDirs['image'], $baseUrl . '/images', ['image/jpeg', 'image/png', 'image/gif']);
                    if (is_array($imageData)) {
                        saveFilePathInDatabase('celebrity_event_images', $id, $imageData['path'], null, null, $pdo);
                    } else {
                        $uploadSuccess = false;
                        $response["message"] .= $imageData . " ";
                    }
                }
            }

            if (isset($_FILES['video'])) {
                foreach ($_FILES['video']['tmp_name'] as $key => $tmpName) {
                    $fileData = [
                        'name' => $_FILES['video']['name'][$key],
                        'type' => $_FILES['video']['type'][$key],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['video']['error'][$key],
                        'size' => $_FILES['video']['size'][$key]
                    ];
                    $videoData = handleFileUpload($fileData, $uploadDirs['video'], $baseUrl . '/videos', ['video/mp4', 'video/x-m4v']);
                    if (is_array($videoData)) {
                        saveFilePathInDatabase('celebrity_event_videos', $id, $videoData['path'], null, null, $pdo);
                    } else {
                        $uploadSuccess = false;
                        $response["message"] .= $videoData . " ";
                    }
                }
            }

            if (isset($_FILES['audio'])) {
                foreach ($_FILES['audio']['tmp_name'] as $key => $tmpName) {
                    $fileData = [
                        'name' => $_FILES['audio']['name'][$key],
                        'type' => $_FILES['audio']['type'][$key],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['audio']['error'][$key],
                        'size' => $_FILES['audio']['size'][$key]
                    ];
                    $audioData = handleFileUpload($fileData, $uploadDirs['audio'], $baseUrl . '/audio', ['audio/mpeg', 'audio/wav']);
                    if (is_array($audioData)) {
                        saveFilePathInDatabase('celebrity_event_audios', $id, $audioData['path'], null, null, $pdo);
                    } else {
                        $uploadSuccess = false;
                        $response["message"] .= $audioData . " ";
                    }
                }
            }

            if (isset($_FILES['document'])) {
                foreach ($_FILES['document']['tmp_name'] as $key => $tmpName) {
                    $fileData = [
                        'name' => $_FILES['document']['name'][$key],
                        'type' => $_FILES['document']['type'][$key],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['document']['error'][$key],
                        'size' => $_FILES['document']['size'][$key]
                    ];
                    $documentData = handleFileUpload($fileData, $uploadDirs['document'], $baseUrl . '/documents', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
                    if (is_array($documentData)) {
                        saveFilePathInDatabase('celebrity_event_documents', $id, $documentData['path'], $documentData['type'], $documentData['original_name'], $pdo);
                    } else {
                        $uploadSuccess = false;
                        $response["message"] .= $documentData . " ";
                    }
                }
            }

            if ($uploadSuccess) {
                $response["success"] = true;
                $response["message"] = "Event updated successfully, including uploads.";
            } else {
                $response["success"] = false;
                if (empty($response["message"])) {
                    $response["message"] = "Event updated, but some files were not uploaded due to invalid types.";
                }
            }
        } catch (PDOException $e) {
            $response["message"] = "Error updating event: " . $e->getMessage();
        }
    } else {
        $response["message"] = "ID is required.";
    }
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM celebrity_event_data ORDER BY created_at ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            $response["message"] = "No events found.";
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

function saveFilePathInDatabase($tableName, $eventId, $filePath, $docType = null, $originalName = null, $pdo)
{
    if ($tableName === 'celebrity_event_documents') {
        $stmt = $pdo->prepare("INSERT INTO $tableName (event_id, document_path, doc_type, original_name) VALUES (:event_id, :file_path, :doc_type, :original_name)");
        $stmt->execute(['event_id' => $eventId, 'file_path' => $filePath, 'doc_type' => $docType, 'original_name' => $originalName]);
    } elseif ($tableName === 'celebrity_event_images') {
        $stmt = $pdo->prepare("INSERT INTO $tableName (event_id, image_path) VALUES (:event_id, :file_path)");
        $stmt->execute(['event_id' => $eventId, 'file_path' => $filePath]);
    } elseif ($tableName === 'celebrity_event_audios') {
        $stmt = $pdo->prepare("INSERT INTO $tableName (event_id, audio_path) VALUES (:event_id, :file_path)");
        $stmt->execute(['event_id' => $eventId, 'file_path' => $filePath]);
    } elseif ($tableName === 'celebrity_event_videos') {
        $stmt = $pdo->prepare("INSERT INTO $tableName (event_id, video_path) VALUES (:event_id, :file_path)");
        $stmt->execute(['event_id' => $eventId, 'file_path' => $filePath]);
    }
}

header('Content-Type: application/json');
echo json_encode($response);
