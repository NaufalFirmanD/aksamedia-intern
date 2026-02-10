<?php
/**
 * Deploy script - PHP-only, no shell_exec needed
 * URL: http://aksa.id/deploy.php?key=aksamedia2026
 * HAPUS FILE INI SETELAH DEPLOY SELESAI!
 */
set_time_limit(300);
ini_set('memory_limit', '256M');

$KEY = 'aksamedia2026';
if (($_GET['key'] ?? '') !== $KEY) die('Unauthorized');
$action = $_GET['action'] ?? 'info';

echo "<pre style='font-family:monospace;background:#1a1a2e;color:#e94560;padding:20px;'>\n";
echo "=== DEPLOY SCRIPT ===\n";
echo "Action: $action\n\n";

$home = '/home/aksq9845';
$repo = "$home/aksamedia-intern";
$html = "$home/public_html";

// PHP-native recursive copy
function rcopy($src, $dst) {
    $count = 0;
    if (!is_dir($src)) { echo "ERROR: $src not found\n"; return 0; }
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = "$src/$file";
        $dstPath = "$dst/$file";
        if (is_dir($srcPath)) {
            $count += rcopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
            $count++;
        }
    }
    closedir($dir);
    return $count;
}

// PHP-native chmod recursive
function rchmod($path, $perm = 0755) {
    if (is_file($path)) { chmod($path, $perm); return; }
    if (!is_dir($path)) return;
    chmod($path, $perm);
    $dir = opendir($path);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        rchmod("$path/$file", $perm);
    }
    closedir($dir);
}

switch ($action) {
    case 'info':
        echo "PHP: " . phpversion() . "\n";
        echo "shell_exec: " . (function_exists('shell_exec') ? 'YES' : 'NO') . "\n";
        $disabled = ini_get('disable_functions');
        echo "disabled functions: " . ($disabled ?: 'none') . "\n\n";
        echo "Repo exists: " . (is_dir($repo) ? 'YES' : 'NO') . "\n";
        echo "Backend exists: " . (is_dir("$repo/backend") ? 'YES' : 'NO') . "\n";
        echo "Vendor exists: " . (is_dir("$repo/backend/vendor") ? 'YES' : 'NO') . "\n";
        echo "Frontend dist: " . (is_dir("$repo/frontend/dist") ? 'YES' : 'NO') . "\n\n";
        echo "Steps:\n";
        echo "1. ?action=copy      - Copy backend to public_html\n";
        echo "2. ?action=env       - Create .env file\n";
        echo "3. ?action=setup     - Key + migrate + seed\n";
        echo "4. ?action=frontend  - Copy frontend files\n";
        echo "5. DELETE this file!\n";
        break;

    case 'copy':
        echo "Copying backend files to public_html (PHP native)...\n";
        echo "(This may take a minute for vendor/...)\n\n";
        flush();

        // Copy all backend folders/files
        $src = "$repo/backend";
        $skip = ['.', '..', '.git', '.env', 'node_modules'];
        $dir = opendir($src);
        $totalFiles = 0;
        while (($item = readdir($dir)) !== false) {
            if (in_array($item, $skip)) continue;
            $srcPath = "$src/$item";
            $dstPath = "$html/$item";
            if (is_dir($srcPath)) {
                $n = rcopy($srcPath, $dstPath);
                echo "  [DIR]  $item ($n files)\n";
                flush();
                $totalFiles += $n;
            } else {
                copy($srcPath, $dstPath);
                echo "  [FILE] $item\n";
                $totalFiles++;
            }
        }
        closedir($dir);

        // Copy .htaccess
        if (file_exists("$src/.htaccess")) {
            copy("$src/.htaccess", "$html/.htaccess");
            echo "  [FILE] .htaccess\n";
        }

        // Set permissions
        echo "\nSetting permissions...\n";
        if (is_dir("$html/storage")) rchmod("$html/storage", 0777);
        if (is_dir("$html/bootstrap/cache")) rchmod("$html/bootstrap/cache", 0777);

        echo "\nTotal: $totalFiles files copied\n";
        echo "DONE! Now run ?action=env&dbpass=YOUR_DB_PASSWORD\n";
        break;

    case 'env':
        $dbpass = $_GET['dbpass'] ?? 'CHANGE_ME';
        $envContent = "APP_NAME=Aksamedia
APP_ENV=production
APP_KEY=
APP_DEBUG=true
APP_URL=http://aksa.id

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
DB_PASSWORD=$dbpass

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
        echo ".env created at $html/.env\n";
        echo "DB_PASSWORD set to: $dbpass\n";
        echo "\nNow run ?action=setup\n";
        break;

    case 'setup':
        // Run artisan commands via PHP include  
        $step = $_GET['step'] ?? 'key';
        echo "--- Setup Step: $step ---\n";
        flush();
        ob_flush();

        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        chdir($html);
        $_SERVER['argv'] = ['artisan'];
        
        if (!file_exists("$html/vendor/autoload.php")) {
            echo "ERROR: vendor/autoload.php not found!\n";
            break;
        }

        echo "Loading autoload...\n"; flush(); ob_flush();
        require "$html/vendor/autoload.php";
        
        echo "Loading app...\n"; flush(); ob_flush();
        $app = require_once "$html/bootstrap/app.php";
        
        echo "Making kernel...\n"; flush(); ob_flush();
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        
        echo "Laravel loaded OK!\n\n"; flush(); ob_flush();

        switch ($step) {
            case 'key':
                echo "--- Generate Key ---\n";
                $kernel->call('key:generate', ['--force' => true]);
                echo $kernel->output();
                echo "\nNext: ?action=setup&step=migrate\n";
                break;
            case 'migrate':
                echo "--- Run Migrations ---\n";
                $kernel->call('migrate', ['--force' => true]);
                echo $kernel->output();
                echo "\nNext: ?action=setup&step=seed\n";
                break;
            case 'seed':
                echo "--- Run Seeders ---\n";
                $kernel->call('db:seed', ['--force' => true]);
                echo $kernel->output();
                echo "\nNext: ?action=setup&step=link\n";
                break;
            case 'link':
                echo "--- Storage Link ---\n";
                $target = "$html/storage/app/public";
                $link = "$html/public/storage";
                if (!file_exists($link)) {
                    @symlink($target, $link) ? print("Created\n") : print("Symlink failed\n");
                } else {
                    echo "Already exists\n";
                }
                echo "--- Cache ---\n";
                $kernel->call('config:cache');
                echo $kernel->output();
                $kernel->call('route:cache');
                echo $kernel->output();
                echo "\n=== ALL SETUP DONE! ===\n";
                echo "Test: http://aksa.id/api/login\n";
                echo "Next: ?action=frontend\n";
                break;
        }
        break;

    case 'frontend':
        echo "Copying frontend dist files...\n";
        $distSrc = "$repo/frontend/dist";
        $distDst = "$html/public/app";

        if (!is_dir($distSrc)) {
            echo "ERROR: $distSrc not found!\n";
            echo "Make sure frontend/dist was pushed to git.\n";
            break;
        }

        $n = rcopy($distSrc, $distDst);
        echo "$n files copied to public/app/\n";

        // Create .htaccess for SPA routing
        $htaccess = '<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /app/
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /app/index.html [L]
</IfModule>';
        file_put_contents("$distDst/.htaccess", $htaccess);
        echo ".htaccess for SPA created\n";
        echo "\nDONE! Frontend at http://aksa.id/app\n";
        break;

    default:
        echo "Unknown action: $action\n";
}
echo "</pre>";
