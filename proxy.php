<?php
header('Content-Type: application/json; charset=utf-8');

$p = $_GET['p'] ?? '';

if ($p === 'api/login') {
    $raw = @file_get_contents('php://input');
    $input = @json_decode($raw, true);
    if (!is_array($input)) {
        echo json_encode(['status' => 'error', 'error' => 'bad_json']);
        exit;
    }
    $login   = trim($input['login']   ?? '');
    $password = trim($input['password'] ?? '');
    $hwid    = trim($input['hwid']    ?? '');
    if ($login === '' || $password === '' || $hwid === '') {
        echo json_encode(['status' => 'error', 'error' => 'missing_fields']);
        exit;
    }
    try {
        $pdo = new PDO("mysql:host=sql309.infinityfree.com;dbname=if0_42944847_AntiPackageLeak;charset=utf8mb4",
            'if0_42944847', 'c92WWhdVGc', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $stmt = $pdo->prepare("SELECT id, username, password, hwid, banned, uuid FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'error' => 'db_error']);
        exit;
    }
    if (!$user) {
        echo json_encode(['status' => 'error', 'error' => 'invalid_credentials']);
        exit;
    }
    if (!empty($user['banned'])) {
        echo json_encode(['status' => 'error', 'error' => 'banned']);
        exit;
    }
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'error', 'error' => 'invalid_credentials']);
        exit;
    }
    if (!empty($user['hwid']) && $user['hwid'] !== $hwid) {
        echo json_encode(['status' => 'error', 'error' => 'hwid_mismatch']);
        exit;
    }
    if (empty($user['hwid'])) {
        $pdo->prepare("UPDATE users SET hwid = ? WHERE id = ?")->execute([$hwid, $user['id']]);
    }
    $uuid = $user['uuid'] ?? '';
    if ($uuid === '') {
        $uuid = bin2hex(random_bytes(16));
        $pdo->prepare("UPDATE users SET uuid = ? WHERE id = ?")->execute([$uuid, $user['id']]);
    }
    $crypto_key = '';
    try {
        $ver = $pdo->query("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        if ($ver && !empty($ver['crypto_key'])) $crypto_key = $ver['crypto_key'];
    } catch (PDOException $e) {}
    try {
        $pdo->prepare("INSERT INTO launcher_stats (user_id, hwid, ip, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$user['id'], $hwid, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (PDOException $e) {}
    echo json_encode([
        'status' => 'ok',
        'token' => bin2hex(random_bytes(32)),
        'user' => ['id' => (string)$user['id'], 'uuid' => $uuid],
        'crypto_key' => $crypto_key,
    ]);
    exit;
}

if ($p === 'api/settings') {
    $token = '';
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $h, $m)) $token = $m[1];
    try {
        $pdo = new PDO("mysql:host=sql309.infinityfree.com;dbname=if0_42944847_AntiPackageLeak;charset=utf8mb4",
            'if0_42944847', 'c92WWhdVGc', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $settings = [];
        $rows = $pdo->query("SELECT setting_key, setting_value FROM launcher_settings")->fetchAll();
        foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
        $ver = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        $crypto_key = '';
        $ck = $pdo->query("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        if ($ck && !empty($ck['crypto_key'])) $crypto_key = $ck['crypto_key'];
    } catch (PDOException $e) { $settings = []; $ver = null; }
    echo json_encode([
        'site_name' => $settings['site_name'] ?? 'AntiPackageLeak',
        'site_url' => $settings['site_url'] ?? 'https://antiaileaks.ct.ws',
        'version' => $ver ? ($ver['version'] ?? '1.0.0') : '1.0.0',
        'url_game_jar' => $ver ? ($ver['url_game_jar'] ?? '') : '',
        'url_libs' => $ver ? ($ver['url_libs'] ?? '') : '',
        'url_assets' => $settings['global_assets_url'] ?? ($ver ? ($ver['url_assets'] ?? '') : ''),
        'url_java' => $settings['java_download_url'] ?? ($ver ? ($ver['url_java'] ?? '') : ''),
        'url_natives' => $ver ? ($ver['url_natives'] ?? '') : '',
        'url_fake_jar' => $ver ? ($ver['url_fake_jar'] ?? '') : '',
        'install_dir' => $ver ? ($ver['install_dir'] ?? 'AntiPackageLeak') : 'AntiPackageLeak',
        'hash_game_jar' => $ver ? ($ver['hash_game_jar'] ?? '') : '',
        'hash_libs' => $ver ? ($ver['hash_libs'] ?? '') : '',
        'hash_assets' => $settings['global_assets_hash'] ?? ($ver ? ($ver['hash_assets'] ?? '') : ''),
        'hash_java' => $settings['java_download_hash'] ?? ($ver ? ($ver['hash_java'] ?? '') : ''),
        'hash_natives' => $ver ? ($ver['hash_natives'] ?? '') : '',
        'hash_fake_jar' => $ver ? ($ver['hash_fake_jar'] ?? '') : '',
        'crypto_key' => $crypto_key,
    ]);
    exit;
}

if ($p === 'api/config') {
    $build = isset($_GET['build']) ? (int)$_GET['build'] : 0;
    try {
        $pdo = new PDO("mysql:host=sql309.infinityfree.com;dbname=if0_42944847_AntiPackageLeak;charset=utf8mb4",
            'if0_42944847', 'c92WWhdVGc', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $settings = [];
        $rows = $pdo->query("SELECT setting_key, setting_value FROM launcher_settings")->fetchAll();
        foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
        if ($build > 0) {
            $ver = $pdo->prepare("SELECT * FROM launcher_versions WHERE is_active = 1 AND launcher_build_min <= ? ORDER BY id DESC LIMIT 1");
            $ver->execute([$build]);
            $ver = $ver->fetch();
        } else {
            $ver = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        }
        $crypto_key = '';
        if ($build > 0) {
            $ck = $pdo->prepare("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 AND launcher_build_min <= ? ORDER BY id DESC LIMIT 1");
            $ck->execute([$build]);
            $ck = $ck->fetch();
        } else {
            $ck = $pdo->query("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        }
        if ($ck && !empty($ck['crypto_key'])) $crypto_key = $ck['crypto_key'];
    } catch (PDOException $e) { $ver = null; $settings = []; }
    echo json_encode([
        'site_name' => $settings['site_name'] ?? 'AntiPackageLeak',
        'site_url' => $settings['site_url'] ?? 'https://antiaileaks.ct.ws',
        'version' => $ver ? ($ver['version'] ?? '1.0.0') : '1.0.0',
        'url_game_jar' => $ver ? ($ver['url_game_jar'] ?? '') : '',
        'url_libs' => $ver ? ($ver['url_libs'] ?? '') : '',
        'url_assets' => $settings['global_assets_url'] ?? ($ver ? ($ver['url_assets'] ?? '') : ''),
        'url_java' => $settings['java_download_url'] ?? ($ver ? ($ver['url_java'] ?? '') : ''),
        'url_natives' => $ver ? ($ver['url_natives'] ?? '') : '',
        'url_fake_jar' => $ver ? ($ver['url_fake_jar'] ?? '') : '',
        'install_dir' => $ver ? ($ver['install_dir'] ?? 'AntiPackageLeak') : 'AntiPackageLeak',
        'hash_game_jar' => $ver ? ($ver['hash_game_jar'] ?? '') : '',
        'hash_libs' => $ver ? ($ver['hash_libs'] ?? '') : '',
        'hash_assets' => $settings['global_assets_hash'] ?? ($ver ? ($ver['hash_assets'] ?? '') : ''),
        'hash_java' => $settings['java_download_hash'] ?? ($ver ? ($ver['hash_java'] ?? '') : ''),
        'hash_natives' => $ver ? ($ver['hash_natives'] ?? '') : '',
        'hash_fake_jar' => $ver ? ($ver['hash_fake_jar'] ?? '') : '',
        'crypto_key' => $crypto_key,
    ]);
    exit;
}

if ($p === 'api/init') {
    $version = $_GET['version'] ?? '';
    $hwid = $_GET['hwid'] ?? '';
    try {
        $pdo = new PDO("mysql:host=sql309.infinityfree.com;dbname=if0_42944847_AntiPackageLeak;charset=utf8mb4",
            'if0_42944847', 'c92WWhdVGc', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $ver = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        $settings = [];
        $rows = $pdo->query("SELECT setting_key, setting_value FROM launcher_settings")->fetchAll();
        foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
        $crypto_key = '';
        $ck = $pdo->query("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        if ($ck && !empty($ck['crypto_key'])) $crypto_key = $ck['crypto_key'];
    } catch (PDOException $e) { $ver = null; $settings = []; }
    echo json_encode([
        'version' => $ver ? ($ver['version'] ?? '1.0.0') : '1.0.0',
        'url_game_jar' => $ver ? ($ver['url_game_jar'] ?? '') : '',
        'url_fake_jar' => $ver ? ($ver['url_fake_jar'] ?? '') : '',
        'url_libs' => $ver ? ($ver['url_libs'] ?? '') : '',
        'url_assets' => $settings['global_assets_url'] ?? ($ver ? ($ver['url_assets'] ?? '') : ''),
        'url_java' => $settings['java_download_url'] ?? ($ver ? ($ver['url_java'] ?? '') : ''),
        'url_natives' => $ver ? ($ver['url_natives'] ?? '') : '',
        'install_dir' => $ver ? ($ver['install_dir'] ?? 'AntiPackageLeak') : 'AntiPackageLeak',
        'hash_game_jar' => $ver ? ($ver['hash_game_jar'] ?? '') : '',
        'hash_libs' => $ver ? ($ver['hash_libs'] ?? '') : '',
        'hash_assets' => $settings['global_assets_hash'] ?? ($ver ? ($ver['hash_assets'] ?? '') : ''),
        'hash_java' => $settings['java_download_hash'] ?? ($ver ? ($ver['hash_java'] ?? '') : ''),
        'hash_natives' => $ver ? ($ver['hash_natives'] ?? '') : '',
        'hash_fake_jar' => $ver ? ($ver['hash_fake_jar'] ?? '') : '',
        'crypto_key' => $crypto_key,
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'error' => 'unknown_endpoint']);
