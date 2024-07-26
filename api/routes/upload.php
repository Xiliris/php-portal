<?php
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST request received');
    error_log(print_r($_POST, true));
    error_log(print_r($_FILES, true));

    if (isset($_FILES['file'])) {
        if ($_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
            $response['message'] = "No file chosen. Please select a file to upload.";
        } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = "File upload error: " . $_FILES['file']['error'];
        } else {
            if (isset($_POST['submit'])) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["file"]["name"]);
                $uploadOk = 1;
                $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                if (isset($_FILES["file"]["tmp_name"]) && filesize($_FILES["file"]["tmp_name"]) !== false) {
                    if ($fileType != "doc" && $fileType != "docx" && $fileType != "pdf") {
                        $response['message'] = "Sorry, only DOC, DOCX, & PDF files are allowed.";
                        $uploadOk = 0;
                    }
                } else {
                    $response['message'] = "File is not a valid document.";
                    $uploadOk = 0;
                }
                if ($uploadOk == 0) {
                    $response['message'] = $response['message'] ?: "Sorry, your file was not uploaded.";
                } else {
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                        $response['status'] = 'success';
                        $response['message'] = "The file " . htmlspecialchars(basename($_FILES["file"]["name"])) . " has been uploaded.";
                    } else {
                        $response['message'] = "Sorry, there was an error uploading your file.";
                    }
                }
            } else {
                $response['message'] = "Submit button not set.";
            }
        }
    } else {
        $response['message'] = "No file input field found.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $dir = "uploads/";
    $documents = [];

    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    $fileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $documents[] = [
                        'name' => $file,
                        'type' => $fileType
                    ];
                }
            }
            closedir($dh);
        }
    }

    echo json_encode($documents);
}

echo json_encode($response);
