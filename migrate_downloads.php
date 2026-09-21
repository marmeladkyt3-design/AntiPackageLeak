<?php
/**
 * Download Logs Table Migration
 * Run once to create the download_logs table
 */
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `download_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `filename` varchar(255) NOT NULL,
        `ip` varchar(45) NOT NULL DEFAULT '',
        `hwid` varchar(64) NOT NULL DEFAULT '',
        `key_code` varchar(100) NOT NULL DEFAULT '',
        `downloaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_filename` (`filename`),
        KEY `idx_downloaded_at` (`downloaded_at`),
        KEY `idx_hwid` (`hwid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "download_logs table created successfully\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Add launcher_build_min, update_url, update_msg columns if missing
$cols_to_add = [
    'launcher_build_min' => "ALTER TABLE launcher_versions ADD COLUMN `launcher_build_min` int(11) NOT NULL DEFAULT 0 AFTER `crypto_key`",
    'update_url' => "ALTER TABLE launcher_versions ADD COLUMN `update_url` text NOT NULL DEFAULT '' AFTER `launcher_build_min`",
    'update_msg' => "ALTER TABLE launcher_versions ADD COLUMN `update_msg` text NOT NULL DEFAULT '' AFTER `update_url`",
];

foreach ($cols_to_add as $col => $sql) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM launcher_versions LIKE '$col'");
        if ($check->rowCount() == 0) {
            $pdo->exec($sql);
            echo "Added column: $col\n";
        }
    } catch (PDOException $e) {}
}

echo "Done!\n";
