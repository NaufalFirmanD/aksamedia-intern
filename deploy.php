<?php
/**
 * Deploy script v3 - Raw PHP/PDO, NO Laravel bootstrap needed
 * HAPUS FILE INI SETELAH DEPLOY SELESAI!
 */
set_time_limit(300);
ini_set('memory_limit', '256M');
ini_set('display_errors', '1');
error_reporting(E_ALL);

$KEY = 'aksamedia2026';
if (($_GET['key'] ?? '') !== $KEY) die('Unauthorized');
$action = $_GET['action'] ?? 'info';

echo "<pre style='font-family:monospace;background:#1a1a2e;color:#0f0;padding:20px;font-size:14px;'>\n";
echo "=== DEPLOY v3 (Raw PHP) ===\n";
echo "Action: $action\n\n";

$home = '/home/aksq9845';
$repo = "$home/aksamedia-intern";
$html = "$home/public_html";

$dbHost = 'localhost';
$dbName = 'aksq9845_aksa_intern';
$dbUser = 'aksq9845_admin';
$dbPass = $_GET['dbpass'] ?? 'Amengazzahra19?';

function rcopy($src, $dst) {
    $count = 0;
    if (!is_dir($src)) return 0;
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $s = "$src/$file"; $d = "$dst/$file";
        if (is_dir($s)) { $count += rcopy($s, $d); }
        else { copy($s, $d); $count++; }
    }
    closedir($dir);
    return $count;
}

function rchmod($path, $perm = 0755) {
    if (is_file($path)) { chmod($path, $perm); return; }
    if (!is_dir($path)) return;
    chmod($path, $perm);
    $dir = opendir($path);
    while (($f = readdir($dir)) !== false) {
        if ($f === '.' || $f === '..') continue;
        rchmod("$path/$f", $perm);
    }
    closedir($dir);
}

function uuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

