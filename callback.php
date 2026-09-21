<?php
// ============================================================
// ROLLYPAY — ВЕБХУК (callback.php)
// Принимает события: payment.paid, payment.canceled,
// payment.expired, payment.chargeback, payment.refunded
// Подпись: HMAC-SHA256(timestamp + "." + rawBody, signing_secret)
// ============================================================
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';

// Если конфиг не обновлён — молча отвечаем 500, вебхуки будут повторены позже
if (!defined('ROLLYPAY_SIGNING_SECRET') || !defined('ROLLYPAY_TERMINAL_ID')) {
    http_response_code(500);
    exit('Config not ready');
}

// Гарантируем существование таблицы платежей
try {
    $pdo->query("SELECT 1 FROM payments LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` varchar(64) NOT NULL,
            `user_id` int(11) NOT NULL,
            `plan` varchar(32) NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `currency` varchar(8) NOT NULL DEFAULT 'RUB',
            `status` varchar(20) NOT NULL DEFAULT 'created',
            `payment_id` varchar(64) DEFAULT NULL,
            `promo_code` varchar(64) DEFAULT NULL,
            `test` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `paid_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `order_id` (`order_id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e2) {}
}

$raw_body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
$timestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';

// Проверка подписи — обязательно
$expected = hash_hmac('sha256', $timestamp . '.' . $raw_body, ROLLYPAY_SIGNING_SECRET);
if (!is_string($signature) || !hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}

// Защита от повторов: X-Timestamp должен быть свежим (не старше 10 минут)
if (!preg_match('/^[0-9]+$/', (string)$timestamp) || abs(time() - (int)$timestamp) > 600) {
    http_response_code(403);
    exit('Stale timestamp');
}

$data = json_decode($raw_body, true);
if (!is_array($data)) {
    http_response_code(400);
    exit('Bad JSON');
}

$order_id   = (string)($data['order_id'] ?? '');
$status     = (string)($data['status'] ?? '');
$payment_id = (string)($data['payment_id'] ?? '');
$test       = !empty($data['test']);

if ($order_id === '' || $status === '') {
    http_response_code(400);
    exit('Missing fields');
}

// Находим платёж по order_id
$stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? LIMIT 1");
$stmt->execute([$order_id]);
$pay = $stmt->fetch();
if (!$pay) {
    http_response_code(404);
    exit('Payment not found');
}

$prev_status = $pay['status'];

// Если событие по уже финальному статусу (или то же) — просто 200 (идемпотентность)
if ($status === $prev_status) {
    http_response_code(200);
    exit('OK');
}

// Обновляем статус платежа
$pdo->prepare("UPDATE payments SET status = ?, payment_id = COALESCE(NULLIF(?, ''), payment_id), updated_at = NOW() WHERE order_id = ?")
    ->execute([$status, $payment_id, $order_id]);

// ===== УСПЕШНАЯ ОПЛАТА =====
if ($status === 'paid') {
    $pdo->prepare("UPDATE payments SET paid_at = NOW(), updated_at = NOW() WHERE order_id = ?")->execute([$order_id]);

    // Выдаём подписку навсегда (как в админке: days=99999)
    $stmt = $pdo->prepare("UPDATE users SET subscription_end = '9999-12-31 23:59:59', role = 'user' WHERE id = ?");
    $stmt->execute([$pay['user_id']]);

    // Уведомление пользователю
    try {
        $stmt = $pdo->prepare("INSERT INTO user_notifications (user_id, title, message, type, link) VALUES (?, 'Оплата получена', ?, 'success', '/profile')");
        $stmt->execute([$pay['user_id'], 'Оплата подписки «' . $pay['plan'] . '» подтверждена. Доступ активирован навсегда!']);
    } catch (PDOException $e) {}

    try { $pdo->exec("CREATE TABLE IF NOT EXISTS `referrals` (`id` int(11) NOT NULL AUTO_INCREMENT,`referrer_id` int(11) NOT NULL,`referred_id` int(11) NOT NULL,`rewarded` tinyint(1) NOT NULL DEFAULT 0,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),UNIQUE KEY `referred_id` (`referred_id`),KEY `referrer_id` (`referrer_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (PDOException $e) {}
    $ref_stmt = $pdo->prepare("SELECT id, referrer_id FROM referrals WHERE referred_id = ? AND rewarded = 0 LIMIT 1");
    $ref_stmt->execute([$pay['user_id']]);
    $ref = $ref_stmt->fetch();
    if ($ref) {
        $pdo->prepare("UPDATE referrals SET rewarded = 1 WHERE id = ?")->execute([$ref['id']]);
        $ref_user = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
        $ref_user->execute([$ref['referrer_id']]);
        $ru = $ref_user->fetch();
        if ($ru) {
            $base = ($ru['subscription_end'] && strtotime($ru['subscription_end']) > time()) ? strtotime($ru['subscription_end']) : time();
            $new_end = date('Y-m-d H:i:s', $base + 86400);
            $pdo->prepare("UPDATE users SET subscription_end = ? WHERE id = ?")->execute([$new_end, $ref['referrer_id']]);
            try { $pdo->prepare("INSERT INTO user_notifications (user_id, title, message, type, link) VALUES (?, 'Реферальная награда', 'Ваш друг оплатил подписку! Вам начислен +1 день подписки.', 'success', '/profile')")->execute([$ref['referrer_id']]); } catch (PDOException $e) {}
        }
    }

    http_response_code(200);
    exit('OK');
}

// ===== ЧАРДЖБЕК / ВОЗВРАТ — отзываем подписку, если она была выдана этим платежом =====
if (($status === 'chargeback' || $status === 'refunded') && $prev_status === 'paid') {
    $stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
    $stmt->execute([$pay['user_id']]);
    $u = $stmt->fetch();
    if ($u && !empty($u['subscription_end']) && $u['subscription_end'] === '9999-12-31 23:59:59') {
        $pdo->prepare("UPDATE users SET subscription_end = NULL WHERE id = ?")->execute([$pay['user_id']]);
    }
}

http_response_code(200);
exit('OK');
