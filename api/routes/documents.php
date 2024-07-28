<?php
require_once '../config.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

$upload_dir = 'uploads/profile_documents/';

function read_file_docx_as_html($filename) {
    if (!file_exists($filename)) {
        error_log("File not found: $filename");
        return false;
    }

    try {
        $phpWord = IOFactory::load($filename, 'Word2007');
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
        ob_start();
        $htmlWriter->save('php://output');
        $htmlContent = ob_get_clean();

        return $htmlContent;
    } catch (Exception $e) {
        error_log("Error reading DOCX as HTML: " . $e->getMessage());
        return false;
    }
}

if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $action = isset($_GET['action']) ? $_GET['action'] : 'download';
    $full_path = $upload_dir . $file;

    if (file_exists($full_path)) {
        $fileType = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));

        if ($action === 'view') {
            if ($fileType === 'pdf') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($full_path) . '"');
                readfile($full_path);
            } elseif ($fileType === 'docx') {
                header('Content-Type: text/html; charset=utf-8');
                $content = read_file_docx_as_html($full_path);
                if ($content !== false) {
                    echo $content;
                } else {
                    http_response_code(500);
                    echo 'Couldn\'t read the DOCX file. Please check that file.';
                }
            } elseif ($fileType === 'doc') {
                http_response_code(415);
                echo 'Preview not supported for this DOC file type.';
            } else {
                http_response_code(415);
                echo 'Preview not supported for this file type.';
            }
        } elseif ($action === 'download') {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($full_path));
            readfile($full_path);
            exit;
        }
    } else {
        http_response_code(404);
        echo 'File not found.';
    }
    exit;
} elseif (isset($_GET['id'])) {
    $profileId = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("SELECT profile_documents FROM celebrity_profiles WHERE id = :id");
        $stmt->execute(['id' => $profileId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $profileDocumentsJson = $row['profile_documents'];
            error_log("profile_documents JSON: $profileDocumentsJson");

            $profileDocuments = json_decode($profileDocumentsJson, true);
            error_log("Decoded profile_documents: " . print_r($profileDocuments, true));

            if ($profileDocuments) {
                $documents = [];

                foreach ($profileDocuments as $document) {
                    $fileType = strtolower(pathinfo($document, PATHINFO_EXTENSION));
                    $documents[] = [
                        'name' => basename($document),
                        'type' => $fileType
                    ];
                }

                header('Content-Type: application/json');
                echo json_encode($documents);
            } else {
                echo json_encode([]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Database query error: ' . $e->getMessage();
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid request';
}
?>
