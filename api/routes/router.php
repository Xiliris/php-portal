<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $data = array(
        "message" => "GET request received",
        "status" => 200
    ); 
    echo json_encode($data);   
};
?>