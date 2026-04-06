#!/usr/bin/env php
<?php
/**
 * ShockTV Interactive Installation Script
 * Run: php install.php
 */

// ANSI Colors
define('GREEN', "\033[92m");
define('RED', "\033[91m");
define('YELLOW', "\033[93m");
define('BLUE', "\033[94m");
define('RESET', "\033[0m");

function println($text, $color = 'RESET') {
    echo constant($color) . $text . constant('RESET') . "\n";
}

function section($title) {
    echo "\n";
    println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", 'BLUE');
    println("  " . strtoupper($title), 'BLUE');
    println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", 'BLUE');
    echo "\n";
}

function ask($question) {
    echo $question . " > ";
    return trim(fgets(STDIN));
}

function check($condition, $message) {
    if ($condition) {
        println("✓ " . $message, 'GREEN');
    } else {
        println("✗ " . $message, 'RED');
        return false;
    }
    return true;
}

section("ShockTV v2.0 Installation");

println("Welcome to ShockTV installation!", 'YELLOW');

// Validar requisitos
section("Checking Requirements");

$requirements = [
    'PHP >= 7.4' => version_compare(PHP_VERSION, '7.4', '>='),
    'PDO SQLite extension' => extension_loaded('pdo_sqlite'),
    'cURL extension' => extension_loaded('curl'),
    'Writable database directory' => is_writable(__DIR__ . '/database'),
];

$allOk = true;
foreach ($requirements as $req => $status) {
    if (!check($status, $req)) {
        $allOk = false;
    }
}

if (!$allOk) {
    println("\n❌ Some requirements are not met. Please install them first.", 'RED');
    exit(1);
}

// Configuración de admin
section("Admin User Configuration");

println("Create your admin account (default: admin / admin123)", 'YELLOW');

do {
    $username = ask("Admin username (default: admin)");
    if (empty($username)) $username = "admin";
} while (strlen($username) < 3);

do {
    $password = ask("Admin password (default: admin123)");
    if (empty($password)) $password = "admin123";
} while (strlen($password) < 6);

// Inicializar BD
section("Initializing Database");

try {
    require_once __DIR__ . '/config/db.php';

    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tablas
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $db->exec($sql);
    check(true, "Database schema created");

    // Insertar usuario admin personalizado
    $stmt = $db->prepare('DELETE FROM admin_users');
    $stmt->execute();

    $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT)]);
    check(true, "Admin user created: $username");

    // Insertar proveedores por defecto
    $providers = [
        ['Latino VIP 1', 'https://vidsrc.me/embed/{type}?tmdb={id}&sea={season}&epi={episode}&lang=es', 'es-MX', 1, 1],
        ['Latino VIP 2', 'https://vidsrc.xyz/embed/{type}?tmdb={id}&sea={season}&epi={episode}&lang=es-LA', 'es-LA', 1, 2],
        ['Server XYZ', 'https://vidsrc.to/embed/{type}/{id}/{season}/{episode}', 'es', 1, 3],
        ['MultiEmbed', 'https://multiembed.mov/?video_id={id}&tmdb=1', 'es', 1, 4],
    ];

    $insert = $db->prepare('INSERT INTO providers (name, embed_pattern, language_param, active, priority) VALUES (?, ?, ?, ?, ?)');
    foreach ($providers as $provider) {
        $insert->execute($provider);
    }
    check(true, "Default providers added (4 servers)");

} catch (Exception $e) {
    check(false, "Database initialization: " . $e->getMessage());
    exit(1);
}

// Resumen
section("Installation Summary");

println("✓ All components installed successfully!", 'GREEN');

println("\n📍 Next Steps:", 'YELLOW');
println("1. Start your web server (Apache or Nginx)", 'YELLOW');
println("2. Access admin panel at: http://localhost/admin/", 'YELLOW');
println("3. Login with:", 'YELLOW');
println("   Username: $username", 'YELLOW');
println("   Password: $password", 'YELLOW');
println("\n4. Add movies/series from admin panel", 'YELLOW');
println("5. Manage providers in the providers section", 'YELLOW');

println("\n📚 Documentation: README.md", 'BLUE');
println("🐛 Need help? Check the Troubleshooting section", 'BLUE');

section("Installation Complete");

println("🎉 ShockTV v2.0 is ready to use!", 'GREEN');
println("Visit http://localhost/ to get started!", 'GREEN');

echo "\n";
