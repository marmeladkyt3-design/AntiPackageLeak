<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'rate_limit.php';

checkRateLimit($pdo, 'log_download', 30, 60);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$login = trim($input['login'] ?? '');
$hwid = trim($input['hwid'] ?? '');
$file_type = trim($input['file_type'] ?? '');
$file_size = (int)($input['file_size'] ?? 0);
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (empty($login) || empty($file_type)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'login and file_type required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO download_logs (login, hwid, file_type, file_size, ip) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$login, $hwid, $file_type, $file_size, $ip]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'DB error']);
}
?>
