<?php
/**
 * Database Initialization Script
 * Run once to initialize the SQLite database
 */

require_once __DIR__ . '/config/db.php';

echo "🚀 Initializing ShockTV Database...\n\n";

try {
    $db = getDB();
    echo "✅ Database connection successful\n";
    echo "✅ Tables created\n";
    echo "✅ Default admin user created (admin@shocktv.com / admin@shocktv.com)\n";
    echo "✅ Default providers added\n\n";

    // Verificar
    $userCount = $db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    $provCount = $db->query('SELECT COUNT(*) FROM providers')->fetchColumn();

    echo "📊 Statistics:\n";
    echo "   - Admin users: $userCount\n";
    echo "   - Providers: $provCount\n\n";

    echo "🎉 Database initialization completed!\n";
    echo "📍 You can now access:\n";
    echo "   - Frontend: http://localhost/\n";
    echo "   - Admin: http://localhost/admin/\n";
    echo "   - Default credentials: admin@shocktv.com / admin@shocktv.com\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
