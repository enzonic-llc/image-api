<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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

$clientPassword = getenv('CLIENT_PASSWORD');
$apiKey = getenv('API_KEY');

$input = json_decode(file_get_contents('php://input'), true);

$enteredPassword = $input['password'] ?? '';

if ($enteredPassword === $clientPassword) {
    echo json_encode(["success" => true, "message" => "Authentication successful.", "apiKey" => $apiKey]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid password."]);
}

?>
