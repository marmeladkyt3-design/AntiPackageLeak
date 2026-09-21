<?php
/**
 * Download Router - serves launcher files
 * Route: /dl/{filename}
 * Examples: /dl/game.jar, /dl/java.zip, /dl/assets.zip
 */
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';

// Get settings
$settings = getLauncherSettings($pdo);

// Map of allowed files to their URLs
$file_map = [];

// Get URLs from launcher_versions
try {
    $stmt = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $v = $stmt->fetch();
    if ($v) {
        $file_map['game.jar'] = $v['url_game_jar'] ?? '';
        $file_map['libs.zip'] = $v['url_libs'] ?? '';
        $file_map['assets.zip'] = $v['url_assets'] ?? '';
        $file_map['natives.zip'] = $v['url_natives'] ?? '';
    }
} catch (PDOException $e) {}

// Java and runtime from settings
$file_map['java.zip'] = $settings['java_download_url'] ?? '';
$file_map['AntiPackageLeakvmruntime.zip'] = $settings['java_download_url'] ?? ''; // Alias

// Get filename from request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
$parts = explode('/', $uri);
$filename = end($parts);

// Security: prevent path traversal
$filename = basename($filename);

if (empty($filename)) {
    http_response_code(400);
    echo json_encode(['error' => 'No file specified']);
    exit;
}

// Check if file exists in map
if (!isset($file_map[$filename]) || empty($file_map[$filename])) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found', 'available' => array_keys(array_filter($file_map))]);
    exit;
}

$download_url = $file_map[$filename];

// Log the download
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$hwid = $_GET['hwid'] ?? '';
$key = $_GET['key'] ?? '';

try {
    $stmt = $pdo->prepare("INSERT INTO download_logs (filename, ip, hwid, key_code, downloaded_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$filename, $ip, $hwid, $key]);
} catch (PDOException $e) {}

// Redirect to actual download URL
header('Location: ' . $download_url, true, 302);
exit;
