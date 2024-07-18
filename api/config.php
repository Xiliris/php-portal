<?php
// Database connection details
$host = 'localhost';
$dbname = 'php-portal';
$username = 'php-portal-user';
$password = '7r4dd!6P)0zvOLej';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>