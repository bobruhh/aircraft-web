<?php
require_once __DIR__.'/config.php';

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

function require_role($roles) {
    require_login();
    $u = current_user();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!$u || !in_array($u['role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}
