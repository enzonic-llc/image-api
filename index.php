<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

$allowedOrigin = getenv('CORS_ALLOW_ORIGIN') ?: '*';
header("Access-Control-Allow-Origin: " . $allowedOrigin);
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$apiKey = getenv('API_KEY');
if ($apiKey && (!isset($_SERVER['HTTP_AUTHORIZATION']) || $_SERVER['HTTP_AUTHORIZATION'] !== 'Bearer ' . $apiKey)) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized: Invalid or missing API Key."]);
    exit();
}

$imagesDir = 'images/';

if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0777, true);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        handleUpload($imagesDir);
        break;
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'list') {
            handleListImages($imagesDir);
        } else {
            handleDownload($imagesDir);
        }
        break;
    case 'DELETE':
        handleDelete($imagesDir);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

function handleListImages($imagesDir) {
    $files = array_diff(scandir($imagesDir), array('.', '..'));
    $images = [];
    foreach ($files as $file) {
        if (is_file($imagesDir . $file)) {
            $images[] = $file;
        }
    }
    http_response_code(200);
    echo json_encode(["images" => array_values($images)]);
}

function handleUpload($imagesDir) {
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $fileName = basename($file['name']);
        $targetFilePath = $imagesDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                http_response_code(200);
                echo json_encode(["message" => "Image uploaded successfully.", "fileName" => $fileName]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to upload image."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid file type. Only JPG, JPEG, PNG, GIF are allowed."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No image file provided."]);
    }
}

function handleDownload($imagesDir) {
    if (isset($_GET['name'])) {
        $fileName = basename($_GET['name']);
        $filePath = $imagesDir . $fileName;

        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Image not found."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No image name provided."]);
    }
}

function handleDelete($imagesDir) {
    parse_str(file_get_contents("php://input"), $delete_vars);
    $fileName = isset($delete_vars['name']) ? basename($delete_vars['name']) : null;

    if ($fileName) {
        $filePath = $imagesDir . $fileName;

        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                http_response_code(200);
                echo json_encode(["message" => "Image deleted successfully.", "fileName" => $fileName]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to delete image."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Image not found."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No image name provided."]);
    }
}

?>
