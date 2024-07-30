<?php
    require __DIR__ . '/../../config.php';

    $response = ["success" => false, "message" => ""];

    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        $username = $_POST['username'];
        $role = $_POST['role'];
        $ip = $_POST['ip'];
        $country = $_POST['country'];
        $isp = $_POST['isp'];

        if (empty($username) || empty($role) || empty($ip) || empty($country) || empty($isp)) {
            $response['message'] = "Please fill in all fields.";
        } else {
            $checkIpStmt = $pdo->prepare("SELECT COUNT(*) FROM userdata WHERE ip = ?");
            $checkIpStmt->execute([$ip]);
            $ipExists = $checkIpStmt->fetchColumn();

            $checkUsernameStmt = $pdo->prepare("SELECT COUNT(*) FROM userdata WHERE username = ?");
            $checkUsernameStmt->execute([$username]);
            $usernameExists = $checkUsernameStmt->fetchColumn();

            if ($ipExists) {
                $response['message'] = "IP address already exists.";
            } elseif ($usernameExists) {
                $response['message'] = "Username already exists.";
            } else {
                // Insert the new data
                $stmt = $pdo->prepare("INSERT INTO userdata (ip, country, isp, username, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$ip, $country, $isp, $username, $role]);

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
