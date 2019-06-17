<?php
$cleans = array();
$errors = array();
$flgNoError = 0;
//入力チェック
//サニタイズ
if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        $cleans[$key] = htmlspecialchars($value, ENT_QUOTES);
    }
}

if (!empty($cleans['btnSubmit'])) {
    $errors = isNullCheck($cleans);
    $flgNoError = 1;
}

function isNullCheck($check)
{
    if (empty($check['username'])) {
        $error[] = "ユーザーネームが未入力です。";
    }
    if (empty($check['mailaddress'])) {
        $error[] = "メールアドレスが未入力です。";
    }
    if (empty($check['password1'])) {
        $error[] = "パスワードが未入力です。";
    }
    if (empty($check['password2'])) {
        $error[] = "パスワード(確認)が未入力です。";
    }
    return $error;
}
 ?>


<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>メモ帳 登録内容の確認</title>
</head>

<body>
  <div>
    <h1>メモ帳</h1>
  </div>
  <div>
    <h3>登録内容の確認</h3>
  </div>
  <div>
    <h4>こちらの内容でよろしいですか？</h4>
  </div>
  <form action="" method="post">
    <div>
      <p>ユーザーネーム  <?php echo $cleans['username']; ?></p>
      <p>メールアドレス  <?php echo $cleans['mailaddress']; ?></p>
      <p>パスワード  <?php echo $cleans['password1']; ?></p>
      <p>パスワード(確認)  <?php echo $cleans['password1']; ?></p>
    </div>
    <br>
    <p><input type="submit" name="btnSubmit" value="ユーザー登録"></p>
  </form>
  <p><input type="button" onclick="history.back()" value="戻る">
  </p>
</body>

</html>
