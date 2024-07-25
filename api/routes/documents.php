<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

$upload_dir = 'uploads/';
$media_dir = 'uploads/media/';

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
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            readfile($full_path);
        }
        exit;
    } else {
        http_response_code(404);
        echo "File not found.";
        exit;
    }
}

$documents = [];
if (is_dir($upload_dir)) {
    if ($dh = opendir($upload_dir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != "." && $file != "..") {
                $fileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $documents[] = [
                    'name' => $file,
                    'type' => $fileType
                ];
            }
        }
        closedir($dh);
    }
}

header('Content-Type: application/json');
if (empty($documents)) {
    echo json_encode(['message' => 'No documents uploaded.']);
} else {
    echo json_encode($documents);
}
?>
