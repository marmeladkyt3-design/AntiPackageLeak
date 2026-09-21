<?php
/**
 * Convert images to WebP and AVIF for performance
 * Run on server after deployment: php convert_images.php
 */

$sourceDir = __DIR__ . '/assets/img/';
$qualityWebP = 82;
$qualityAVIF = 50;

if (!extension_loaded('gd')) {
    die("GD extension required\n");
}

if (!function_exists('imageavif')) {
    echo "AVIF not supported (PHP 8.1+), WebP only\n";
}

$images = glob($sourceDir . '*.{jpg,jpeg,png}', GLOB_BRACE);
echo "Found " . count($images) . " images\n";

foreach ($images as $src) {
    $name = basename($src);
    $base = pathinfo($name, PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
    // Create image resource
    $img = null;
    if ($ext === 'png') {
        $img = imagecreatefrompng($src);
        if (!$img) continue;
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
    } elseif (in_array($ext, ['jpg', 'jpeg'])) {
        $img = imagecreatefromjpeg($src);
        if (!$img) continue;
    } else {
        continue;
    }
    
    // WebP
    $webpPath = $sourceDir . $base . '.webp';
    if (imagewebp($img, $webpPath, $qualityWebP)) {
        echo "✓ WebP: $base.webp (" . round(filesize($webpPath)/1024, 1) . " KB)\n";
    }
    
    // AVIF (PHP 8.1+)
    if (function_exists('imageavif')) {
        $avifPath = $sourceDir . $base . '.avif';
        if (imageavif($img, $avifPath, $qualityAVIF)) {
            echo "✓ AVIF: $base.avif (" . round(filesize($avifPath)/1024, 1) . " KB)\n";
        }
    }
    
    imagedestroy($img);
}

echo "Done!\n";