<?php
// ========== НАЗВАНИЕ САЙТА / ЛАУНЧЕРА ==========
$SITE_NAME = 'AntiPackageLeak';

// ========== ПРЕФИКС ДЛЯ ORDER_ID В ПЛАТЕЖАХ ==========
$ORDER_PREFIX = 'aial';

// ========== ССЫЛКИ ==========
// Укажите ссылки, они подставятся на сайте (index.php) и в профиле (profile.php)
$YOUTUBE_LINK = 'https://www.youtube.com/watch?v=rgk3ZHFmHm4';   // видео/канал ютуба для index.php
$DISCORD_LINK = 'https://discord.gg/FH3DND8Shj';           // discord сервер
$TELEGRAM_LINK = 'https://t.me/AntiPackageLeak';             // telegram канал
$LOADER_LINK = 'launcher/launcher.exe';                    // ссылка на лаунчер для profile.php

// ========== ПЛАТЁЖНАЯ СИСТЕМА ROLLYPAY ==========
define('ROLLYPAY_API_KEY', 'DUgiTOSphNaEZjEE7Cb5f5r9g3OA7LiVgKORIzkkmMM');
define('ROLLYPAY_SIGNING_SECRET', 'wKC6iyOcqiHl0BiUEJkob_Le9NjyjTTxvQeuqSwgu2g');
define('ROLLYPAY_TERMINAL_ID', '3be7b2ac-8a46-4b3d-8155-1b777f7d70c3');
define('ROLLYPAY_API_URL', 'https://api.rollypay.io/api/v1/payments');
define('ROLLYPAY_CALLBACK_URL', 'https://antiaileaks.ct.ws/callback.php');
define('ROLLYPAY_SUCCESS_URL', 'https://antiaileaks.ct.ws/success.php');
define('ROLLYPAY_FAIL_URL', 'https://antiaileaks.ct.ws/fail.php');

// ========== ФУНКЦИИ ROLLYPAY (по схеме donatesite1337) ==========
function rollypay_api_key() {
    return ROLLYPAY_API_KEY;
}
function rollypay_secret() {
    return ROLLYPAY_SIGNING_SECRET;
}
function rollypay_terminal_id() {
    return ROLLYPAY_TERMINAL_ID;
}
function rollypay_enabled() {
    return defined('ROLLYPAY_API_KEY') && defined('ROLLYPAY_SIGNING_SECRET')
        && ROLLYPAY_API_KEY !== '' && ROLLYPAY_SIGNING_SECRET !== '';
}

function rollypay_base_url() {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'antiaileaks.ct.ws';
    return ($https ? 'https' : 'http') . '://' . $host;
}

function rollypay_nonce() {
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
}

function rollypay_create_payment($amount, $orderId, $description, $successUrl, $failUrl) {
    $apiKey = rollypay_api_key();
    $terminal = rollypay_terminal_id();
    if ($apiKey === '') {
        return ['error' => 'Оплата не настроена: укажите API-ключ RollyPay.'];
    }
    $payload = [
        'amount' => number_format((float)$amount, 2, '.', ''),
        'payment_currency' => 'RUB',
        'order_id' => $orderId,
        'description' => $description,
        'success_redirect_url' => $successUrl,
        'fail_redirect_url' => $failUrl,
    ];
    if ($terminal !== '') {
        $payload['terminal_id'] = $terminal;
    }

    $ch = curl_init(ROLLYPAY_API_URL);
    if ($ch === false) {
        return ['error' => 'Не удалось инициализировать curl.'];
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey,
            'X-Nonce: ' . rollypay_nonce(),
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);
    $raw = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    // Фолбэк: если не удалось из-за SSL-сертификатов (на хостингах без CA) — повтор без проверки
    if ($raw === false && stripos($err, 'SSL') !== false) {
        $ch = curl_init(ROLLYPAY_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $apiKey,
                'X-Nonce: ' . rollypay_nonce(),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
        $raw = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
    }

    if ($raw === false) {
        return ['error' => 'Ошибка запроса к RollyPay: ' . $err];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['error' => 'Некорректный ответ RollyPay (HTTP ' . $http . ').'];
    }
    if ($http >= 400 || !empty($data['error'])) {
        return ['error' => 'RollyPay: ' . ($data['error'] ?? ($data['message'] ?? ('HTTP ' . $http)))];
    }
    if (empty($data['pay_url'])) {
        return ['error' => 'RollyPay не вернул ссылку на оплату.'];
    }
    return $data;
}

function rollypay_verify_signature($body, $timestamp, $signature) {
    $secret = rollypay_secret();
    if ($secret === '') {
        return false;
    }
    $expected = hash_hmac('sha256', $timestamp . '.' . $body, $secret);
    return hash_equals($expected, (string)$signature);
}