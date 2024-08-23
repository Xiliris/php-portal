<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../../vendor/autoload.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $price = $_POST['price'];
    $number = trim($_POST['number']);

    if (empty($name) || empty($description) || empty($price) || empty($number)) {
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    if (!is_numeric($price)) {
        $response['message'] = "Price must be numeric.";
        echo json_encode($response);
        exit;
    }
    if (!is_numeric($number) || strlen($number) > 11) {
        $response['message'] = "Product Number must be a numeric value with up to 11 digits.";
        echo json_encode($response);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM shop WHERE number = :number");
    $stmt->bindParam(':number', $number, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $response['message'] = "Product number already exists.";
        echo json_encode($response);
        exit;
    }

    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $directory = __DIR__ . '/../../storage/shop';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024;

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileMimeType = mime_content_type($fileTmpPath);

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $response['message'] = "Invalid file type. Only JPEG, PNG, and GIF files are allowed.";
            echo json_encode($response);
            exit;
        }

        if ($fileSize > $maxFileSize) {
            $response['message'] = "File size exceeds the 2MB limit.";
            echo json_encode($response);
            exit;
        }

        $handle = new \Verot\Upload\Upload($_FILES['image']);
        if ($handle->uploaded) {
            $handle->file_new_name_body = uniqid();
            $handle->process($directory);
            if ($handle->processed) {
                $imagePath = '/api/storage/shop/' . $handle->file_dst_name;
                $handle->clean();
            } else {
                $response['message'] = "File upload failed: " . $handle->error;
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = "File upload failed: " . $handle->error;
            echo json_encode($response);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO shop (name, description, price, number, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $number, $imagePath]);

        $lastInsertId = $pdo->lastInsertId();

        $response['success'] = true;
        $response['message'] = "Product added successfully!";
        $response['data'] = [
            "id" => $lastInsertId,
            "name" => $name,
            "description" => $description,
            "price" => $price,
            "number" => $number,
            "image_path" => $imagePath
        ];
    } catch (Exception $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

echo json_encode($response);
