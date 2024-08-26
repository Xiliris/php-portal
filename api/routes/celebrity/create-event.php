<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Verot\Upload\Upload;

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $title = isset($_POST['title']) ? $_POST['title'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $publish_date = isset($_POST['publish_date']) ? $_POST['publish_date'] : null;

    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
    $storagePath = $protocol . '://' .  $_SERVER["SERVER_NAME"] . '/api/storage/celebrity/data';

    $images = isset($_FILES['images']) && is_array($_FILES['images']['name']) ? $_FILES['images'] : null;
    $documents = isset($_FILES['documents']) && is_array($_FILES['documents']['name']) ? $_FILES['documents'] : null;
    $audio = isset($_FILES['audio']) && is_array($_FILES['audio']['name']) ? $_FILES['audio'] : null;
    $video = isset($_FILES['video']) && is_array($_FILES['video']['name']) ? $_FILES['video'] : null;


    // Insert event data into database
    try {
        $stmt = $pdo->prepare('INSERT INTO celebrity_event_data (title, description, publish_date, celebrity_profile_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $description, $publish_date, $id]);
        $newEventId = $pdo->lastInsertId();

        $id = $newEventId;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $response["message"] = "Error: " . $e->getMessage();
    }


    // Upload images
    if (isset($images['name']) && count($images['name']) > 0) {
        foreach ($images['name'] as $key => $value) {
            $file_tmp = $images['tmp_name'][$key];

            $upload = new Upload($file_tmp);
            $upload->file_new_name_body = uniqid();
            $upload->process(__DIR__ . '/../../storage/celebrity/data/images');

            if ($upload->processed) {
                $image_path = $storagePath . '/images/' . $upload->file_dst_name;
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

            $upload = new Upload($file_tmp);
            $upload->file_new_name_body = uniqid();
            $upload->allowed = array('audio/mpeg', 'audio/wav');
            $upload->process(__DIR__ . '/../../storage/celebrity/data/audio');

            if ($upload->processed) {
                $audio_path = $storagePath . '/audio/' . $upload->file_dst_name;
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
            $file_type = $documents['type'][$key];
            $file_size = $documents['size'][$key];
            $file_error = $documents['error'][$key];

            if ($file_error === UPLOAD_ERR_OK) {
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_ext;
                $destination = __DIR__ . '/../../storage/celebrity/data/documents/' . $new_file_name;

                // Move file from temp directory to final destination
                if (move_uploaded_file($file_tmp, $destination)) {
                    $document_path = $storagePath . '/documents/' . $new_file_name;

                    try {
                        $stmt = $pdo->prepare('INSERT INTO celebrity_event_documents (event_id, document_path, doc_type) VALUES (?, ?, ?)');
                        $stmt->execute([$id, $document_path, $file_ext]);
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

            $upload = new Upload($file_tmp);
            $upload->file_new_name_body = uniqid();
            $upload->allowed = array(
                'video/mp4',
                'video/avi',
                'video/mpeg',
                'video/quicktime',
                'video/x-ms-wmv'
            );
            $upload->process(__DIR__ . '/../../storage/celebrity/data/videos');

            if ($upload->processed) {
                $video_path = $storagePath . '/videos/' . $upload->file_dst_name;
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

    // Check for empty required fields
    if (empty($id) || empty($title) || empty($description) || empty($publish_date)) {
        $response["success"] = false;
        $response["message"] = "Id, title, description, and publish date are required";
        echo json_encode($response);
        exit;
    }


    $response["success"] = true;
    $response["message"] = "Event created successfully";
}

echo json_encode($response);
