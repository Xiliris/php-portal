<?php
require __DIR__ . '/../../config.php'; // Ensure the path to config.php is correct

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data for each social platform
    $telegramLink = isset($_POST['telegram']) ? trim($_POST['telegram']) : '';
    $twitterLink = isset($_POST['twitter']) ? trim($_POST['twitter']) : '';
    $facebookLink = isset($_POST['facebook']) ? trim($_POST['facebook']) : '';
    $instagramLink = isset($_POST['instagram']) ? trim($_POST['instagram']) : '';

    // Validate at least one link
    if (empty($telegramLink) && empty($twitterLink) && empty($facebookLink) && empty($instagramLink)) {
        $response['message'] = 'At least one social media link is required.';
    } else {
        try {
            // Start a transaction
            $pdo->beginTransaction();

            // Prepare a statement to insert or update each social media link
            $stmt = $pdo->prepare("INSERT INTO socials (name, link) 
                VALUES (:name, :link)
                ON DUPLICATE KEY UPDATE 
                    link = VALUES(link)");

            // Bind and execute for Telegram
            if (!empty($telegramLink)) {
                $stmt->bindValue(':name', 'telegram');
                $stmt->bindValue(':link', $telegramLink);
                $stmt->execute();
            }

            // Bind and execute for Twitter
            if (!empty($twitterLink)) {
                $stmt->bindValue(':name', 'twitter');
                $stmt->bindValue(':link', $twitterLink);
                $stmt->execute();
            }

            // Bind and execute for Facebook
            if (!empty($facebookLink)) {
                $stmt->bindValue(':name', 'facebook');
                $stmt->bindValue(':link', $facebookLink);
                $stmt->execute();
            }

            // Bind and execute for Instagram
            if (!empty($instagramLink)) {
                $stmt->bindValue(':name', 'instagram');
                $stmt->bindValue(':link', $instagramLink);
                $stmt->execute();
            }

            // Commit the transaction
            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Social media links updated successfully.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

echo json_encode($response);
