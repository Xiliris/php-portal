<?php
require __DIR__ . '/../../config.php';

header('Content-Type: application/json');

function handleFileUpload()
{
    $data = array();

    if (isset($_FILES['upload']['name'])) {

        $filename = $_FILES['upload']['name'];

        $uploadDirectory = __DIR__ . '/../../../storage/about/';
        $location = $uploadDirectory . $filename;

        $file_extension = pathinfo($location, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);

        $valid_ext = array("jpg", "png", "jpeg");

        if (in_array($file_extension, $valid_ext)) {
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            if (move_uploaded_file($_FILES['upload']['tmp_name'], $location)) {
                $data['fileName'] = $filename;
                $data['uploaded'] = 1;
                $data['url'] = '/storage/about/' . $filename;
                return $data;
            }
        } else {
            $data['uploaded'] = 0;
            $data['error']['message'] = 'Invalid file type. Only jpg, png, and jpeg files are allowed.';
        }
    } else {
        $data['uploaded'] = 0;
        $data['error']['message'] = 'No file uploaded.';
    }

    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['title']) && isset($_POST['content'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];

        if ($title && $content) {
            $stmt = $pdo->prepare("SELECT id, title, content FROM about LIMIT 1");
            $stmt->execute();
            $existingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRecord) {
                $stmt = $pdo->prepare("UPDATE about SET title = :title, content = :content WHERE id = :id");
                $stmt->execute([':title' => $title, ':content' => $content, ':id' => $existingRecord['id']]);
                echo json_encode(['success' => true, 'message' => 'Record updated successfully.']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO about (title, content) VALUES (:title, :content)");
                $stmt->execute([':title' => $title, ':content' => $content]);
                echo json_encode(['success' => true, 'message' => 'Record created successfully.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Title and content are required.']);
        }
    } elseif (isset($_FILES['upload'])) {
        $uploadData = handleFileUpload();
        echo json_encode($uploadData);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT title, content FROM about ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No content found.']);
    }
}
