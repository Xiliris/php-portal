<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];

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
                $imagePath = $celebrity['image_path'];

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
                }

                // Delete the main image if it exists
                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
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
