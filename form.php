<?php
require_once 'functions.php';

$errors = [];
$values = [
    'category' => '',
    'team' => '',
    'name' => '',
    'phone' => '',
    'email' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = h(trim($_POST[$key] ?? ''));
    }

    // バリデーション
    if ($values['category'] === '') {
        $errors['category'] = 'カテゴリを選択してください。';
    }
    if ($values['team'] === '') {
        $errors['team'] = 'チーム名を入力してください。';
    }
    if ($values['name'] === '') {
        $errors['name'] = '名前を入力してください。';
    }
    if (!preg_match('/^\d{10,11}$/', $values['phone'])) {
        $errors['phone'] = '電話番号を正しく入力してください。';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = '正しいメールアドレスを入力してください。';
    }
    if ($values['message'] === '') {
        $errors['message'] = '本文を入力してください。';
    }

    // reCAPTCHAチェック
    $recaptcha = $_POST['g-recaptcha-response'] ?? '';
    if (!verify_recaptcha($recaptcha)) {
        $errors['recaptcha'] = 'reCAPTCHA認証に失敗しました。';
    }

    // エラーがなければセッションに保存し確認画面へ
    if (empty($errors)) {
        $_SESSION['form'] = $values;
        $_SESSION['file'] = $_FILES['attachment'] ?? null;
        header('Location: confirm.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お問い合わせフォーム</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="g-recaptcha" data-sitekey="<?= h($_ENV['RECAPTCHA_SITE_KEY'] ?? '') ?>"></div>

</head>
<body>

<h1>お問い合わせ</h1>

<form action="" method="post" enctype="multipart/form-data">
    <label>カテゴリ
        <select name="category">
            <option value="">選択してください</option>
            <option value="u12" <?= $values['category'] === 'u12' ? 'selected' : '' ?>>U-12</option>
            <option value="u11" <?= $values['category'] === 'u11' ? 'selected' : '' ?>>U-11</option>
            <option value="u10" <?= $values['category'] === 'u10' ? 'selected' : '' ?>>U-10</option>
            <option value="u9"  <?= $values['category'] === 'u9' ? 'selected' : '' ?>>U-9</option>
            <option value="u8"  <?= $values['category'] === 'u8' ? 'selected' : '' ?>>U-8</option>
            <option value="u7"  <?= $values['category'] === 'u7' ? 'selected' : '' ?>>U-7</option>
            <option value="u6"  <?= $values['category'] === 'u6' ? 'selected' : '' ?>>U-6 バンビ</option>
            <option value="other" <?= $values['category'] === 'other' ? 'selected' : '' ?>>その他</option>
        </select>
        <div class="error"><?= $errors['category'] ?? '' ?></div>
    </label>

    <label>チーム名
        <input type="text" name="team" value="<?= $values['team'] ?>">
        <div class="error"><?= $errors['team'] ?? '' ?></div>
    </label>

    <label>名前
        <input type="text" name="name" value="<?= $values['name'] ?>">
        <div class="error"><?= $errors['name'] ?? '' ?></div>
    </label>

    <label>電話番号
        <input type="text" name="phone" value="<?= $values['phone'] ?>">
        <div class="error"><?= $errors['phone'] ?? '' ?></div>
    </label>

    <label>メールアドレス
        <input type="email" name="email" value="<?= $values['email'] ?>">
        <div class="error"><?= $errors['email'] ?? '' ?></div>
    </label>

    <label>本文
        <textarea name="message"><?= $values['message'] ?></textarea>
        <div class="error"><?= $errors['message'] ?? '' ?></div>
    </label>

    <label>ファイル添付（任意）
        <input type="file" name="attachment">
    </label>

    <div class="g-recaptcha" data-sitekey="6LfTNDsrAAAAAIOym0oz0FdZj-J_f_1NrL3cdEJF"></div>
    <div class="error"><?= $errors['recaptcha'] ?? '' ?></div>

    <button type="submit">確認画面へ</button>
</form>

</body>
</html>
