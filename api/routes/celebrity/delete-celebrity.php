<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

$basePath = realpath(__DIR__ . '/../../storage/celebrity/data');
$imagePath = realpath(__DIR__ . '/../../storage/celebrity/image');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"] ?? '';

    if (empty($id)) {
        $response["message"] = "ID is required";
    } else {
        try {
            $pdo->beginTransaction();

            // Fetch the main celebrity record
            $stmt = $pdo->prepare("SELECT image_path FROM celebrity_profile WHERE id = ?");
            $stmt->execute([$id]);
            $celebrity = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($celebrity) {
                $profileImagePath = $celebrity['image_path'];
                $fullProfileImagePath = $imagePath . '/' . basename($profileImagePath);

                // Fetch event data linked to this celebrity
                $stmt = $pdo->prepare("SELECT id FROM celebrity_event_data WHERE celebrity_profile_id = ?");
                $stmt->execute([$id]);
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($events as $event) {
                    $eventId = $event['id'];

                    // Delete images related to this event
                    $stmt = $pdo->prepare("SELECT image_path FROM celebrity_event_images WHERE event_id = ?");
                    $stmt->execute([$eventId]);
                    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($images as $image) {
                        $imagePath = $image['image_path'];
                        $fullImagePath = $basePath . '/images/' . basename($imagePath);
                        if (file_exists($fullImagePath)) {
                            unlink($fullImagePath);
                        }
                    }
                    $stmt = $pdo->prepare("DELETE FROM celebrity_event_images WHERE event_id = ?");
                    $stmt->execute([$eventId]);

                    // Delete videos related to this event
                    $stmt = $pdo->prepare("SELECT video_path FROM celebrity_event_videos WHERE event_id = ?");
                    $stmt->execute([$eventId]);
                    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($videos as $video) {
                        $videoPath = $video['video_path'];
                        $fullVideoPath = $basePath . '/videos/' . basename($videoPath);
                        if (file_exists($fullVideoPath)) {
                            unlink($fullVideoPath);
                        }
                    }
                    $stmt = $pdo->prepare("DELETE FROM celebrity_event_videos WHERE event_id = ?");
                    $stmt->execute([$eventId]);

                    // Delete audios related to this event
                    $stmt = $pdo->prepare("SELECT audio_path FROM celebrity_event_audios WHERE event_id = ?");
                    $stmt->execute([$eventId]);
                    $audios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($audios as $audio) {
                        $audioPath = $audio['audio_path'];
                        $fullAudioPath = $basePath . '/audio/' . basename($audioPath);
                        if (file_exists($fullAudioPath)) {
                            unlink($fullAudioPath);
                        }
                    }
                    $stmt = $pdo->prepare("DELETE FROM celebrity_event_audios WHERE event_id = ?");
                    $stmt->execute([$eventId]);

                    // Delete documents related to this event
                    $stmt = $pdo->prepare("SELECT document_path FROM celebrity_event_documents WHERE event_id = ?");
                    $stmt->execute([$eventId]);
                    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($documents as $document) {
                        $documentPath = $document['document_path'];
                        $fullDocumentPath = $basePath . '/documents/' . basename($documentPath);
                        if (file_exists($fullDocumentPath)) {
                            unlink($fullDocumentPath);
                        }
                    }
                    $stmt = $pdo->prepare("DELETE FROM celebrity_event_documents WHERE event_id = ?");
                    $stmt->execute([$eventId]);

                    // Finally, delete the event data
                    $stmt = $pdo->prepare("DELETE FROM celebrity_event_data WHERE id = ?");
                    $stmt->execute([$eventId]);
                }

                // Delete the main image if it exists
                if (file_exists($fullProfileImagePath)) {
                    unlink($fullProfileImagePath);
                }

                // Delete the main record in celebrity_profile
                $stmt = $pdo->prepare("DELETE FROM celebrity_profile WHERE id = ?");
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    $response["success"] = true;
                    $response["message"] = "Celebrity and all related data deleted successfully";
                } else {
                    $response["message"] = "No celebrity found with the given ID";
                }
            } else {
                $response["message"] = "No celebrity found with the given ID";
            }

            $pdo->commit();
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $response["message"] = "Database error: " . $e->getMessage();
        }
    }
} else {
    $response["message"] = "Invalid request method";
}

echo json_encode($response);
