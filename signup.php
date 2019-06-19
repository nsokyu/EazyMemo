<?php
$cleans = array(); //エスケープ後配列
$errors = array(); //エラー出力用配列
$pageChange = 0;   //登録、登録完了ページの切り替え

//共通関数
require_once('./functions.php');

//入力チェック
if (!empty($_POST['buttonSignUp'])) {
    foreach ($_POST as $key => $value) {
        //エスケープ 共通関数
        $cleans[$key] = $cleans[$key] = h($value);
    }
}

//登録ボタンを押している場合
if (!empty($cleans['buttonSignUp'])) {
    //エラーチェック
    nullCheck($errors,$cleans['username'],"ユーザーネーム");
    nullCheck($errors,$cleans['mailaddress'],"メールアドレス");
    nullCheck($errors,$cleans['password1'],"パスワード");
    nullCheck($errors,$cleans['password2'],"パスワード(確認)");
}

//メールアドレスの登録チェック
//登録されていない場合は、登録処理
if (empty($errors) && (!empty($cleans['buttonSignUp']))) {
    try {
        //DB接続
        require_once('./db_connect.php');
        $dbh = db_connect();

        $stmt = $dbh->prepare("SELECT * FROM users WHERE email =?");
        $stmt -> bindValue(1, $cleans['mailaddress']);
        $stmt -> execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //メールチェックが登録されているか
        if (!$row) {
            //メールアドレスが登録されていない場合は登録処理
            $stmt = $dbh->prepare("INSERT INTO
              users(name, email, password, created_at, updated_at)
            VALUE (?,?,?,NOW(),NOW() )");
            //バインド
            $stmt -> bindValue(1, $cleans['username']);
            $stmt -> bindValue(2, $cleans['mailaddress']);
            $stmt -> bindValue(3, password_hash($cleans['password1'], PASSWORD_DEFAULT));
            $stmt -> execute();
            //表示ページの切り替え
            $pageChange = 1;
        } else {
            //メールアドレスが既に登録されている場合はエラー
            $errors[] = "このメールアドレスは既に登録されています。";
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
  <meta charset="UTF-8">
  <title>メモ帳 ユーザー登録</title>
</head>

<body>
<?php if ($pageChange === 0) {
     ////////////////////////////
     //////////登録ページ//////////
     ////////////////////////////?>
  <div>
    <h1>メモ帳</h1>
  </div>
  <div>
    <h3>ユーザー登録</h3>
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
  } ?>
  <form action="" method="post">
    <div>
      <p>ユーザーネーム<input type="text" name="username" placeholder="イージーメモタロウ" <?php if (!empty($cleans['username'])) {
      echo "value=\"".$cleans['username']."\"";
  } ?>></p>
      <p>メールアドレス<input type="text" name="mailaddress" placeholder="eazymemo@example.com" <?php if (!empty($cleans['mailaddress'])) {
      echo "value =".$cleans['mailaddress'];
  } ?>></p>
      <p>パスワード<input type="password" name="password1" <?php if (!empty($cleans['password1'])) {
      echo "value =".$cleans['password1'];
  } ?>></p>
      <p>※半角英数字で4〜16文字</p>
      <p>パスワード(確認)<input type="password" name="password2" <?php if (!empty($cleans['password2'])) {
      echo "value =".$cleans['password2'];
  } ?>></p>
    </div>
    <br>
    <p><button type="submit" name="buttonSignUp" value="signUp">登録</button></p>
  </form>
  <p><button type="submit" onclick="history.back()">戻る</button></p>


<?php
 } elseif ($pageChange === 1) {
     ////////////////////////////
     //////////登録完了ページ//////
     ////////////////////////////?>
  <p>ユーザー登録が完了しました。</p>
  <br>
  <button type="submit" onclick="location.href='signin.php'">トップへ</button>
<?php
 } ?>
</body>

</html>
