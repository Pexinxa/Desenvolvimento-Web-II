<?php
// functions.php

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login($nivel = null) {
    if (!is_logged_in()) {
        header('Location: ' . url('login.php'));
        exit;
    }
    if ($nivel !== null && $_SESSION['user']['nivel'] !== $nivel) {
        http_response_code(403);
        echo "Acesso negado.";
        exit;
    }
}

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}