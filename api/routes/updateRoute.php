<?php
require __DIR__ . '/../config.php';


$response = ["success" => false, "message" => ""];

if($_SERVER["REQUEST_METHOD"] == 'POST') {

    $route = $_POST['route'];
    $changedRoute = $_POST['changedRoute'];

    if(empty($route) || empty($changedRoute)) {
        $response['message'] = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("UPDATE routes SET changedRoute = ? WHERE route = ?");
        $stmt->execute([$changedRoute, $route]);

        if($stmt->rowCount() === 1) {
            $response['success'] = true;
            $response['message'] = "Route updated successfully!";
        } else {
            $response['message'] = "Failed to update route. Please try again later.";
        }
    }

    echo json_encode($response);
}
?>