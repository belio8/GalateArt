<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'avatar_url' => $_SESSION['avatar_url'] ?? null,
    ];
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(?string $role = null): void
{
    $user = current_user();
    if (!$user) {
        header('Location: landing.php?auth=login');
        exit;
    }

    if ($role !== null && ($user['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function active_user_home(): string
{
    $role = $_SESSION['role'] ?? null;
    if ($role === 'artist') {
        return 'landing-artist.php';
    }
    if ($role === 'regular') {
        return 'landing-reguler.php';
    }
    if ($role === 'admin') {
        return 'admin.php';
    }

    return 'landing.php';
}
function active_user_profile(): string
{
    $role = $_SESSION['role'] ?? null;
    if ($role === 'artist') {
        return 'artist-profile.php';
    }
    if ($role === 'regular') {
        return 'profile.php';
    }
    if ($role === 'admin') {
        return 'admin.php';
    }

    return 'landing.php';
}