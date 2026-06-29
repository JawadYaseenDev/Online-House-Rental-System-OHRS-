<?php
/**
 * Authentication Helpers — OHRS
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Check if logged in ───────────────────────────────────────
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

// ── Check role ───────────────────────────────────────────────
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function is_customer(): bool
{
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'customer';
}

// ── Require login — redirect if not authenticated ────────────
function require_login(string $redirect = 'login.php'): void
{
    if (!is_logged_in()) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
        header("Location: " . ROOT_URL . $redirect);
        exit;
    }
}

// ── Require admin ────────────────────────────────────────────
function require_admin(): void
{
    if (!is_admin()) {
        header("Location: " . ROOT_URL . "index.php");
        exit;
    }
}

// ── Login — set session ──────────────────────────────────────
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['user_pic']   = $user['profile_pic'];
}

// ── Logout ───────────────────────────────────────────────────
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ── Current user shorthand ────────────────────────────────────
function current_user(): array
{
    return [
        'id'    => $_SESSION['user_id']    ?? 0,
        'name'  => $_SESSION['user_name']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role']  ?? '',
        'pic'   => $_SESSION['user_pic']   ?? 'default-avatar.png',
    ];
}
