<?php

session_start();

if (isset($_SESSION["name"])) {
  $errorMessage = "ログアウトしました。";
} else {
  $errorMessage = "セッションがタイムアウトしました。";
}
// セッション用Cookieの破棄
setcookie(session_name(), '', 1);
// セッションファイルの破棄
session_destroy();
// ログアウト完了後に /login.php に遷移
//header('Location: index.php');

?>

<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
  <link rel="stylesheet" href="css/common.css" type="text/css">
  <html lang="ja">
  <title>ログアウト - EazyMemo</title>
</head>

<body>
  <div class="text-center mb-4">
    <img class="" src="picture/EazyMemo_logo.png" alt="" width="300" height="100">
  </div>

  <div class="container">
    <div class="div-regulate">
      <p class="text-center"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></p>
      <button class="btn btn-outline-primary btn-block" type="submit" onclick="location.href='index.php'">ログイン画面に戻る</button>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
</body>

</html>