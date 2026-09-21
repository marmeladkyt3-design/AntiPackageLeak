<?php
// Диагностика деплоя AURA. Залей ОДИН этот файл в корень сайта, открой /deploy_check.php,
// посмотри красные строки (чего не хватает на сервере), долей файлы, затем УДАЛИ этот файл.
header('Content-Type: text/html; charset=utf-8');
$need = [
    'assets/css/aura.css',
    'assets/js/aura.js',
    'assets/logo.webp',
    'assets/logo-256.webp',
    'assets/ava.webp',
    'assets/img/background-1920.webp',
    'assets/img/background-768.webp',
    'assets/img/shot-combat.webp',
    'assets/img/shot-world.webp',
    'assets/shot-launcher.webp',
    'assets/shot-menu.webp',
    'challenge.php',
    'antiddos.php',
    'lang.js',
    'devtools.js',
];
echo '<body style="background:#0f0f23;color:#e2e8f0;font-family:monospace;padding:24px">';
echo '<h2>AURA deploy check — PHP ' . PHP_VERSION . '</h2><ul>';
$bad = 0;
foreach ($need as $f) {
    $p = __DIR__ . '/' . $f;
    if (is_file($p) && is_readable($p)) {
        echo '<li style="color:#5ebb2b">OK ' . $f . ' (' . round(filesize($p) / 1024, 1) . ' KB)</li>';
    } else {
        $bad++;
        echo '<li style="color:#f43f5e">MISSING ' . $f . '</li>';
    }
}
echo '</ul>';
echo $bad ? '<h3 style="color:#f43f5e">Не хватает файлов: ' . $bad . ' — залей их, иначе белые страницы</h3>'
           : '<h3 style="color:#5ebb2b">Все файлы на месте. Если страницы всё равно белые — смотри логи ошибок PHP.</h3>';
echo '<p>УДАЛИ deploy_check.php после проверки.</p></body>';
