<?php
/**
 * Repository API - Version check + manifest
 * GET /repository.php?channel=stable&version=1.0.0
 * GET /repository.php?action=manifest&version=1.0.0
 */
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
header('Content-Type: application/json; charset=utf-8');

$channel = $_GET['channel'] ?? 'stable';
$action = $_GET['action'] ?? 'check';
$current_version = $_GET['version'] ?? '';
$hwid = $_GET['hwid'] ?? '';

// Get launcher settings
$settings = getLauncherSettings($pdo);

$maintenance = ($settings['maintenance_mode'] ?? '0') === '1';
$bypass_roles = array_map('trim', explode(',', $settings['maintenance_bypass_roles'] ?? 'admin'));

if ($maintenance && !empty($hwid)) {
    $user = null;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE hwid = ?");
    $stmt->execute([$hwid]);
    $user = $stmt->fetch();
    if (!$user || !in_array($user['role'], $bypass_roles)) {
        echo json_encode([
            'status' => 'maintenance',
            'message' => $settings['maintenance_message'] ?? 'Launcher is under maintenance.',
        ]);
        exit;
    }
}

// Get latest version
$latest = null;
try {
    $stmt = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $latest = $stmt->fetch();
} catch (PDOException $e) {}

if (!$latest) {
    echo json_encode(['status' => 'error', 'message' => 'No versions available']);
    exit;
}

// Check if update needed
$update_available = false;
if ($current_version && $latest['version'] !== $current_version) {
    $update_available = true;
}

if ($action === 'manifest') {
    // Full manifest for download
    $manifest = [
        'version' => $latest['version'],
        'install_dir' => $latest['install_dir'] ?? 'PouchCrack',
        'files' => [
            'game.jar' => [
                'url' => $latest['url_game_jar'] ?? '',
                'hash' => $latest['hash_game_jar'] ?? '',
                'size' => 0,
            ],
            'libs' => [
                'url' => $latest['url_libs'] ?? '',
                'hash' => $latest['hash_libs'] ?? '',
                'size' => 0,
            ],
            'assets' => [
                'url' => $latest['url_assets'] ?? '',
                'hash' => $latest['hash_assets'] ?? '',
                'size' => 0,
            ],
            'natives' => [
                'url' => $latest['url_natives'] ?? '',
                'hash' => $latest['hash_natives'] ?? '',
                'size' => 0,
            ],
            'java' => [
                'url' => $settings['java_download_url'] ?? '',
                'hash' => $settings['java_download_hash'] ?? '',
                'size' => 0,
            ],
            'crypto_key' => $latest['crypto_key'] ?? '',
        ],
        'java_url' => $settings['java_download_url'] ?? '',
        'java_hash' => $settings['java_download_hash'] ?? '',
        'global_assets_url' => $settings['global_assets_url'] ?? '',
        'global_assets_hash' => $settings['global_assets_hash'] ?? '',
    ];
    echo json_encode($manifest, JSON_PRETTY_PRINT);
    exit;
}

// Default: version check
$response = [
    'status' => 'ok',
    'update_available' => $update_available,
    'latest_version' => $latest['version'],
    'current_version' => $current_version,
    'download_url' => $settings['launcher_download_url'] ?? '',
    'update_url' => $latest['update_url'] ?? '',
    'update_message' => $latest['update_msg'] ?? '',
    'build_min' => (int)($latest['launcher_build_min'] ?? 0),
];

echo json_encode($response, JSON_PRETTY_PRINT);
