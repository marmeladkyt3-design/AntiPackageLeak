<?php
if (basename($_SERVER['PHP_SELF'] ?? '') === basename(__FILE__)) { http_response_code(403); exit('Forbidden'); }

if (!defined('ANTI_SECRET')) {
    define('ANTI_SECRET', 'ca4f01f8847f72673543f8f73d77b1f7ba9d51f60fbf29fa3d769d5f9f2009d0');
}
define('ANTI_TOKEN_TTL', 7200);
define('ANTI_DIR', sys_get_temp_dir() . '/ad2');

@is_dir(ANTI_DIR) || @mkdir(ANTI_DIR, 0777, true);

// Clean ALL old ban/rate files
foreach (@glob(ANTI_DIR . '/b_*') ?: [] as $f) { @unlink($f); }
foreach (@glob(ANTI_DIR . '/r_*') ?: [] as $f) { @unlink($f); }
foreach (@glob(ANTI_DIR . '/cr_*') ?: [] as $f) { @unlink($f); }
foreach (@glob(ANTI_DIR . '/chal_*') ?: [] as $f) { @unlink($f); }

function anti_is_https(): bool {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
    if (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on') return true;
    return false;
}

function anti_make_token(): string {
    $exp = time() + ANTI_TOKEN_TTL;
    $nonce = bin2hex(random_bytes(8));
    $body = $exp . '|' . $nonce;
    return rtrim(strtr(base64_encode($body), '+/', '-_'), '=') . '.' . hash_hmac('sha256', $body, ANTI_SECRET);
}

function anti_valid_token(string $token): bool {
    $p = explode('.', $token, 2);
    if (count($p) !== 2) return false;
    $body = @base64_decode(strtr($p[0], '-_', '+/'), true);
    if ($body === false || substr_count($body, '|') < 1) return false;
    if (!hash_equals(hash_hmac('sha256', $body, ANTI_SECRET), $p[1])) return false;
    $parts = explode('|', $body);
    $exp = (int)$parts[0];
    if ($exp < time()) return false;
    return true;
}

function anti_serve_challenge(): never {
    $is_https = anti_is_https();

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Проверка...</title>';
    echo '<style>*{margin:0;padding:0;box-sizing:border-box}body{background:#06060f;color:#c8cdd8;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center}.c{text-align:center;padding:40px;max-width:400px}.s{width:72px;height:72px;margin:0 auto 24px;border-radius:18px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:0 20px 60px -12px rgba(99,102,241,.5);animation:p 2.5s ease-in-out infinite}@keyframes p{0%,100%{transform:scale(1)}50%{transform:scale(1.04)}}h2{font-size:20px;font-weight:700;margin-bottom:6px;color:#f0f1f5}p{font-size:13px;color:#6b7280;margin-bottom:24px;line-height:1.5}.sp{width:36px;height:36px;margin:0 auto;border:3px solid rgba(255,255,255,.08);border-top-color:#6366f1;border-radius:50%;animation:r .8s linear infinite}@keyframes r{to{transform:rotate(360deg)}}.pr{margin-top:14px;font-size:11px;color:#4b5563}.er{display:none;margin-top:18px;padding:12px 22px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:10px;color:#fca5a5;font-size:12px}.rb{display:none;margin-top:16px;padding:11px 24px;background:#6366f1;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer}.rb:hover{background:#5558e6}</style></head>';
    echo '<body><div class="c"><div class="s">&#128737;</div><h2>Проверка безопасности</h2><p>Подождите, выполняется автоматическая проверка вашего браузера...</p><div class="sp" id="sp"></div><div class="pr" id="pr">Инициализация...</div><div class="er" id="er">Не удалось пройти проверку. Обновите страницу.</div><button class="rb" id="rb" onclick="location.reload()">Попробовать снова</button></div>';
    echo '<script>';
    echo 'try{sessionStorage.removeItem("anti_t")}catch(e){}';
    echo '(function(){';
    echo 'var CH="'.bin2hex(random_bytes(16)).'";';
    echo 'var sp=document.getElementById("sp"),pr=document.getElementById("pr"),er=document.getElementById("er"),rb=document.getElementById("rb");';
    echo 'function fail(m){sp.style.display="none";er.style.display="block";rb.style.display="inline-block";pr.textContent="";if(m)console.error("challenge_error:",m)}';
    echo 'function getFP(){var fp=[];try{var c=document.createElement("canvas"),x=c.getContext("2d");x.textBaseline="top";x.font="14px Arial";x.fillStyle="#f60";x.fillRect(0,0,200,50);x.fillStyle="#069";x.fillText("fp",2,15);fp.push(c.toDataURL().length)}catch(e){fp.push(0)}fp.push(screen.width,screen.height,screen.colorDepth);try{fp.push(Intl.DateTimeFormat().resolvedOptions().timeZone)}catch(e){fp.push("")}fp.push(navigator.language||"",navigator.platform||"",navigator.maxTouchPoints||0,navigator.hardwareConcurrency||0);return fp.join("|")}';
    echo 'pr.textContent="Проверка...";';
    echo 'var fp=getFP();';
    echo 'setTimeout(function(){';
    echo 'var xhr=new XMLHttpRequest();';
    echo 'xhr.open("POST","/challenge.php",true);';
    echo 'xhr.setRequestHeader("Content-Type","application/json");';
    echo 'xhr.onreadystatechange=function(){';
    echo 'if(xhr.readyState===4){';
    echo 'if(xhr.status===200){try{var d=JSON.parse(xhr.responseText);if(d&&d.ok){location.reload()}else{fail("server:"+JSON.stringify(d))}}catch(e){fail("json:"+e.message+" resp:"+xhr.responseText.substring(0,200))}}';
    echo 'else{fail("http:"+xhr.status)}}};';
    echo 'xhr.onerror=function(){fail("network_error")};';
    echo 'xhr.send(JSON.stringify({c:CH,fp:fp}));';
    echo '},1500)';
    echo '})()</script></body></html>';
    exit;
}

/* ============ MAIN ============ */

$anti_uri = strtolower($_SERVER['REQUEST_URI'] ?? '/');
$anti_skip_ext = ['.jpg','.jpeg','.png','.gif','.webp','.ico','.svg','.css','.js','.woff2','.woff','.ttf','.eot','.mp3','.mp4','.zip','.jar','.json','.xml','.map','.webm'];
$anti_ext = strtolower(pathinfo($anti_uri, PATHINFO_EXTENSION));
if ($anti_ext && in_array('.' . $anti_ext, $anti_skip_ext, true)) { return; }

$anti_skip_paths = ['/api','/assets/','/devtools.js','/antiddos.js','/challenge.php','/captcha.php','/480eefb0c03178c537353c5b3c23acd1'];
foreach ($anti_skip_paths as $sp) {
    if (str_starts_with($anti_uri, $sp)) { return; }
}

$AAL_BYPASS_KEY = 'aal_launcher_v2_c782d208d32e3fa0208494e8ca5b569b';
if (isset($_SERVER['HTTP_X_AAL_KEY']) && hash_equals($AAL_BYPASS_KEY, $_SERVER['HTTP_X_AAL_KEY'])) { return; }
if (isset($_GET['aal']) && hash_equals($AAL_BYPASS_KEY, $_GET['aal'])) { return; }

if (anti_valid_token($_COOKIE['anti_v'] ?? '')) { return; }

if (isset($_COOKIE['anti_v'])) {
    setcookie('anti_v', '', time() - 3600, '/', '', anti_is_https(), true);
}

anti_serve_challenge();
