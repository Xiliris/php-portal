<?php
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Unknown error'];

require '../config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['picture']) && isset($_POST['bio']) && isset($_FILES['profile_documents'])) {
        $bio = trim($_POST['bio']);
        $profilePicture = $_FILES['picture'];
        $profileDocuments = $_FILES['profile_documents'];

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
                        $response['message'] = 'No document chosen. Please select a document.';
                        echo json_encode($response);
                        exit;
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

                $documentPathsJson = json_encode($documentPaths);
                $sql = "INSERT INTO celebrity_profiles (user_id, profile_picture, description, profile_documents, created_at) VALUES (:user_id, :profile_picture, :description, :profile_documents, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':profile_picture', $target_file);
                $stmt->bindParam(':description', $bio);
                $stmt->bindParam(':profile_documents', $documentPathsJson);

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
        $response['message'] = "Profile picture, bio, and documents are required.";
    }
}

echo json_encode($response);
?>
