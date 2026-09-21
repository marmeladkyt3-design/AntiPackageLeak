<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'rate_limit.php';

checkRateLimit($pdo, 'heartbeat', 120, 60);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$login = trim($input['login'] ?? '');

if (empty($login)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'login required']);
    exit;
}

try {
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_activity'");
    if ($cols->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN `last_activity` datetime DEFAULT NULL");
    }
} catch (PDOException $e) {}

try {
    $stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'DB error']);
}
?>
