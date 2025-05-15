<?php
require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form.php');
    exit;
}

if ($_POST['action'] === 'back') {
    header('Location: form.php');
    exit;
}

$form = $_SESSION['form'] ?? null;
$file = $_SESSION['file'] ?? null;

if (!$form) {
    header('Location: form.php');
    exit;
}

// カテゴリに応じた宛先
$to = match ($form['category']) {
    'u12' => 'u12@xn--fc-v21d550bkkh.com',
    'u11' => 'u11@xn--fc-v21d550bkkh.com',
    'u10' => 'u10@xn--fc-v21d550bkkh.com',
    'u9'  => 'u9@xn--fc-v21d550bkkh.com',
    'u8'  => 'u8@xn--fc-v21d550bkkh.com',
    'u7'  => 'u7@xn--fc-v21d550bkkh.com',
    'u6'  => 'u6@xn--fc-v21d550bkkh.com',
    default => 'info@xn--fc-v21d550bkkh.com'
};

// SMTP設定（.envから取得）
$host = $_ENV['SMTP_HOST'] ?? '';
$port = (int) ($_ENV['SMTP_PORT'] ?? 465);
$username = $_ENV['SMTP_USER'] ?? '';
$password = $_ENV['SMTP_PASS'] ?? '';
$from = $_ENV['SMTP_FROM'] ?? '';
$fromName = $_ENV['SMTP_NAME'] ?? '';


try {
    // アップロード処理
    $uploadedPath = '';
    if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
        $uploadedPath = $uploadDir . uniqid('file_') . '_' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $uploadedPath)) {
            throw new Exception('ファイルのアップロードに失敗しました。');
        }
    }

    // 管理者向けメール
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($from, $fromName);
    $mail->addAddress($to);
    $mail->Subject = '【お問い合わせ】' . $form['name'] . ' 様';

    $body = <<<EOT
チーム名: {$form['team']}
名前: {$form['name']}
電話番号: {$form['phone']}
メールアドレス: {$form['email']}
カテゴリ: {$form['category']}

本文:
{$form['message']}
EOT;

    $mail->Body = $body;

    if ($uploadedPath && file_exists($uploadedPath)) {
        $mail->addAttachment($uploadedPath);
    }

    $mail->send();

    // 自動返信メール
    $reply = new PHPMailer(true);
    $reply->isSMTP();
    $reply->Host = $host;
    $reply->SMTPAuth = true;
    $reply->Username = $username;
    $reply->Password = $password;
    $reply->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $reply->Port = 465;
    $reply->CharSet = 'UTF-8';

    $reply->setFrom($from, $fromName);
    $reply->addAddress($form['email'], $form['name']);
    $reply->Subject = '【自動返信】お問い合わせありがとうございました';

    $template = file_get_contents(__DIR__ . '/mail_templates/user_mail.html');
    $template = str_replace(
        ['{{name}}', '{{message}}'],
        [h($form['name']), nl2br(h($form['message']))],
        $template
    );

    $reply->isHTML(true);
    $reply->Body = $template;
    $reply->send();

    // 添付ファイル削除（保存不要な場合）
    if ($uploadedPath && file_exists($uploadedPath)) {
        unlink($uploadedPath);
    }

    unset($_SESSION['form'], $_SESSION['file']);
    header('Location: thanks.php');
    exit;
} catch (Exception $e) {
    echo '送信に失敗しました：' . h($e->getMessage());
}
