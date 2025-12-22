<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

$dbFile = 'database.json';

if (!file_exists($dbFile)) {
    file_put_contents($dbFile, '[]');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo file_get_contents($dbFile);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid input"]);
        exit;
    }

    $currentData = json_decode(file_get_contents($dbFile), true);
    
    if ($input['action'] === 'add') {
        if (!isset($input['snippet'])) {
            http_response_code(400);
            exit;
        }
        array_unshift($currentData, $input['snippet']);
    } 
    elseif ($input['action'] === 'delete') {
        if (!isset($input['id'])) {
            http_response_code(400);
            exit;
        }
        
        $isAdmin = isset($input['user']) && $input['user'] === 'ExterLeo';
        if ($isAdmin) {
            $currentData = array_filter($currentData, function($item) use ($input) {
                return $item['id'] != $input['id'];
            });
            $currentData = array_values($currentData);
        } else {
            http_response_code(403);
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }
    }

    if (file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT))) {
        echo json_encode(["success" => true, "data" => $currentData]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Write failed"]);
    }
    exit;
}
?>
