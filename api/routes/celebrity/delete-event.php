<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

$basePath = realpath(__DIR__ . '/../../storage/celebrity/data');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventId = $_POST["id"];

    if (empty($eventId)) {
        $response["message"] = "Event ID is required";
    } else {
        try {
            $pdo->beginTransaction();

            // Fetch event data to ensure it exists
            $stmt = $pdo->prepare("SELECT id FROM celebrity_event_data WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($event) {
                // Delete images related to this event
                $stmt = $pdo->prepare("SELECT image_path FROM celebrity_event_images WHERE event_id = ?");
                $stmt->execute([$eventId]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($images as $image) {
                    $imagePath = $image['image_path'];
                    $fullImagePath = $basePath . '/images/' . basename($imagePath);
                    if ($fullImagePath && file_exists($fullImagePath)) {
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
                    if ($fullVideoPath && file_exists($fullVideoPath)) {
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
                    if ($fullAudioPath && file_exists($fullAudioPath)) {
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
                    if ($fullDocumentPath && file_exists($fullDocumentPath)) {
                        unlink($fullDocumentPath);
                    }
                }
                $stmt = $pdo->prepare("DELETE FROM celebrity_event_documents WHERE event_id = ?");
                $stmt->execute([$eventId]);

                // Finally, delete the event data
                $stmt = $pdo->prepare("DELETE FROM celebrity_event_data WHERE id = ?");
                $stmt->execute([$eventId]);

                if ($stmt->rowCount() > 0) {
                    $response["success"] = true;
                    $response["message"] = "Event and all related data deleted successfully";
                } else {
                    $response["message"] = "No event found with the given ID";
                }
            } else {
                $response["message"] = "No event found with the given ID";
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
