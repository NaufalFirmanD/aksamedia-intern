<?php
/**
 * Setup script untuk deploy Laravel di shared hosting tanpa SSH
 * HAPUS FILE INI SETELAH SETUP SELESAI!
 */

// Password protection
$SETUP_PASSWORD = 'aksamedia2026';

if (!isset($_GET['key']) || $_GET['key'] !== $SETUP_PASSWORD) {
    die('Unauthorized. Usage: setup.php?key=aksamedia2026&action=ACTION');
}

$action = $_GET['action'] ?? 'status';

// Change to Laravel root (one level up from public/)
$basePath = dirname(__DIR__);
chdir($basePath);

echo "<pre>\n";
echo "=== Laravel Hosting Setup ===\n";
echo "Base Path: $basePath\n";
echo "Action: $action\n";
echo str_repeat('=', 40) . "\n\n";

switch ($action) {
    case 'status':
        echo "PHP Version: " . phpversion() . "\n";
        echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
        echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
        echo ".env exists: " . (file_exists($basePath . '/.env') ? 'YES' : 'NO') . "\n";
        echo "vendor/ exists: " . (is_dir($basePath . '/vendor') ? 'YES' : 'NO') . "\n";
        echo "storage/ writable: " . (is_writable($basePath . '/storage') ? 'YES' : 'NO') . "\n\n";
        echo "Available actions:\n";
        echo "  ?key=$SETUP_PASSWORD&action=status     - Show status\n";
        echo "  ?key=$SETUP_PASSWORD&action=key        - Generate APP_KEY\n";
        echo "  ?key=$SETUP_PASSWORD&action=migrate    - Run migrations\n";
        echo "  ?key=$SETUP_PASSWORD&action=seed       - Run seeders\n";
        echo "  ?key=$SETUP_PASSWORD&action=storage    - Create storage link\n";
        echo "  ?key=$SETUP_PASSWORD&action=cache      - Cache config & routes\n";
        echo "  ?key=$SETUP_PASSWORD&action=all        - Run all setup steps\n";
        break;

    case 'key':
        echo runArtisan('key:generate --force');
        break;

    case 'migrate':
        echo runArtisan('migrate --force');
        break;

    case 'seed':
        echo runArtisan('db:seed --force');
        break;

    case 'storage':
        // Manual storage link creation
        $target = $basePath . '/storage/app/public';
        $link = $basePath . '/public/storage';
        if (file_exists($link)) {
            echo "Storage link already exists.\n";
        } else {
            if (symlink($target, $link)) {
                echo "Storage link created successfully.\n";
            } else {
                echo "Failed to create symlink. Creating copy instead...\n";
                // Fallback: copy directory
                if (!is_dir($link)) mkdir($link, 0755, true);
                echo "Created storage directory manually.\n";
            }
        }
        break;

    case 'cache':
        echo runArtisan('config:cache');
        echo "\n";
        echo runArtisan('route:cache');
        break;

    case 'all':
        echo "--- Step 1: Generate Key ---\n";
        echo runArtisan('key:generate --force');
        echo "\n--- Step 2: Run Migrations ---\n";
        echo runArtisan('migrate --force');
        echo "\n--- Step 3: Run Seeders ---\n";
        echo runArtisan('db:seed --force');
        echo "\n--- Step 4: Storage Link ---\n";
        $target = $basePath . '/storage/app/public';
        $link = $basePath . '/public/storage';
        if (!file_exists($link)) {
            symlink($target, $link) ? print("Storage link created.\n") : print("Symlink failed.\n");
        } else {
            echo "Storage link exists.\n";
        }
        echo "\n--- Step 5: Cache Config ---\n";
        echo runArtisan('config:cache');
        echo "\n--- Step 6: Cache Routes ---\n";
        echo runArtisan('route:cache');
        echo "\n\n=== SETUP COMPLETE! ===\n";
        echo "PENTING: Hapus file setup.php ini sekarang!\n";
        break;

    default:
        echo "Unknown action: $action\n";
}

echo "</pre>";

function runArtisan($command) {
    $output = [];
    $exitCode = 0;
    exec("php artisan $command 2>&1", $output, $exitCode);
    $result = implode("\n", $output);
    if ($exitCode !== 0) {
        $result .= "\n[Exit code: $exitCode]";
    }
    return $result . "\n";
}
