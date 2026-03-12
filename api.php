<?php
header('Content-Type: application/json');

// Handle POST requests (enable/disable SRT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cameraIp = $_POST['camera_ip'] ?? '';
    $camKey = $_POST['cam_key'] ?? '';
    $srtIp = $_POST['srt_ip'] ?? '';
    $action = $_POST['action'] ?? '';
    $port = $_POST['port'] ?? '5000';
    $latency = (int)($_POST['latency'] ?? 500);

    if (!$cameraIp || !$camKey || !$srtIp || !in_array($action, ['enable', 'disable'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }

    $enable = ($action === 'enable') ? 1 : 0;
    $url = "http://$cameraIp/cgi-bin/web.fcgi?func=set";
    $payload = json_encode([
        'key' => (int)$camKey,
        'SRT' => [
            'mode' => 'caller',
            'main caller' => [
                'enable' => $enable,
                'ip' => $srtIp,
                'port' => (int)$port,
                'latency' => $latency,
                'streamid' => '',
                'encryption' => 0,
                'key length' => 32,
                'key' => ''
            ],
            'sub caller' => [
                'enable' => 0,
                'ip' => $srtIp,
                'port' => (int)$port,
                'latency' => $latency,
                'streamid' => '',
                'encryption' => 0,
                'key length' => 32,
                'key' => ''
            ]
        ]
    ]);

    $result = sendPostRequest($url, $payload);
    echo json_encode($result);
    exit;
}

// Handle GET requests (check status)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_status'])) {
    $cameraIp = $_GET['camera_ip'] ?? '';
    $camKey = $_GET['cam_key'] ?? '';

    if (!$cameraIp || !$camKey) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }

    $result = getStatus($cameraIp, $camKey);
    echo json_encode($result);
    exit;
}

function getStatus($cameraIp, $camKey) {
    // Use POST method with SRT=true payload
    $url = "http://$cameraIp/cgi-bin/web.fcgi?func=get";
    $payload = json_encode(['key' => (int)$camKey, 'SRT' => true]);

    // Debug logging
    error_log("Camera: $cameraIp, Key: $camKey, Payload: $payload");

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => $error, 'enabled' => null];
    }

    // Parse the response
    $data = json_decode($response, true);
    $enabled = null;

    // Check if SRT data is present and extract enable status
    if ($data && isset($data['SRT'])) {
        // Case 1: SRT is false (completely disabled)
        if ($data['SRT'] === false) {
            $enabled = false;
        }
        // Case 2: SRT is an object with configuration
        elseif (is_array($data['SRT']) && isset($data['SRT']['main caller']['enable'])) {
            $enabled = (int)$data['SRT']['main caller']['enable'] === 1;
        }
    }

    return [
        'success' => true,
        'http_code' => $httpCode,
        'response' => $response,
        'enabled' => $enabled,
        'debug' => $data,
        'sent_payload' => $payload,
        'sent_key' => (int)$camKey
    ];
}

function sendPostRequest($url, $payload) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    return [
        'success' => true,
        'http_code' => $httpCode,
        'response' => $response
    ];
}
?>
