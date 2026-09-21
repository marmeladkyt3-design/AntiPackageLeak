<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');

try {
    $tmp = db();
    $cols = $tmp->query("SHOW COLUMNS FROM `users` LIKE 'avatar_url'")->fetch();
    if (!$cols) {
        $tmp->exec("ALTER TABLE `users` ADD COLUMN `avatar_url` varchar(500) DEFAULT ''");
    }
    $cols2 = $tmp->query("SHOW COLUMNS FROM `users` LIKE 'hwid'")->fetch();
    if (!$cols2) {
        $tmp->exec("ALTER TABLE `users` ADD COLUMN `hwid` varchar(128) DEFAULT ''");
    }
    $cols3 = $tmp->query("SHOW COLUMNS FROM `users` LIKE 'uuid'")->fetch();
    if (!$cols3) {
        $tmp->exec("ALTER TABLE `users` ADD COLUMN `uuid` varchar(64) DEFAULT ''");
    }
    $tmp = null;
} catch (PDOException $e) {}

$act = $_GET['act'] ?? $_GET['route'] ?? '';
if ($act === '') {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $uri = rtrim($uri, '/');
    if (preg_match('#/api/(login|info|versions|logout)$#', $uri, $m)) {
        $act = $m[1];
    }
}

function jwt_create($user_id) {
    $secret = 'launcher_jwt_secret_aial_' . md5('ca4f01f8847f72673543f8f73d77b1f7ba9d51f60fbf29fa3d769d5f9f2009d0');
    $header = base64_encode(json_encode(['typ'=>'JWT','alg'=>'HS256']));
    $payload = base64_encode(json_encode(['uid'=>$user_id,'iat'=>time(),'exp'=>time()+86400*30]));
    $sig = hash_hmac('sha256', "$header.$payload", $secret);
    return "$header.$payload.$sig";
}

function jwt_verify($token) {
    $secret = 'launcher_jwt_secret_aial_' . md5('ca4f01f8847f72673543f8f73d77b1f7ba9d51f60fbf29fa3d769d5f9f2009d0');
    $parts = explode('.', $token, 3);
    if (count($parts) !== 3) return null;
    $sig = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret);
    if (!hash_equals($sig, $parts[2])) return null;
    $payload = json_decode(base64_decode($parts[1], true), true);
    if (!$payload || empty($payload['uid']) || ($payload['exp'] ?? 0) < time()) return null;
    return (int)$payload['uid'];
}

function jwt_from_request() {
    $token = '';
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) $token = $m[1];
    if ($token === '') $token = $_GET['token'] ?? '';
    return $token !== '' ? jwt_verify($token) : null;
}

