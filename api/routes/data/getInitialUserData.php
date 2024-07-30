<?php
    require __DIR__ . '/../../config.php';

    $response = ["success" => false, "message" => "", "data" => []];

    if ($_SERVER["REQUEST_METHOD"] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM userdata ORDER BY country ASC"); 
        $response['data'] = $stmt->fetchAll();
        $response['success'] = true;

        echo json_encode($response);
    }

?>