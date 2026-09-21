<?php
function checkRateLimit($pdo, $endpoint, $maxHits = 60, $windowSeconds = 60, $login = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $login ? $ip . ':' . $login : $ip;
    
    try {
        $stmt = $pdo->prepare("SELECT id, hits, window_start FROM rate_limits WHERE ip = ? AND endpoint = ?");
        $stmt->execute([$key, $endpoint]);
        $row = $stmt->fetch();
        
        if ($row) {
            $elapsed = time() - strtotime($row['window_start']);
            if ($elapsed < $windowSeconds) {
                if ($row['hits'] >= $maxHits) {
                    http_response_code(429);
                    echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
                    exit;
                }
                $pdo->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE id = ?")->execute([$row['id']]);
            } else {
                $pdo->prepare("UPDATE rate_limits SET hits = 1, window_start = NOW() WHERE id = ?")->execute([$row['id']]);
            }
        } else {
            $pdo->prepare("INSERT INTO rate_limits (ip, endpoint, hits, window_start) VALUES (?, ?, 1, NOW())")->execute([$key, $endpoint]);
        }
    } catch (PDOException $e) {
        // fail open
    }
}

function checkLoginRateLimit($pdo, $login, $maxHits = 10, $windowSeconds = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return checkRateLimit($pdo, 'login:' . $login, $maxHits, $windowSeconds, $login);
}

function checkHwidRateLimit($pdo, $login, $maxHits = 5, $windowSeconds = 300) {
    return checkRateLimit($pdo, 'hwid:' . $login, $maxHits, $windowSeconds, $login);
}
?>