<?php
require __DIR__ . '/../../config.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $socialNetwork1Link = isset($_POST['social-network-1']) ? trim($_POST['social-network-1']) : '';
    $socialNetwork2Link = isset($_POST['social-network-2']) ? trim($_POST['social-network-2']) : '';
    $socialNetwork3Link = isset($_POST['social-network-3']) ? trim($_POST['social-network-3']) : '';
    $socialNetwork4Link = isset($_POST['social-network-4']) ? trim($_POST['social-network-4']) : '';

    if (empty($socialNetwork1Link) && empty($socialNetwork2Link) && empty($socialNetwork3Link) && empty($socialNetwork4Link)) {
        $response['message'] = 'At least one social media link is required.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO socials (name, link, svg_path) 
                VALUES (:name, :link, :svg_path)
                ON DUPLICATE KEY UPDATE 
                    link = VALUES(link),
                    svg_path = IF(VALUES(svg_path) IS NOT NULL, VALUES(svg_path), svg_path)");

            function uploadSvg($fieldName, $targetDir)
            {
                if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
                    $fileType = mime_content_type($_FILES[$fieldName]['tmp_name']);
                    if ($fileType !== 'image/svg+xml') {
                        throw new Exception('Invalid file type. Only SVG files are allowed.');
                    }

                    $filename = basename($_FILES[$fieldName]['name']);
                    $targetFilePath = $targetDir . '/' . $filename;

                    if (!is_dir($targetDir)) {
                        if (!mkdir($targetDir, 0755, true)) {
                            throw new Exception('Failed to create directories.');
                        }
                    }

                    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetFilePath)) {
                        return '/api/storage/socials/' . $filename;
                    } else {
                        throw new Exception('Failed to move uploaded file.');
                    }
                }
                return null;
            }

            $svgDir = __DIR__ . '/../../storage/socials';

            $svgPath1 = uploadSvg('social-network-1-svg', $svgDir);
            $svgPath2 = uploadSvg('social-network-2-svg', $svgDir);
            $svgPath3 = uploadSvg('social-network-3-svg', $svgDir);
            $svgPath4 = uploadSvg('social-network-4-svg', $svgDir);

            $stmt->execute(['name' => 'social-network-1', 'link' => $socialNetwork1Link, 'svg_path' => $svgPath1]);
            $stmt->execute(['name' => 'social-network-2', 'link' => $socialNetwork2Link, 'svg_path' => $svgPath2]);
            $stmt->execute(['name' => 'social-network-3', 'link' => $socialNetwork3Link, 'svg_path' => $svgPath3]);
            $stmt->execute(['name' => 'social-network-4', 'link' => $socialNetwork4Link, 'svg_path' => $svgPath4]);

            $pdo->commit();

            $response['success'] = true;
            $response['message'] = 'Social media links updated successfully.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
