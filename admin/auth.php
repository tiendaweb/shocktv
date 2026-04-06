<?php
/**
 * Admin Authentication Middleware
 */

require_once __DIR__ . '/../config/constants.php';

function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/index.php');
        exit;
    }

    // Check session timeout
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: /admin/index.php?timeout=1');
            exit;
        }
        // Refresh session timeout
        $_SESSION['login_time'] = time();
    }
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function getAdminUsername() {
    return $_SESSION['admin_username'] ?? 'unknown';
}
