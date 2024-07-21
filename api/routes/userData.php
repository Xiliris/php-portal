<?php
    require __DIR__ . '/../config.php';

    $response = ["success" => false, "message" => ""];

    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        $ip = $_POST['ip'];
        $country = $_POST['country'];
        $isp = $_POST['isp'];

        if (empty($ip) || empty($country) || empty($isp)) {
            $response['message'] = "Please fill in all fields.";
        } else {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM userdata WHERE ip = ?");
            $checkStmt->execute([$ip]);
            $ipExists = $checkStmt->fetchColumn();

            if ($ipExists) {
                $response['message'] = "IP address already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO userdata (ip, country, isp) VALUES (?, ?, ?)");
                $stmt->execute([$ip, $country, $isp]);

                if ($stmt->rowCount() === 1) {
                    $response['success'] = true;
                    $response['message'] = "Data saved successfully!";
                } else {
                    $response['message'] = "Failed to save data. Please try again later.";
                }
            }
        }

        echo json_encode($response);
    }
?>
