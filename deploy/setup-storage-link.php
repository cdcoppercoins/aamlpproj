<?php
/**
 * One-time: link public_html/storage -> laravel/storage/app/public
 * Upload to /public_html/setup-storage-link.php, open in browser, then DELETE.
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$publicHtml = __DIR__;
$target = dirname(__DIR__) . '/laravel/storage/app/public';
$link = $publicHtml . '/storage';

echo "Storage link setup\n\n";
echo "Target (uploads): $target\n";
echo "Link (web URL /storage/...): $link\n\n";

if (! is_dir($target)) {
    echo "ERROR: Target folder missing. Create it in File Manager:\n";
    echo "  laravel/storage/app/public\n";
    exit;
}

if (! is_writable($target)) {
    echo "WARNING: Target folder may not be writable. Uploads can fail until permissions are fixed.\n\n";
}

if (is_link($link)) {
    echo "OK: storage link already exists.\n";
    echo "Points to: " . readlink($link) . "\n";
    exit;
}

if (file_exists($link)) {
    echo "ERROR: public_html/storage exists but is NOT a symlink.\n";
    echo "Ask your host to remove or rename public_html/storage, then run this script again.\n";
    exit;
}

if (@symlink($target, $link)) {
    echo "SUCCESS: Created storage link.\n";
    echo "Profile photos and other uploads should load at /storage/...\n";
    echo "\nDelete setup-storage-link.php from public_html now.\n";
    exit;
}

echo "ERROR: Could not create symlink (host may block symlinks).\n\n";
echo "Ask your host to run:\n";
echo "  ln -s $target $link\n\n";
echo "Or link in cPanel File Manager if available.\n";
