<?php
$cleans = array(); //エスケープ後配列
$errors = array(); //エラー出力用配列

//共通関数
require_once('./functions.php');
//セッションスタート
require_unlogined_session();

//入力チェック
if (!empty($_POST['buttonSignIn'])) {
    foreach ($_POST as $key => $value) {
        //エスケープ
        $cleans[$key] = h($value);
    }
}

//登録チェック
//登録されている場合、ログインページへ
//登録されていない場合は、エラー
if (empty($errors) && (!empty($cleans['buttonSignIn']))) {
    try {
        //DB接続
        require_once('./db_connect.php');
        $dbh = db_connect();

        $stmt = $dbh->prepare("SELECT * FROM users WHERE email =?");
        $stmt -> bindValue(1, $cleans['mailaddress']);
        $stmt -> execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //登録されているか
        if (!$row || (!password_verify($cleans['password'], $row['password']))) {
            //登録されていない場合はエラー
            $errors[] = "メールアドレス または パスワードに誤りがあります。";
        } else {
            //ログインの認証に成功
            //セッションIDの追跡を防ぐ
            session_regenerate_id(true);
            //ユーザーネーム、ユーザーIDをセッションにセット
            $_SESSION['name'] = $row['name'];
            $_SESSION['id'] = $row['id'];
            //ログインページに遷移
            header('location: main.php');
            exit;
        }
    } catch (PDOException $e) {
        //デバック用
        $errors[] = 'DB接続エラー: '.$e -> getMessage();
    }
}


?>


<!doctype html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>メモ帳 ログイン</title>
</head>

<body>
  <div>
    <h1>メモ帳</h1>
  </div>
  <div class="">
    <p>ログイン後、メモ帳が利用できます</p>
  </div>

  <?php
  //入力、DBエラーがあれば、出力
  if (!empty($errors)) {
      echo "<ul>";
      foreach ($errors as $message) {
          echo "<li>";
          echo $message;
          echo "</li>";
      }
      echo "</ul>";
  }  ?>
  <div>
    <form action="signin.php" method="post">
      <p>メールアドレス</p>
      <input type="text" name="mailaddress" value="<?php if (!empty($cleans['mailaddress'])) {
      echo $cleans['mailaddress'];
  } ?>">
      <p>パスワード</p>
      <input type="password" name="password" value="<?php if (!empty($cleans['password'])) {
      echo $cleans['password'];
  } ?>">
      <br>
      <button type="submit" name="buttonSignIn" value="signIn">ログイン</button>
    </form>
  </div>
  <br>
  <p>初めての方は こちらから</p>
  <button type="submit" onclick="location.href='signup.php'">ユーザー登録</button>


</body>

</html>