function db() {
    return new PDO("mysql:host=sql309.infinityfree.com;dbname=if0_42944847_AntiPackageLeak;charset=utf8mb4",
        'if0_42944847', 'c92WWhdVGc', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function b64($data) {
    return base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

switch ($act) {

case 'hwid':
    $login = trim($_GET['login'] ?? '');
    $pass  = trim($_GET['password'] ?? '');
    $hwid  = trim($_GET['hwid'] ?? '');
    $build = isset($_GET['build']) ? (int)$_GET['build'] : 0;
    if ($login === '' || $pass === '' || $hwid === '') {
        echo b64(['ok' => false, 'error' => 'missing_fields']); exit;
    }
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, password, hwid, banned, uuid FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        echo b64(['ok' => false, 'error' => 'db_error']); exit;
    }
    if (!$user) { echo b64(['ok' => false, 'error' => 'invalid_credentials']); exit; }
    if (!empty($user['banned'])) { echo b64(['ok' => false, 'error' => 'banned']); exit; }
    if (!password_verify($pass, $user['password'])) { echo b64(['ok' => false, 'error' => 'invalid_credentials']); exit; }
    if (!empty($user['hwid']) && $user['hwid'] !== $hwid) { echo b64(['ok' => false, 'error' => 'hwid_mismatch']); exit; }
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
        if ($build > 0) {
            $ver = $pdo->prepare("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 AND launcher_build_min <= ? ORDER BY id DESC LIMIT 1");
            $ver->execute([$build]);
        } else {
            $ver = $pdo->query("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        }
        $verRow = $ver->fetch();
        if ($verRow && !empty($verRow['crypto_key'])) $crypto_key = $verRow['crypto_key'];
    } catch (PDOException $e) {}
    $token = jwt_create($user['id']);
    try {
        $pdo->prepare("INSERT INTO launcher_stats (user_id, hwid, ip, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$user['id'], $hwid, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (PDOException $e) {}
    echo b64(['ok' => true, 'uuid' => $uuid, 'crypto_key' => $crypto_key, 'token' => $token,
        'user' => ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => 'user',
            'subscription_end' => null, 'avatar_url' => '']]);
    break;

case 'session':
    $client = trim($_GET['client'] ?? '');
    $login  = trim($_GET['login']  ?? '');
    $hwid   = trim($_GET['hwid']   ?? '');
    if ($client === '' || $login === '') { echo b64(['ok' => false]); exit; }
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        $uid = $user ? $user['id'] : 0;
    } catch (PDOException $e) { $uid = 0; }
    try {
        $pdo->prepare("INSERT INTO launcher_stats (user_id, client, hwid, ip, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$uid, $client, $hwid, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (PDOException $e) {}
    echo b64(['ok' => true]);
    break;

case 'yznay':
    $exe    = strtolower(trim($_GET['exe']    ?? ''));
    $client = strtolower(trim($_GET['client'] ?? ''));
    $parent = strtolower(trim($_GET['parent'] ?? ''));
    $exe = preg_replace('/[^a-z0-9]/', '', $exe);
    $client = preg_replace('/[^a-z0-9]/', '', $client);
    $parent = preg_replace('/[^a-z0-9]/', '', $parent);
    $clients = [
        'AntiPackageLeak' => [
            'names' => ['AntiPackageLeak','0x15loader','AntiPackageLeakloader','loader'],
            'name' => 'AntiPackageLeak', 'site_url' => 'https://antiaileaks.ct.ws/',
            'loader_url' => 'https://antiaileaks.ct.ws/480eefb0c03178c537353c5b3c23acd1',
            'dir' => 'AntiPackageLeak', 'download_url' => 'https://antiaileaks.ct.ws/92045e61267bf502d93cc2c130a058c6',
        ],
    ];
    $found = 'AntiPackageLeak';
    foreach (['client', 'parent', 'exe'] as $k) {
        $v = $$k;
        if ($v !== '') {
            foreach ($clients as $ck => $c) {
                if (in_array($v, $c['names']) || $v === $ck) { $found = $ck; break 2; }
            }
        }
    }
    $c = $clients[$found];
    echo b64([
        'ok' => true, 'client' => $found, 'name' => $c['name'],
        'site_url' => $c['site_url'], 'loader_url' => $c['loader_url'],
        'dir' => $c['dir'], 'download_url' => $c['download_url'],
    ]);
    break;

case 'config':
    $build = isset($_GET['build']) ? (int)$_GET['build'] : 0;
    try {
        $pdo = db();
        $settings = [];
        $rows = $pdo->query("SELECT setting_key, setting_value FROM launcher_settings")->fetchAll();
        foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
    } catch (PDOException $e) { $settings = []; }
    try {
        if ($build > 0) {
            $ver = $pdo->prepare("SELECT * FROM launcher_versions WHERE is_active = 1 AND launcher_build_min <= ? ORDER BY id DESC LIMIT 1");
            $ver->execute([$build]);
            $ver = $ver->fetch();
        } else {
            $ver = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        }
    } catch (PDOException $e) { $ver = null; }
    $crypto_key = '';
    try {
        if ($build > 0) {
            $ck = $pdo->prepare("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 AND launcher_build_min <= ? ORDER BY id DESC LIMIT 1");
            $ck->execute([$build]);
            $ck = $ck->fetch();
        } else {
            $ck = $pdo->query("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        }
        if ($ck && !empty($ck['crypto_key'])) $crypto_key = $ck['crypto_key'];
    } catch (PDOException $e) {}
    echo b64([
        'site_name' => $settings['site_name'] ?? ($SITE_NAME ?? 'Placeholder'),
        'site_url' => $settings['site_url'] ?? 'https://antiaileaks.ct.ws',
        'version' => $ver ? ($ver['version'] ?? '1.0.0') : '1.0.0',
        'url_game_jar' => $ver ? ($ver['url_game_jar'] ?? '') : '',
        'url_libs' => $ver ? ($ver['url_libs'] ?? '') : '',
        'url_assets' => $settings['global_assets_url'] ?? ($ver ? ($ver['url_assets'] ?? '') : ''),
        'url_java' => $settings['java_download_url'] ?? ($ver ? ($ver['url_java'] ?? '') : ''),
        'url_natives' => $ver ? ($ver['url_natives'] ?? '') : '',
        'url_fake_jar' => $ver ? ($ver['url_fake_jar'] ?? '') : '',
        'install_dir' => $ver ? ($ver['install_dir'] ?? 'PouchCrack') : 'PouchCrack',
        'hash_game_jar' => $ver ? ($ver['hash_game_jar'] ?? '') : '',
        'hash_libs' => $ver ? ($ver['hash_libs'] ?? '') : '',
        'hash_assets' => $settings['global_assets_hash'] ?? ($ver ? ($ver['hash_assets'] ?? '') : ''),
        'hash_java' => $settings['java_download_hash'] ?? ($ver ? ($ver['hash_java'] ?? '') : ''),
        'hash_natives' => $ver ? ($ver['hash_natives'] ?? '') : '',
        'hash_fake_jar' => $ver ? ($ver['hash_fake_jar'] ?? '') : '',
        'crypto_key' => $crypto_key,
    ]);
    break;

case 'uid':
    $login = $_GET['login'] ?? '';
    $password = $_GET['password'] ?? '';
    if ($login === '' || $password === '') {
        echo b64(['ok' => false, 'error' => 'missing params']); exit;
    }
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, uid, password FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$login]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password'])) {
            $uid = $row['uid'] ?? '';
            if ($uid === '') {
                $uid = md5($login . $password . 'AntiPackageLeak_salt');
                $pdo->prepare("UPDATE users SET uid = ? WHERE id = ?")->execute([$uid, $row['id']]);
            }
            echo b64(['ok' => true, 'uid' => (string)$uid]);
        } else {
            echo b64(['ok' => false, 'error' => 'auth failed']);
        }
    } catch (PDOException $e) {
        echo b64(['ok' => false, 'error' => 'db error']);
    }
    break;

case 'version':
    $apiVer = $_GET['apiVersionName'] ?? '';
    $versionName = '1.0.0';
    $assetsIndex = '';
    $version = '1.0.0';
    $isFabric = false;
    try {
        $pdo = db();
        $row = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
        if ($row) {
            $versionName = $row['version'] ?? $versionName;
            $assetsIndex = $row['url_assets'] ?? $assetsIndex;
            $version = $row['version'] ?? $version;
            $isFabric = !empty($row['install_dir']) && strpos($row['install_dir'], 'fabric') !== false;
        }
    } catch (PDOException $e) {}
    echo b64([
        'apiVersionName' => $apiVer,
        'versionName' => $versionName,
        'assetsIndex' => $assetsIndex,
        'version' => $version,
        'libraries' => '',
        'public' => true,
        'fabric' => $isFabric,
    ]);
    break;

case 'login':
    $input = @json_decode(@file_get_contents('php://input'), true) ?? [];
    $login = trim($input['login'] ?? $_POST['login'] ?? '');
    $pass  = $input['password'] ?? $_POST['password'] ?? '';
    $hwid  = trim($input['hwid'] ?? $_POST['hwid'] ?? '');
    if ($login === '' || $pass === '') {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>false,'error'=>'missing_fields']); exit;
    }
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, email, password, role, banned, subscription_end, avatar_url, hwid FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>false,'error'=>'db_error']); exit;
    }
    if (!$user) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'error'=>'invalid_credentials']); exit; }
    if (!empty($user['banned'])) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'error'=>'banned','reason'=>'Account banned']); exit; }
    if (!password_verify($pass, $user['password'])) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'error'=>'invalid_credentials']); exit; }
    if ($hwid !== '' && !empty($user['hwid']) && $user['hwid'] !== $hwid) {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>false,'error'=>'hwid_mismatch']); exit;
    }
    if ($hwid !== '' && empty($user['hwid'])) {
        try { $pdo->prepare("UPDATE users SET hwid = ? WHERE id = ?")->execute([$hwid, $user['id']]); } catch (PDOException $e) {}
    }
    $token = jwt_create($user['id']);
    try { $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?")->execute([$_SERVER['REMOTE_ADDR'] ?? '', $user['id']]); } catch (PDOException $e) {}
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true, 'token' => $token,
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'] ?? '',
            'role' => $user['role'] ?? 'user',
            'subscription_end' => $user['subscription_end'] ?? null,
            'avatar_url' => $user['avatar_url'] ?? '',
        ],
    ]);
    exit;

case 'info':
    header('Content-Type: application/json');
    $uid = jwt_from_request();
    if (!$uid) { echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, email, role, subscription_end, avatar_url FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        echo json_encode(['ok'=>false,'error'=>'db_error']); exit;
    }
    if (!$user) { echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
    echo json_encode([
        'ok' => true,
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'] ?? '',
            'role' => $user['role'] ?? 'user',
            'subscription_end' => $user['subscription_end'] ?? null,
            'avatar_url' => $user['avatar_url'] ?? '',
        ],
    ]);
    exit;

case 'versions':
    header('Content-Type: application/json');
    $uid = jwt_from_request();
    if (!$uid) { echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }
    $versions = [];
    try {
        $pdo = db();
        $versions = $pdo->query("SELECT version, blocked FROM launcher_versions ORDER BY id DESC")->fetchAll();
    } catch (PDOException $e) {
        try {
            $versions = $pdo->query("SELECT version FROM launcher_versions ORDER BY id DESC")->fetchAll();
        } catch (PDOException $e2) { $versions = []; }
    }
    echo json_encode(['ok'=>true, 'versions'=>$versions]);
    exit;

default:
    echo b64(['ok' => false, 'error' => 'unknown_act']);
}
