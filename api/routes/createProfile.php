<?php
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Unknown error'];

require '../config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['picture']) && isset($_POST['bio']) && isset($_FILES['profile_documents']) && isset($_FILES['video']) && isset($_FILES['audio'])) {
        $bio = trim($_POST['bio']);
        $profilePicture = $_FILES['picture'];
        $profileDocuments = $_FILES['profile_documents'];
        $videos = $_FILES['video'];
        $audio = $_FILES['audio'];

        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userExists = $stmt->fetchColumn();

        if (!$userExists) {
            $response['message'] = "Invalid user ID.";
            echo json_encode($response);
            exit;
        }

        if (empty($bio)) {
            $response['message'] = "Bio cannot be empty.";
        } elseif ($profilePicture['error'] === UPLOAD_ERR_NO_FILE) {
            $response['message'] = "No profile picture chosen. Please select a profile picture.";
        } elseif ($profilePicture['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = "Profile picture upload error: " . $profilePicture['error'];
        } elseif (!str_starts_with(mime_content_type($profilePicture['tmp_name']), 'image/')) {
            $response['message'] = "Invalid profile picture format. Only image files are allowed.";
        } elseif (empty($profileDocuments['name'][0])) {
            $response['message'] = 'No document chosen. Please select at least one document.';
        } elseif (empty($videos['name'][0])) {
            $response['message'] = 'No video chosen. Please select at least one video.';
        } elseif (empty($audio['name'][0])) {
            $response['message'] = 'No audio chosen. Please select at least one audio.';
        } else {
            $uploads_dir = "../routes/uploads/";
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }

            $target_dir = $uploads_dir . "profile_pictures/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . basename($profilePicture["name"]);
            if (move_uploaded_file($profilePicture["tmp_name"], $target_file)) {
                $documentPaths = [];
                $allowedExtensions = ['doc', 'docx', 'pdf'];

                $documents_dir = $uploads_dir . "profile_documents/";
                if (!is_dir($documents_dir)) {
                    mkdir($documents_dir, 0777, true);
                }

                for ($i = 0; $i < count($profileDocuments['name']); $i++) {
                    $documentName = $profileDocuments['name'][$i];
                    $documentTmpName = $profileDocuments['tmp_name'][$i];
                    $documentError = $profileDocuments['error'][$i];
                    $documentExtension = strtolower(pathinfo($documentName, PATHINFO_EXTENSION));

                    if ($documentError === UPLOAD_ERR_NO_FILE) {
                        continue;
                    } elseif ($documentError !== UPLOAD_ERR_OK) {
                        $response['message'] = 'Error uploading document: ' . $documentError;
                        echo json_encode($response);
                        exit;
                    } elseif (!in_array($documentExtension, $allowedExtensions)) {
                        $response['message'] = 'Invalid document format. Only DOC, DOCX, and PDF files are allowed.';
                        echo json_encode($response);
                        exit;
                    } else {
                        $documentPath = $documents_dir . basename($documentName);
                        if (move_uploaded_file($documentTmpName, $documentPath)) {
                            $documentPaths[] = $documentPath;
                        } else {
                            $response['message'] = 'Failed to move uploaded document.';
                            echo json_encode($response);
                            exit;
                        }
                    }
                }

                $video_dir = $uploads_dir . "profile_videos/";
                if (!is_dir($video_dir)) {
                    mkdir($video_dir, 0777, true);
                }
                $videoPaths = [];
                if (is_array($videos['name'])) {
                    for ($i = 0; $i < count($videos['name']); $i++) {
                        $videoName = $videos['name'][$i];
                        $videoTmpName = $videos['tmp_name'][$i];
                        $videoError = $videos['error'][$i];
                        $videoMimeType = mime_content_type($videoTmpName);

                        if ($videoError === UPLOAD_ERR_NO_FILE) {
                            continue;
                        } elseif ($videoError !== UPLOAD_ERR_OK) {
                            $response['message'] = 'Error uploading video: ' . $videoError;
                            echo json_encode($response);
                            exit;
                        } elseif (!str_starts_with($videoMimeType, 'video/')) {
                            $response['message'] = 'Invalid video format. ';
                            echo json_encode($response);
                            exit;
                        } else {
                            $videoPath = $video_dir . basename($videoName);
                            if (move_uploaded_file($videoTmpName, $videoPath)) {
                                $videoPaths[] = $videoPath;
                            } else {
                                $response['message'] = 'Failed to move uploaded video.';
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                } else {
                    $videoName = $videos['name'];
                    $videoTmpName = $videos['tmp_name'];
                    $videoError = $videos['error'];
                    $videoMimeType = mime_content_type($videoTmpName);

                    if ($videoError !== UPLOAD_ERR_OK) {
                        $response['message'] = 'Error uploading video: ' . $videoError;
                        echo json_encode($response);
                        exit;
                    } elseif (!str_starts_with($videoMimeType, 'video/')) {
                        $response['message'] = 'Invalid video format. ';
                        echo json_encode($response);
                        exit;
                    } else {
                        $videoPath = $video_dir . basename($videoName);
                        if (move_uploaded_file($videoTmpName, $videoPath)) {
                            $videoPaths[] = $videoPath;
                        } else {
                            $response['message'] = 'Failed to move uploaded video.';
                            echo json_encode($response);
                            exit;
                        }
                    }
                }

                $audio_dir = $uploads_dir . "profile_audios/";
                if (!is_dir($audio_dir)) {
                    mkdir($audio_dir, 0777, true);
                }
                $audioPaths = [];
                if (is_array($audio['name'])) {
                    for ($i = 0; $i < count($audio['name']); $i++) {
                        $audioName = $audio['name'][$i];
                        $audioTmpName = $audio['tmp_name'][$i];
                        $audioError = $audio['error'][$i];
                        $audioMimeType = mime_content_type($audioTmpName);

                        if ($audioError === UPLOAD_ERR_NO_FILE) {
                            continue;
                        } elseif ($audioError !== UPLOAD_ERR_OK) {
                            $response['message'] = 'Error uploading audio: ' . $audioError;
                            echo json_encode($response);
                            exit;
                        } elseif (!str_starts_with($audioMimeType, 'audio/')) {
                            $response['message'] = 'Invalid audio format. ';
                            echo json_encode($response);
                            exit;
                        } else {
                            $audioFile = $audio_dir . basename($audioName);
                            if (move_uploaded_file($audioTmpName, $audioFile)) {
                                $audioPaths[] = $audioFile;
                            } else {
                                $response['message'] = "Failed to move uploaded audio.";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                } else {
                    $audioName = $audio['name'];
                    $audioTmpName = $audio['tmp_name'];
                    $audioError = $audio['error'];
                    $audioMimeType = mime_content_type($audioTmpName);

                    if ($audioError !== UPLOAD_ERR_OK) {
                        $response['message'] = 'Error uploading audio: ' . $audioError;
                        echo json_encode($response);
                        exit;
                    } elseif (!str_starts_with($audioMimeType, 'audio/')) {
                        $response['message'] = 'Invalid audio format. ';
                        echo json_encode($response);
                        exit;
                    } else {
                        $audioFile = $audio_dir . basename($audioName);
                        if (move_uploaded_file($audioTmpName, $audioFile)) {
                            $audioPaths[] = $audioFile;
                        } else {
                            $response['message'] = "Failed to move uploaded audio.";
                            echo json_encode($response);
                            exit;
                        }
                    }
                }

                $documentPathsJson = json_encode($documentPaths);
                $videoPathsJson = json_encode($videoPaths);
                $audioPathsJson = json_encode($audioPaths);

                $sql = "INSERT INTO celebrity_profiles (user_id, profile_picture, description, profile_documents, video, audio, created_at)
               VALUES (:user_id, :profile_picture, :description, :profile_documents, :video, :audio, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':profile_picture', $target_file);
                $stmt->bindParam(':description', $bio);
                $stmt->bindParam(':profile_documents', $documentPathsJson);
                $stmt->bindParam(':video', $videoPathsJson);
                $stmt->bindParam(':audio', $audioPathsJson);

                if ($stmt->execute()) {
                    $profileId = $pdo->lastInsertId();
                    $response['status'] = 'success';
                    $response['message'] = "Profile created successfully.";
                    $response['profileId'] = $profileId;
                } else {
                    $response['message'] = "Failed to create profile.";
                }
            } else {
                $response['message'] = "Sorry, there was an error uploading your profile picture.";
            }
        }
    } else {
        $response['message'] = "Profile picture, bio, documents, videos, and audio are required.";
    }
}

echo json_encode($response);
