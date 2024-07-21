<?php 
require __DIR__ . '/../config.php';

$resposne = ["success" => false, "message" => "", "data" => []];

if($_SERVER["REQUEST_METHOD"] == 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM routes");
    $stmt->execute();
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($routes) {
        $response['success'] = true;
        $response['message'] = "Routes found.";
        $response['data'] = $routes;
    } else {
        $response['message'] = "Routes not found.";
    }

    echo json_encode($response);
}

?>