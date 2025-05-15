<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();

// .env を読み込み
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// HTMLエスケープ
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// reCAPTCHA 検証
function verify_recaptcha($response_token) {
    $secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
    if (empty($secret) || empty($response_token)) {
        return false;
    }

    $res = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secret) . '&response=' . urlencode($response_token));
    $result = json_decode($res, true);

    return $result['success'] ?? false;
}
