<?php
$cleans = array(); //エスケープ後配列
$errors = array(); //エラー出力用配列

//共通関数
require_once('./functions.php');
require_once('./db_connect.php');
//セッションスタート
require_unlogined_session();

//入力チェック
if (!empty($_POST['buttonSignIn']) || !empty($_POST['buttonExperience'])) {
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
    $dbh = db_connect();

    $stmt = $dbh->prepare("SELECT * FROM users WHERE email =?");
    $stmt->bindValue(1, $cleans['mailaddress'], PDO::PARAM_STR);
    $stmt->execute();
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
    $errors[] = 'DB接続エラー: ' . $e->getMessage();
  }
}

//テストユーザのログインページへ
//登録されていない場合は、エラー
if (empty($errors) && (!empty($cleans['buttonExperience']))) {
  try {
    //DB接続
    $dbh = db_connect();

    $stmt = $dbh->query("SELECT * FROM users WHERE email = 'test@test.com'");
    $stmt->execute();
    $row = $stmt->fetch();

    //登録されているか
    if (!$row) {
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
    $errors[] = 'DB接続エラー: ' . $e->getMessage();
  }
}

?>


<!doctype html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
  <link rel="stylesheet" href="css/common.css" type="text/css">
  <html lang="ja">
  <title>EazyMemo</title>
</head>

<body>
  <div class="container">

    <div class="text-center mb-4">
      <img class="" src="picture/EazyMemo_logo.png" alt="" width="300" height="100">
      <p>EazyMemoでは、ブラウザからあなただけのメモ帳が利用できます。</p>
    </div>

    <div class="div-regulate-signin">
      <form class="form-signin" action="index.php" method="post">
        <?php
        //入力、DBエラーがあれば、出力
        if (!empty($errors)) {
          echo '<ul class="ul-error">';
          foreach ($errors as $message) {
            echo "<li>";
            echo $message;
            echo "</li>";
          }
          echo "</ul>";
        }  ?>
        <div class="form-group">
          <input type="email" class="form-control" name="mailaddress" placeholder="メールアドレス" value="<?php if (!empty($cleans['mailaddress'])) {
                                                                                                      echo $cleans['mailaddress'];
                                                                                                    } ?>" required autofocus>
        </div>
        <div class="form-group">
          <input type="password" class="form-control" name="password" placeholder="パスワード" value="<?php if (!empty($cleans['password'])) {
                                                                                                    echo $cleans['password'];
                                                                                                  } ?>" required>
        </div>
        <div class="form-signin-button">
          <button class="btn btn-lg btn-primary btn-block" type="submit" name="buttonSignIn" value="signIn">ログイン</button>
        </div>
      </form>
      <br>
      <button class="btn btn-outline-primary" type="submit" onclick="location.href='signup.php'">ユーザ登録</button>
      <form class="form-Experience" action="index.php" method="post">
        <button class="btn btn-outline-primary" type="submit" name="buttonExperience" value="Experience">すぐに体験する</button>
      </form>
    </div>
  </div>
  <br>

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
</body>

</html>