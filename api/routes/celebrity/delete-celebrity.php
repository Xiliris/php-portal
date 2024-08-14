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

            $stmt = $pdo->prepare("SELECT image_path FROM celebrity_profile WHERE `id` = ?");
            $stmt->execute([$id]);
            $celebrity = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($celebrity) {
                $imagePath = $celebrity['image_path'];

                $stmt = $pdo->prepare("DELETE FROM celebrity_profile WHERE `id` = ?");
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    if ($imagePath && file_exists($imagePath)) {
                        unlink($imagePath);
                    }

                    $response["success"] = true;
                    $response["message"] = "Celebrity and image deleted successfully";
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
?>
