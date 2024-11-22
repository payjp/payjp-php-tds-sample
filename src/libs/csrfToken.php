<?php
declare(strict_types=1);

function generateCsrfToken(): string
{
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;
    return $csrfToken;
}

function verifyCsrfToken(): void
{
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        throw new RuntimeException('CSRF validation failed.');
    }
}