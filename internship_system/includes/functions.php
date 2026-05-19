<?php
// =========================================
// SHARED HELPER FUNCTIONS
// =========================================

/**
 * Sanitize input to prevent XSS
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Flash message (store in session)
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message as styled alert
 */
function showFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'];
        $styles = [
            'success' => 'background:#f0fdf4;color:#166534;border-left:4px solid #22c55e;',
            'error'   => 'background:#fef2f2;color:#991b1b;border-left:4px solid #B57B66;',
            'warning' => 'background:#fffbeb;color:#92400e;border-left:4px solid #f59e0b;',
            'info'    => 'background:#eff6ff;color:#1e40af;border-left:4px solid #3b82f6;',
        ];
        $style = $styles[$type] ?? $styles['info'];
        $icon  = match($type) {
            'success' => 'bi-check-circle-fill',
            'error'   => 'bi-exclamation-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            default   => 'bi-info-circle-fill',
        };
        echo "<div style='padding:12px 16px;border-radius:10px;font-size:.875rem;margin-bottom:16px;{$style}'>
                <i class='bi {$icon} me-2'></i>{$flash['message']}
              </div>";
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user role
 */
function userRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if current user has a specific role
 */
function hasRole($role) {
    return ($_SESSION['role'] ?? '') === $role;
}

/**
 * Check if current user is admin or lecturer (school side)
 */
function isSchool() {
    return in_array($_SESSION['role'] ?? '', ['admin', 'lecturer']);
}

/**
 * Require login — redirect if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $base = getBaseUrl();
        redirect($base . '/auth/login.php');
    }
}

/**
 * Require a specific role — redirect to dashboard if not authorized
 */
function requireRole($roles) {
    requireLogin();
    if (!is_array($roles)) $roles = [$roles];
    if (!in_array($_SESSION['role'] ?? '', $roles)) {
        setFlash('error', 'Bạn không có quyền truy cập trang này.');
        redirect(getBaseUrl() . '/index.php');
    }
}

/**
 * Get base URL to /internship_system using SCRIPT_NAME (URL path, not filesystem)
 */
function getBaseUrl() {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $pos    = strpos($script, '/internship_system');
    return ($pos !== false) ? substr($script, 0, $pos) . '/internship_system' : '/internship_system';
}
