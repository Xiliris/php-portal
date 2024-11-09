<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => "", "id" => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $title = isset($_POST['title']) ? $_POST['title'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $publish_date = isset($_POST['publish_date']) ? $_POST['publish_date'] : null;
    $slug = $_POST['slug'];
    $storagePath = __DIR__ . '/../../storage/celebrity/data';
    $storageUrl = 'http://' . $_SERVER["SERVER_NAME"] . '/api/storage/celebrity/data';

    $images = isset($_FILES['images']) && is_array($_FILES['images']['name']) ? $_FILES['images'] : null;
    $documents = isset($_FILES['documents']) && is_array($_FILES['documents']['name']) ? $_FILES['documents'] : null;
    $audio = isset($_FILES['audio']) && is_array($_FILES['audio']['name']) ? $_FILES['audio'] : null;
    $video = isset($_FILES['video']) && is_array($_FILES['video']['name']) ? $_FILES['video'] : null;

    $directories = ['images', 'audio', 'documents', 'videos'];
    foreach ($directories as $dir) {
        $path = $storagePath . '/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    // Check for empty required fields
    if (empty($title) || empty($description) || empty($publish_date)) {
        $response["message"] = "Title, description, and publish date are required.";
        echo json_encode($response);
        exit;
    }

    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedAudioTypes = ['audio/mpeg', 'audio/wav'];
    $allowedDocumentTypes = ['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime', 'video/x-ms-wmv'];

    $maxFileSizes = [
        'images' => 5 * 1024 * 1024,
        'audio' => 10 * 1024 * 1024,
        'documents' => 10 * 1024 * 1024,
        'videos' => 50 * 1024 * 1024,
    ];

    function validateFiles($files, $allowedTypes, $maxSize)
    {
        foreach ($files['name'] as $key => $file_name) {
            $file_tmp = $files['tmp_name'][$key];
            $file_type = mime_content_type($file_tmp);
            $file_size = $files['size'][$key];

            if (!in_array($file_type, $allowedTypes)) {
                return "Invalid file type: " . $file_type;
            }
            if ($file_size > $maxSize) {
                return "File size exceeds the maximum limit of " . ($maxSize / 1024 / 1024) . " MB.";
            }
        }
        return null;
    }

    if ($images && ($error = validateFiles($images, $allowedImageTypes, $maxFileSizes['images']))) {
        $response["message"] = "Image validation failed: " . $error;
        echo json_encode($response);
        exit;
    }
    if ($audio && ($error = validateFiles($audio, $allowedAudioTypes, $maxFileSizes['audio']))) {
        $response["message"] = "Audio validation failed: " . $error;
        echo json_encode($response);
        exit;
    }
    if ($documents && ($error = validateFiles($documents, $allowedDocumentTypes, $maxFileSizes['documents']))) {
        $response["message"] = "Document validation failed: " . $error;
        echo json_encode($response);
        exit;
    }
    if ($video && ($error = validateFiles($video, $allowedVideoTypes, $maxFileSizes['videos']))) {
        $response["message"] = "Video validation failed: " . $error;
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT id FROM celebrity_event_data WHERE title = ?');
        $stmt->execute([$title]);
        $duplicateEventId = $stmt->fetchColumn();

        if ($duplicateEventId) {
            $deleteStmt = $pdo->prepare('DELETE FROM celebrity_event_data WHERE id = ?');
            $deleteStmt->execute([$duplicateEventId]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $response["message"] = "Error: " . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    // Insert event data into the database
    try {
        $stmt = $pdo->prepare('INSERT INTO celebrity_event_data (title, description, publish_date, celebrity_profile_id, slug) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $publish_date, $id, $slug]);
        $newEventId = $pdo->lastInsertId();
        $id = $newEventId; // Use this ID for further operations
        $response["id"] = $id; // Set the ID in the response
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $response["message"] = "Error: " . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    function uploadFiles($files, $path, $urlPath, $pdo, $id, $table, $column)
    {
        foreach ($files['name'] as $key => $file_name) {
            $file_tmp = $files['tmp_name'][$key];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid() . '.' . $file_ext;
            $destination = $path . '/' . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $file_path = $urlPath . '/' . $new_file_name;

                try {
                    $stmt = $pdo->prepare("INSERT INTO $table (event_id, $column) VALUES (?, ?)");
                    $stmt->execute([$id, $file_path]);
                } catch (PDOException $e) {
                    error_log("Database error during file upload: " . $e->getMessage());
                    $response["message"] = ucfirst($column) . " upload: Database error: " . $e->getMessage();
                    echo json_encode($response);
                    exit;
                }
            } else {
                error_log(ucfirst($column) . " upload failed: Could not move file.");
                $response["message"] = ucfirst($column) . " upload failed: Could not move file.";
                echo json_encode($response);
                exit;
            }
        }
    }

    if ($images) {
        uploadFiles($images, $storagePath . '/images', $storageUrl . '/images', $pdo, $id, 'celebrity_event_images', 'image_path');
    }

    if ($audio) {
        uploadFiles($audio, $storagePath . '/audio', $storageUrl . '/audio', $pdo, $id, 'celebrity_event_audios', 'audio_path');
    }

    // Upload documents
    if ($documents) {
        foreach ($documents['name'] as $key => $doc_name) {
            $doc_name = str_replace(' ', '_', $doc_name);
            $doc_tmp = $documents['tmp_name'][$key];
            $doc_error = $documents['error'][$key];

            if ($doc_error === UPLOAD_ERR_OK) {
                $doc_ext = pathinfo($doc_name, PATHINFO_EXTENSION);
                $new_doc_name = uniqid() . '.' . $doc_ext;
                $destination = $storagePath . '/documents/' . $new_doc_name;

                if ($documents['size'][$key] > $maxFileSizes['documents']) {
                    $response["message"] = "Document size exceeds the maximum limit of " . ($maxFileSizes['documents'] / 1024 / 1024) . " MB.";
                    echo json_encode($response);
                    exit;
                }

                if (move_uploaded_file($doc_tmp, $destination)) {
                    $document_path = $storageUrl . '/documents/' . $new_doc_name;

                    try {
                        $stmt = $pdo->prepare('INSERT INTO celebrity_event_documents (event_id, document_path, doc_type, original_name) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$id, $document_path, $doc_ext, $doc_name]);
                    } catch (PDOException $e) {
                        error_log("Database error during document upload: " . $e->getMessage());
                        $response["message"] = "Document upload: Database error: " . $e->getMessage();
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    error_log("Document upload failed: Could not move file.");
                    $response["message"] = "Document upload failed: Could not move file.";
                    echo json_encode($response);
                    exit;
                }
            }
        }
    }

    // Upload videos
    if ($video) {
        uploadFiles($video, $storagePath . '/videos', $storageUrl . '/videos', $pdo, $id, 'celebrity_event_videos', 'video_path');
    }

    $response["success"] = true;
    $response["message"] = "Event created successfully!";
}

echo json_encode($response);
