<?php 
    require __DIR__ . '/../../config.php';

    $response = ["success" => false, "message" => "", "data" => []];

    if($_SERVER["REQUEST_METHOD"] == 'GET') {
        $stmt = $pdo->prepare("SELECT * FROM donations");
        $stmt->execute();
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($donations) {
            $response['success'] = true;
            $response['message'] = "Donations found.";
            $response['data'] = $donations;
        } else {
            $response['message'] = "Donations not found.";
        }

        echo json_encode($response);
    }
?>