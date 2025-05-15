<?php
require_once 'functions.php';

// セッションにフォームデータがあるか確認
if (!isset($_SESSION['form'])) {
    header('Location: form.php');
    exit;
}

$form = $_SESSION['form'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>確認画面</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h1>入力内容の確認</h1>

<form action="send_contact.php" method="post" enctype="multipart/form-data">
  <dl>
    <dt>カテゴリ</dt>
    <dd><?= h($form['category']) ?></dd>

    <dt>チーム名</dt>
    <dd><?= h($form['team']) ?></dd>

    <dt>名前</dt>
    <dd><?= h($form['name']) ?></dd>

    <dt>電話番号</dt>
    <dd><?= h($form['phone']) ?></dd>

    <dt>メールアドレス</dt>
    <dd><?= h($form['email']) ?></dd>

    <dt>本文</dt>
    <dd><?= nl2br(h($form['message'])) ?></dd>

    <dt>ファイル添付</dt>
    <dd><?= isset($_SESSION['file']['name']) && $_SESSION['file']['name'] !== '' ? h($_SESSION['file']['name']) : 'なし' ?></dd>
  </dl>

  <button type="submit" name="action" value="send">送信する</button>
  <button type="submit" name="action" value="back">修正する</button>
</form>

</body>
</html>
