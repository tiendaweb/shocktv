<?php
/**
 * Global Constants
 */

define('APP_NAME', 'ShockTV');
define('APP_VERSION', '2.0.0');
define('BASE_URL', rtrim(dirname($_SERVER['PHP_SELF']), '/'));

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Admin paths
define('ADMIN_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/admin');

// Load required config files
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/api.php';