switch ($action) {
    case 'info':
        echo "PHP: " . phpversion() . "\n";
        echo "Repo: " . (is_dir($repo) ? 'YES' : 'NO') . "\n";
        echo "Backend: " . (is_dir("$repo/backend") ? 'YES' : 'NO') . "\n";
        echo "Vendor: " . (is_dir("$repo/backend/vendor") ? 'YES' : 'NO') . "\n";
        echo "Dist: " . (is_dir("$repo/frontend/dist") ? 'YES' : 'NO') . "\n\n";
        // Test DB
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            echo "DB connection: OK\n";
        } catch (Exception $e) {
            echo "DB connection: FAILED - " . $e->getMessage() . "\n";
        }
        echo "\nSteps:\n";
        echo "1. ?action=clean     - Clean public_html\n";
        echo "2. ?action=copy      - Copy backend files\n";
        echo "3. ?action=env       - Create .env + APP_KEY\n";
        echo "4. ?action=migrate   - Create DB tables\n";
        echo "5. ?action=seed      - Insert seed data\n";
        echo "6. ?action=frontend  - Copy frontend\n";
        echo "7. DELETE this file!\n";
        break;

    case 'clean':
        echo "Cleaning public_html (keeping deploy.php, cgi-bin)...\n";
        $keep = ['deploy.php', 'cgi-bin', '.', '..'];
        $dir = opendir($html);
        $deleted = 0;
        while (($f = readdir($dir)) !== false) {
            if (in_array($f, $keep)) continue;
            $path = "$html/$f";
            if (is_dir($path)) {
                // recursive delete directory
                $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($files as $file) {
                    $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
                    $deleted++;
                }
                rmdir($path);
                echo "  Deleted dir: $f\n";
            } else {
                unlink($path);
                echo "  Deleted: $f\n";
            }
            $deleted++;
        }
        closedir($dir);
        echo "Cleaned $deleted items.\n";
        echo "DONE! Now run ?action=copy\n";
        break;

    case 'copy':
        echo "Copying backend to public_html...\n"; flush();
        $src = "$repo/backend";
        $skip = ['.', '..', '.git', '.env', 'node_modules'];
        $dir = opendir($src);
        $total = 0;
        while (($item = readdir($dir)) !== false) {
            if (in_array($item, $skip)) continue;
            $s = "$src/$item"; $d = "$html/$item";
            if (is_dir($s)) {
                $n = rcopy($s, $d);
                echo "  [DIR]  $item ($n files)\n"; flush();
                $total += $n;
            } else {
                copy($s, $d); echo "  [FILE] $item\n";
                $total++;
            }
        }
        closedir($dir);
        if (file_exists("$src/.htaccess")) {
            copy("$src/.htaccess", "$html/.htaccess");
            echo "  [FILE] .htaccess\n";
        }
        echo "\nSetting permissions...\n";
        if (is_dir("$html/storage")) rchmod("$html/storage", 0777);
        if (is_dir("$html/bootstrap/cache")) rchmod("$html/bootstrap/cache", 0777);
        echo "Total: $total files\n";
        echo "DONE! Now run ?action=env\n";
        break;

    case 'env':
        // Generate APP_KEY (base64 32 bytes)
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $env = "APP_NAME=Aksamedia
APP_ENV=production
APP_KEY=$appKey
APP_DEBUG=true
APP_URL=http://aksa.id

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=$dbHost
DB_PORT=3306
DB_DATABASE=$dbName
DB_USERNAME=$dbUser
DB_PASSWORD=$dbPass

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file
CACHE_PREFIX=
";
        file_put_contents("$html/.env", $env);
        echo ".env created!\n";
        echo "APP_KEY: $appKey\n";
        echo "DONE! Now run ?action=migrate\n";
        break;

    case 'migrate':
        echo "Creating tables via raw SQL...\n";
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Drop existing
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
            foreach (['employees','divisions','personal_access_tokens','failed_jobs','job_batches','jobs','cache_locks','cache','sessions','password_reset_tokens','users','migrations'] as $t) {
                $pdo->exec("DROP TABLE IF EXISTS `$t`");
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            echo "Dropped old tables.\n";

            // users
            $pdo->exec("CREATE TABLE `users` (
                `id` char(36) NOT NULL PRIMARY KEY,
                `name` varchar(255) NOT NULL,
                `username` varchar(255) NOT NULL UNIQUE,
                `phone` varchar(255) NULL,
                `email` varchar(255) NOT NULL UNIQUE,
                `email_verified_at` timestamp NULL,
                `password` varchar(255) NOT NULL,
                `remember_token` varchar(100) NULL,
                `created_at` timestamp NULL,
                `updated_at` timestamp NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: users\n";

            // password_reset_tokens
            $pdo->exec("CREATE TABLE `password_reset_tokens` (
                `email` varchar(255) NOT NULL PRIMARY KEY,
                `token` varchar(255) NOT NULL,
                `created_at` timestamp NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: password_reset_tokens\n";

            // sessions
            $pdo->exec("CREATE TABLE `sessions` (
                `id` varchar(255) NOT NULL PRIMARY KEY,
                `user_id` char(36) NULL,
                `ip_address` varchar(45) NULL,
                `user_agent` text NULL,
                `payload` longtext NOT NULL,
                `last_activity` int NOT NULL,
                INDEX `sessions_user_id_index` (`user_id`),
                INDEX `sessions_last_activity_index` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: sessions\n";

            // cache
            $pdo->exec("CREATE TABLE `cache` (
                `key` varchar(255) NOT NULL PRIMARY KEY,
                `value` mediumtext NOT NULL,
                `expiration` int NOT NULL,
                INDEX `cache_expiration_index` (`expiration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: cache\n";

            // cache_locks
            $pdo->exec("CREATE TABLE `cache_locks` (
                `key` varchar(255) NOT NULL PRIMARY KEY,
                `owner` varchar(255) NOT NULL,
                `expiration` int NOT NULL,
                INDEX `cache_locks_expiration_index` (`expiration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: cache_locks\n";

            // personal_access_tokens
            $pdo->exec("CREATE TABLE `personal_access_tokens` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `tokenable_type` varchar(255) NOT NULL,
                `tokenable_id` char(36) NOT NULL,
                `name` text NOT NULL,
                `token` varchar(64) NOT NULL UNIQUE,
                `abilities` text NULL,
                `last_used_at` timestamp NULL,
                `expires_at` timestamp NULL,
                `created_at` timestamp NULL,
                `updated_at` timestamp NULL,
                INDEX `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: personal_access_tokens\n";

            // divisions
            $pdo->exec("CREATE TABLE `divisions` (
                `id` char(36) NOT NULL PRIMARY KEY,
                `name` varchar(255) NOT NULL,
                `created_at` timestamp NULL,
                `updated_at` timestamp NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: divisions\n";

            // employees
            $pdo->exec("CREATE TABLE `employees` (
                `id` char(36) NOT NULL PRIMARY KEY,
                `image` varchar(255) NULL,
                `name` varchar(255) NOT NULL,
                `phone` varchar(255) NOT NULL,
                `division_id` char(36) NOT NULL,
                `position` varchar(255) NOT NULL,
                `created_at` timestamp NULL,
                `updated_at` timestamp NULL,
                CONSTRAINT `employees_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "  Created: employees\n";

            // migrations table
            $pdo->exec("CREATE TABLE `migrations` (
                `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `migration` varchar(255) NOT NULL,
                `batch` int NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $migrations = [
                '0001_01_01_000000_create_users_table',
                '0001_01_01_000001_create_cache_table',
                '0001_01_01_000002_create_jobs_table',
                '2026_02_09_183131_create_personal_access_tokens_table',
                '2026_02_09_183200_create_divisions_table',
                '2026_02_09_183300_create_employees_table',
            ];
            foreach ($migrations as $m) {
                $pdo->exec("INSERT INTO `migrations` (`migration`, `batch`) VALUES ('$m', 1)");
            }
            echo "  Created: migrations\n";

            echo "\nAll tables created!\n";
            echo "DONE! Now run ?action=seed\n";
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
        break;

    case 'seed':
        echo "Seeding data via raw SQL...\n";
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $now = date('Y-m-d H:i:s');

            // Admin user
            $adminId = uuid();
            $hash = password_hash('pastibisa', PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT INTO `users` (`id`,`name`,`username`,`phone`,`email`,`password`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$adminId, 'Admin Aksamedia', 'admin', '08123456789', 'admin@aksamedia.co.id', $hash, $now, $now]);
            echo "  Admin user created (admin/pastibisa)\n";

            // Divisions
            $divNames = ['Mobile Apps','QA','Full Stack','Backend','Frontend','UI/UX Designer'];
            $divIds = [];
            $stmt = $pdo->prepare("INSERT INTO `divisions` (`id`,`name`,`created_at`,`updated_at`) VALUES (?,?,?,?)");
            foreach ($divNames as $name) {
                $id = uuid();
                $stmt->execute([$id, $name, $now, $now]);
                $divIds[] = $id;
                echo "  Division: $name\n";
            }

            // Employees
            $employees = [
                ['Budi Santoso','081234567001','Senior Developer'],
                ['Siti Rahayu','081234567002','Junior Developer'],
                ['Ahmad Fauzi','081234567003','QA Engineer'],
                ['Dewi Lestari','081234567004','UI Designer'],
                ['Andi Prasetyo','081234567005','Backend Developer'],
                ['Rina Wati','081234567006','Frontend Developer'],
                ['Joko Widodo','081234567007','Project Manager'],
                ['Maya Sari','081234567008','Full Stack Developer'],
                ['Rudi Hermawan','081234567009','DevOps Engineer'],
                ['Lina Kusuma','081234567010','UX Researcher'],
                ['Doni Pratama','081234567011','Mobile Developer'],
                ['Fitri Handayani','081234567012','Tech Lead'],
            ];
            $stmt = $pdo->prepare("INSERT INTO `employees` (`id`,`image`,`name`,`phone`,`division_id`,`position`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($employees as $i => $emp) {
                $firstName = explode(' ', $emp[0])[0];
                $image = "https://api.dicebear.com/7.x/avataaars/svg?seed=$firstName";
                $divId = $divIds[$i % count($divIds)];
                $stmt->execute([uuid(), $image, $emp[0], $emp[1], $divId, $emp[2], $now, $now]);
                echo "  Employee: {$emp[0]}\n";
            }

            echo "\nSeeding complete!\n";
            echo "DONE! Now run ?action=frontend\n";
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
        break;

    case 'frontend':
        echo "Copying frontend dist...\n";
        $distSrc = "$repo/frontend/dist";
        $distDst = "$html/public/app";
        if (!is_dir($distSrc)) {
            echo "ERROR: $distSrc not found!\n"; break;
        }
        $n = rcopy($distSrc, $distDst);
        $htaccess = '<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /app/
    RewriteRule ^index\\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /app/index.html [L]
</IfModule>';
        file_put_contents("$distDst/.htaccess", $htaccess);
        echo "$n files copied.\n";
        echo "DONE! Frontend at http://aksa.id/app\n";
        echo "\nNow DELETE this deploy.php file!\n";
        break;

    default:
        echo "Unknown action\n";
}
echo "</pre>";
