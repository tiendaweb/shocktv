<?php
/**
 * Get Providers API - Retrieve active providers
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/constants.php';

$db = getDB();

try {
    $stmt = $db->prepare('
        SELECT id, name, embed_pattern, language_param, priority
        FROM providers
        WHERE active = 1
        ORDER BY priority ASC
    ');
    $stmt->execute();
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'providers' => $providers,
        'total' => count($providers),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
