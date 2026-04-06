<?php
/**
 * Database Configuration - SQLite
 * IMPORTANTE: SIEMPRE ESPAÑOL en consultas a TMDB
 */

define('DB_PATH', __DIR__ . '/../database/shocktv.db');

function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            initDatabase();
        } catch (PDOException $e) {
            die('Database Error: ' . $e->getMessage());
        }
    }

    return $pdo;
}

function initDatabase() {
    $db = new PDO('sqlite:' . DB_PATH);
    $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
    $db->exec($sql);

    // Insert default providers
    $providers = [
        ['Latino VIP 1', 'https://vidsrc.me/embed/{type}?tmdb={id}&sea={season}&epi={episode}&lang=es', 'es-MX', 1, 1],
        ['Latino VIP 2', 'https://vidsrc.xyz/embed/{type}?tmdb={id}&sea={season}&epi={episode}&lang=es-LA', 'es-LA', 1, 2],
        ['Server XYZ', 'https://vidsrc.to/embed/{type}/{id}/{season}/{episode}', 'es', 1, 3],
        ['MultiEmbed', 'https://multiembed.mov/?video_id={id}&tmdb=1', 'es', 1, 4],
    ];

    $stmt = $db->prepare('SELECT COUNT(*) FROM providers');
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        $insert = $db->prepare('INSERT INTO providers (name, embed_pattern, language_param, active, priority) VALUES (?, ?, ?, ?, ?)');
        foreach ($providers as $provider) {
            $insert->execute($provider);
        }
    }

    // Insert default admin user (admin / admin123)
    $stmt = $db->prepare('SELECT COUNT(*) FROM admin_users');
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        $insert = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
        $insert->execute(['admin', password_hash('admin123', PASSWORD_BCRYPT)]);
    }
}

function executeQuery($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('Query Error: ' . $e->getMessage());
        return false;
    }
}
