<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

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
                    if ($imagePath && file_exists($imagePath)) {
                        unlink($imagePath);
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
                    if ($videoPath && file_exists($videoPath)) {
                        unlink($videoPath);
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
                    if ($audioPath && file_exists($audioPath)) {
                        unlink($audioPath);
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
                    if ($documentPath && file_exists($documentPath)) {
                        unlink($documentPath);
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
