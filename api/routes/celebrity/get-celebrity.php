<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => "", "data" => []];

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $id = $_POST['id'];


    if (empty($id)) {
        $response['message'] = "ID is required.";
    } else {
        try {

            $stmt = $pdo->prepare("SELECT * FROM celebrity_profile WHERE id = ?");
            $stmt->execute([$id]);


            $celebrity = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($celebrity) {
                $response['success'] = true;
                $response['data'] = $celebrity;
                $response['message'] = "Celebrity found successfully.";
            } else {
                $response['message'] = "No celebrity found with this ID.";
            }
        } catch (Exception $e) {
            $response['message'] = "Error fetching celebrity: " . $e->getMessage();
        }
    }


    echo json_encode($response);
}
?>
