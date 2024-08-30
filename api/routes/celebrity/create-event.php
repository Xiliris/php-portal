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

    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
    $storagePath = __DIR__ . '/../../storage/celebrity/data';
    $storageUrl = $protocol . '://' . $_SERVER["SERVER_NAME"] . '/api/storage/celebrity/data';

    $images = isset($_FILES['images']) && is_array($_FILES['images']['name']) ? $_FILES['images'] : null;
    $documents = isset($_FILES['documents']) && is_array($_FILES['documents']['name']) ? $_FILES['documents'] : null;
    $audio = isset($_FILES['audio']) && is_array($_FILES['audio']['name']) ? $_FILES['audio'] : null;
    $video = isset($_FILES['video']) && is_array($_FILES['video']['name']) ? $_FILES['video'] : null;

    // Check for empty required fields
    if (empty($title) || empty($description) || empty($publish_date)) {
        $response["message"] = "Title, description, and publish date are required.";
        echo json_encode($response);
        exit;
    }

    // Insert event data into the database
    try {
        $stmt = $pdo->prepare('INSERT INTO celebrity_event_data (title, description, publish_date, celebrity_profile_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $description, $publish_date, $id]);
        $newEventId = $pdo->lastInsertId();
        $id = $newEventId; // Use this ID for further operations
        $response["id"] = $id; // Set the ID in the response
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $response["message"] = "Error: " . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    // Upload images
    if (isset($images['name']) && count($images['name']) > 0) {
        foreach ($images['name'] as $key => $value) {
            $file_tmp = $images['tmp_name'][$key];
            $file_type = mime_content_type($file_tmp);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($file_type, $allowedTypes)) {
                $response["message"] = "Invalid image type. Only JPG, PNG, GIF, and WebP are allowed.";
                echo json_encode($response);
                exit;
            }

            $upload = new Upload($file_tmp);
            $upload->file_new_name_body = uniqid();
            $upload->process($storagePath . '/images');

            if ($upload->processed) {
                $image_path = $storageUrl . '/images/' . $upload->file_dst_name;
                $upload->clean();

                try {
                    $stmt = $pdo->prepare('INSERT INTO celebrity_event_images (event_id, image_path) VALUES (?, ?)');
                    $stmt->execute([$id, $image_path]);
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    $response["message"] = "Image upload: Database error";
                    echo json_encode($response);
                    exit;
                }
            } else {
                error_log("Image upload failed: " . $upload->error);
                $response["message"] = "Image upload failed: " . $upload->error;
                echo json_encode($response);
                exit;
            }
        }
    }

    // Upload audio
    if (isset($audio['name']) && count($audio['name']) > 0) {
        foreach ($audio['name'] as $key => $value) {
            $file_tmp = $audio['tmp_name'][$key];
            $file_type = mime_content_type($file_tmp);
            $allowedTypes = ['audio/mpeg', 'audio/wav'];

            if (!in_array($file_type, $allowedTypes)) {
                $response["message"] = "Invalid audio type. Only MP3 and WAV are allowed.";
                echo json_encode($response);
                exit;
            }

            $upload = new Upload($file_tmp);
            $upload->file_new_name_body = uniqid();
            $upload->process($storagePath . '/audio');

            if ($upload->processed) {
                $audio_path = $storageUrl . '/audio/' . $upload->file_dst_name;
                $upload->clean();

                try {
                    $stmt = $pdo->prepare('INSERT INTO celebrity_event_audios (event_id, audio_path) VALUES (?, ?)');
                    $stmt->execute([$id, $audio_path]);
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    $response["message"] = "Audio upload: Database error";
                    echo json_encode($response);
                    exit;
                }
            } else {
                error_log("Audio upload failed: " . $upload->error);
                $response["message"] = "Audio upload failed: " . $upload->error;
                echo json_encode($response);
                exit;
            }
        }
    }

    // Upload documents
    if (isset($documents['name']) && count($documents['name']) > 0) {
        foreach ($documents['name'] as $key => $file_name) {
            $file_tmp = $documents['tmp_name'][$key];
            $file_type = mime_content_type($file_tmp);
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

            if (!in_array($file_type, $allowedTypes)) {
                $response["message"] = "Invalid document type. Only PDF, DOC, DOCX, and XLSX are allowed.";
                echo json_encode($response);
                exit;
            }

            $file_error = $documents['error'][$key];
            $original_name = $documents['name'][$key];

            if ($file_error === UPLOAD_ERR_OK) {
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_ext;
                $destination = __DIR__ . '/../../storage/celebrity/data/documents/' . $new_file_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $document_path = $storageUrl . '/documents/' . $new_file_name;

                    try {
                        $stmt = $pdo->prepare('INSERT INTO celebrity_event_documents (event_id, document_path, doc_type, original_name) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$id, $document_path, $file_ext, $original_name]);
                    } catch (PDOException $e) {
                        error_log("Database error: " . $e->getMessage());
                        $response["message"] = "Document upload: Database error";
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    error_log("Document move failed.");
                    $response["message"] = "Document move failed.";
                    echo json_encode($response);
                    exit;
                }
            } else {
                error_log("File upload error: " . $file_error);
                $response["message"] = "File upload error: " . $file_error;
                echo json_encode($response);
                exit;
            }
        }
    }

    // Upload videos
    if (isset($video['name']) && count($video['name']) > 0) {
        foreach ($video['name'] as $key => $value) {
            $file_tmp = $video['tmp_name'][$key];
            $file_type = mime_content_type($file_tmp);
            $allowedTypes = ['video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime', 'video/x-ms-wmv'];

            if (!in_array($file_type, $allowedTypes)) {
                $response["message"] = "Invalid video type. Only MP4, AVI, MPEG, QuickTime, and WMV are allowed.";
                echo json_encode($response);
                exit;
            }

            $upload = new Upload($file_tmp);
            $upload->file_new_name_body = uniqid();
            $upload->process($storagePath . '/videos');

            if ($upload->processed) {
                $video_path = $storageUrl . '/videos/' . $upload->file_dst_name;
                $upload->clean();

                try {
                    $stmt = $pdo->prepare('INSERT INTO celebrity_event_videos (event_id, video_path) VALUES (?, ?)');
                    $stmt->execute([$id, $video_path]);
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    $response["message"] = "Video upload: Database error";
                    echo json_encode($response);
                    exit;
                }
            } else {
                error_log("Video upload failed: " . $upload->error);
                $response["message"] = "Video upload failed: " . $upload->error;
                echo json_encode($response);
                exit;
            }
        }
    }

    $response["success"] = true;
    $response["message"] = "Event created successfully";
}

echo json_encode($response);
