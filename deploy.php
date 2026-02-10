<?php
/**
 * Deploy script - copies files and runs setup
 * URL: https://aksa.id/deploy.php?key=aksamedia2026
 * HAPUS FILE INI SETELAH DEPLOY SELESAI!
 */

$KEY = 'aksamedia2026';
if (($_GET['key'] ?? '') !== $KEY) die('Unauthorized');
$action = $_GET['action'] ?? 'info';

echo "<pre style='font-family:monospace;background:#1a1a2e;color:#e94560;padding:20px;'>\n";
echo "=== DEPLOY SCRIPT ===\n";
echo "Action: $action\n\n";

$home = '/home/aksq9845';
$repo = "$home/aksamedia-intern";
$html = "$home/public_html";

switch ($action) {
    case 'info':
        echo "PHP: " . phpversion() . "\n";
        echo "Repo exists: " . (is_dir($repo) ? 'YES' : 'NO') . "\n";
        echo "Backend exists: " . (is_dir("$repo/backend") ? 'YES' : 'NO') . "\n\n";
        echo "Steps:\n";
        echo "1. ?key=$KEY&action=copy      - Copy backend to public_html\n";
        echo "2. ?key=$KEY&action=env       - Create .env file\n";
        echo "3. ?key=$KEY&action=composer  - Install composer\n";
        echo "4. ?key=$KEY&action=setup     - Key + migrate + seed\n";
        echo "5. ?key=$KEY&action=frontend  - Copy frontend files\n";
        echo "6. DELETE this file!\n";
        break;

    case 'copy':
        echo "Copying backend files to public_html...\n";
        echo shell_exec("cp -r $repo/backend/* $html/ 2>&1") . "\n";
        echo shell_exec("cp $repo/backend/.htaccess $html/ 2>&1") . "\n";
        echo shell_exec("cp $repo/backend/.env.example $html/.env 2>&1") . "\n";
        echo shell_exec("chmod -R 755 $html/storage 2>&1") . "\n";
        echo shell_exec("chmod -R 755 $html/bootstrap/cache 2>&1") . "\n";
        echo "DONE! Now run action=env\n";
        break;

    case 'env':
        $envContent = "APP_NAME=Aksamedia
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://aksa.id

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=aksq9845_aksa_intern
DB_USERNAME=aksq9845_admin
DB_PASSWORD=" . ($_GET['dbpass'] ?? 'CHANGE_ME') . "

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file
";
        file_put_contents("$html/.env", $envContent);
        echo ".env created!\n";
        echo "NOTE: If password wrong, use: ?key=$KEY&action=env&dbpass=YOUR_PASSWORD\n";
        echo "Now run action=composer\n";
        break;

    case 'composer':
        echo "Installing composer dependencies...\n";
        putenv("COMPOSER_HOME=$home/.composer");
        echo shell_exec("cd $html && curl -sS https://getcomposer.org/installer | php 2>&1") . "\n";
        echo shell_exec("cd $html && php composer.phar install --no-dev --optimize-autoloader 2>&1") . "\n";
        echo "DONE! Now run action=setup\n";
        break;

    case 'setup':
        chdir($html);
        echo "--- Generate Key ---\n";
        echo shell_exec("cd $html && php artisan key:generate --force 2>&1") . "\n";
        echo "--- Run Migrations ---\n";
        echo shell_exec("cd $html && php artisan migrate --force 2>&1") . "\n";
        echo "--- Run Seeders ---\n";
        echo shell_exec("cd $html && php artisan db:seed --force 2>&1") . "\n";
        echo "--- Storage Link ---\n";
        $target = "$html/storage/app/public";
        $link = "$html/public/storage";
        if (!file_exists($link)) {
            symlink($target, $link) ? print("Storage link created\n") : print("Symlink failed\n");
        } else {
            echo "Storage link exists\n";
        }
        echo "--- Cache ---\n";
        echo shell_exec("cd $html && php artisan config:cache 2>&1") . "\n";
        echo shell_exec("cd $html && php artisan route:cache 2>&1") . "\n";
        echo "\n=== SETUP COMPLETE! ===\n";
        break;

    case 'frontend':
        echo "Copying frontend dist files...\n";
        echo shell_exec("mkdir -p $html/public/app 2>&1") . "\n";
        echo shell_exec("cp -r $repo/frontend/dist/* $html/public/app/ 2>&1") . "\n";
        echo "DONE! Frontend available at https://aksa.id/app\n";
        break;

    default:
        echo "Unknown action\n";
}
echo "</pre>";
