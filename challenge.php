<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', sys_get_temp_dir() . '/aal_challenge_errors.log');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'not_post']);
    exit;
}

try {
    if (!defined('ANTI_SECRET')) {
        define('ANTI_SECRET', 'ca4f01f8847f72673543f8f73d77b1f7ba9d51f60fbf29fa3d769d5f9f2009d0');
    }

    $raw = @file_get_contents('php://input');
    $input = @json_decode($raw, true);
    if (!is_array($input)) {
        echo json_encode(['ok' => false, 'error' => 'bad_json', 'raw' => substr((string)$raw, 0, 100)]);
        exit;
    }

    $challenge = $input['c'] ?? '';
    $fingerprint = $input['fp'] ?? '';

    if (!is_string($challenge) || strlen($challenge) < 8 || !preg_match('/^[0-9a-f]+$/', $challenge)) {
        echo json_encode(['ok' => false, 'error' => 'bad_challenge']);
        exit;
    }
    if (!is_string($fingerprint) || strlen($fingerprint) < 5) {
        echo json_encode(['ok' => false, 'error' => 'bad_fp']);
        exit;
    }

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $exp = time() + 7200;
    $nonce = bin2hex(random_bytes(16));
    $body = $exp . '|' . $nonce;
    $sig = hash_hmac('sha256', $body, ANTI_SECRET);
    $token = rtrim(strtr(base64_encode($body), '+/', '-_'), '=') . '.' . $sig;

    setcookie('anti_v', $token, [
        'expires'  => $exp,
        'path'     => '/',
        'secure'   => $is_https,
        'httponly'  => false,
        'samesite' => 'Lax',
    ]);

    echo json_encode(['ok' => true]);

} catch (\Throwable $e) {
    error_log('CHALLENGE_ERR: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
