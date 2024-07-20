<?php
require __DIR__ . '/../config.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    $response['success'] = true;
    $response['message'] = "Logged out successfully!";
    echo json_encode($response);
}

?>