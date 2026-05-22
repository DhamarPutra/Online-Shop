<?php
session_start();

function generateCSRFToken()
{
    // Jika token belum ada, buat token baru
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token)
{
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Fungsi untuk mereset token (jika perlu)
function resetCSRFToken()
{
    unset($_SESSION['csrf_token']);
}
?>