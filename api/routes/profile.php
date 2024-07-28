<?php
header('Content-Type: application/json');

require '../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if (isset($_GET['id'])) {
    $profileId = intval($_GET['id']);

    $stmt = $pdo->prepare("SELECT * FROM celebrity_profiles WHERE id = :id");
    $stmt->execute(['id' => $profileId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        echo json_encode(['status' => 'success', 'profile' => $profile]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Profile not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
