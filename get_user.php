<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
header('Content-Type: application/json');
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo json_encode(['ok' => true, 'login' => $user['username'], 'email' => $user['email']]);
        exit;
    }
}
echo json_encode(['ok' => false, 'login' => '']);
