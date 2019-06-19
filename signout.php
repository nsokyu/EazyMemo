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
//header('Location: signin.php');

 ?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <title>ログアウト</title>
</head>

<body>
  <h1>ログアウト画面</h1>
  <div><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></div>
  <button type="submit" onclick="location.href='signin.php'">ログイン画面に戻る</button>
</body>

</html>
