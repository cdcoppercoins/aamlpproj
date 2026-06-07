<?php
/**
 * One-time: recreate laravel/.env on the server (after accidental delete).
 * Upload to public_html/setup-env.php — DELETE this file when done.
 */
declare(strict_types=1);

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$laravelRoot = dirname(__DIR__) . '/laravel';
$envPath = $laravelRoot . '/.env';

$authorized = false;
$expectedToken = null;

if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), 'DEPLOY_APPLY_TOKEN=')) {
            $expectedToken = trim(substr(trim($line), 19), " \t\"'");
            break;
        }
    }
    if ($expectedToken !== null && $expectedToken !== '' && $token !== '') {
        $authorized = hash_equals($expectedToken, $token);
    }
}

if (! $authorized && $token !== '' && is_file(__DIR__ . '/.setup-env-token')) {
    $fileToken = trim((string) file_get_contents(__DIR__ . '/.setup-env-token'));
    if ($fileToken !== '') {
        $authorized = hash_equals($fileToken, $token);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $authorized) {
    header('Content-Type: text/plain; charset=utf-8');

    $dbHost = trim((string) ($_POST['db_host'] ?? 'localhost'));
    $dbName = trim((string) ($_POST['db_database'] ?? ''));
    $dbUser = trim((string) ($_POST['db_username'] ?? ''));
    $dbPass = (string) ($_POST['db_password'] ?? '');
    $applyToken = trim((string) ($_POST['deploy_apply_token'] ?? $token));

    if ($dbName === '' || $dbUser === '') {
        http_response_code(400);
        echo "Database name and username are required.\n";
        exit(1);
    }

    $appKey = trim((string) ($_POST['app_key'] ?? ''));
    if ($appKey === '') {
        $appKey = 'base64:' . base64_encode(random_bytes(32));
    }

    $env = <<<ENV
APP_NAME="MiniLicensePlates.com"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL=https://minilicenseplates.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT=3306
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=sendmail
MAIL_FROM_ADDRESS=cdcoppercoins@gmail.com
MAIL_FROM_NAME="MiniLicensePlates.com"

CONTRIBUTE_MAIL_TO=cdcoppercoins@gmail.com
CONTRIBUTE_MAIL_FROM_NAME="mlp question"
CONTRIBUTE_MAIL_FROM_ADDRESS=cdcoppercoins@gmail.com

DEPLOY_APPLY_TOKEN={$applyToken}

ADSENSE_ENABLED=false
ENV;

    if (! is_dir($laravelRoot)) {
        http_response_code(500);
        echo "laravel/ folder not found.\n";
        exit(1);
    }

    if (file_put_contents($envPath, $env) === false) {
        http_response_code(500);
        echo "Could not write laravel/.env\n";
        exit(1);
    }

    echo "OK — laravel/.env was created.\n\n";
    echo "Next:\n";
    echo "1. Delete public_html/setup-env.php from the server.\n";
    echo "2. Run Deploy-Now.bat on your PC.\n";
    echo "3. Open https://minilicenseplates.com and press Ctrl+F5.\n";
    exit(0);
}

header('Content-Type: text/html; charset=utf-8');

if (! $authorized) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;max-width:520px;margin:2rem auto">';
    echo '<h1>Not allowed</h1>';
    echo '<p>Open this page with the same <code>deployApplyToken</code> as in <code>deploy/deploy.local.json</code>:</p>';
    echo '<p><code>setup-env.php?token=YOUR_TOKEN</code></p>';
    echo '<p>Or upload <code>deploy/.setup-env-token</code> (one line = that token) next to this file.</p>';
    echo '</body></html>';
    exit(1);
}

$hasEnv = is_file($envPath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recreate laravel/.env</title>
    <style>
        body { font-family: sans-serif; max-width: 520px; margin: 2rem auto; line-height: 1.5; }
        label { display: block; margin-top: 1rem; font-weight: bold; }
        input { width: 100%; padding: 0.5rem; box-sizing: border-box; }
        button { margin-top: 1.5rem; padding: 0.6rem 1.2rem; font-size: 1rem; }
        .note { background: #f4f8fb; padding: 1rem; border-radius: 6px; font-size: 0.95rem; }
    </style>
</head>
<body>
    <h1>Recreate laravel/.env</h1>
    <?php if ($hasEnv) { ?>
        <p class="note"><strong>laravel/.env already exists.</strong> Only submit if you need to replace it.</p>
    <?php } else { ?>
        <p class="note">Your server <strong>.env</strong> file is missing. Fill in MySQL info from <strong>cPanel → MySQL Databases</strong>.</p>
    <?php } ?>
    <form method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <label>DB host (usually localhost)</label>
        <input name="db_host" value="localhost" required>
        <label>Database name</label>
        <input name="db_database" required placeholder="e.g. minilp_wrdp1">
        <label>Database username</label>
        <input name="db_username" required placeholder="e.g. minilp_wrdp1">
        <label>Database password</label>
        <input name="db_password" type="password" autocomplete="new-password">
        <label>Deploy token (same as deploy.local.json)</label>
        <input name="deploy_apply_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" required>
        <label>APP_KEY (leave blank to generate new)</label>
        <input name="app_key" placeholder="optional">
        <button type="submit">Create laravel/.env</button>
    </form>
</body>
</html>
