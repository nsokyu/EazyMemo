<?php
$cleans = array(); //エスケープ後配列
$errors = array(); //エラー出力用配列
$pageChange = 0;   //登録、登録完了ページの切り替え

//共通関数
require_once('./functions.php');
require_once('./db_connect.php');

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
  nullCheck($errors, $cleans['username'], "ユーザ名");
  nullCheck($errors, $cleans['mailaddress'], "メールアドレス");
  nullCheck($errors, $cleans['password'], "パスワード");
}

//メールアドレスの登録チェック
//登録されていない場合は、登録処理
if (empty($errors) && (!empty($cleans['buttonSignUp']))) {
  try {
    //DB接続
    $dbh = db_connect();

    $stmt = $dbh->prepare("SELECT * FROM users WHERE email =?");
    $stmt->bindValue(1, $cleans['mailaddress']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    //メールチェックが登録されているか
    if (!$row) {
      //メールアドレスが登録されていない場合は登録処理
      $stmt = $dbh->prepare("INSERT INTO
              users(name, email, password, created_at, updated_at)
            VALUE (?,?,?,NOW(),NOW() )");
      //バインド
      $stmt->bindValue(1, $cleans['username']);
      $stmt->bindValue(2, $cleans['mailaddress']);
      $stmt->bindValue(3, password_hash($cleans['password'], PASSWORD_DEFAULT));
      $stmt->execute();
      //表示ページの切り替え
      $pageChange = 1;
    } else {
      //メールアドレスが既に登録されている場合はエラー
      $errors[] = "このメールアドレスは既に登録されています。";
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
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
  <link rel="stylesheet" href="/css/common.css" type="text/css">
  <html lang="ja">
  <title>ユーザ登録 - EazyMemo</title>
</head>

<body>
  <?php if ($pageChange === 0) {
    ////////////////////////////
    //////////登録ページ//////////
    ////////////////////////////
    ?>
  <div class="text-center mb-4">
    <img class="" src="picture/EazyMemo_logo.png" alt="" width="300" height="100">
    <h3>EazyMemoへようこそ!</h3>
    <p>新規登録(無料)して利用を開始しましょう。</p>
  </div>

  <div class="container">
    <div class="div-regulate">
      <form class="form-signup" action="" method="post">
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
          } ?>

        <div class="form-group">
          <label class="SignupForm_label" for="username">ユーザ名</label>
          <input type="text" name="username" class="form-control" id="username" placeholder="eazymemo" <?php if (!empty($cleans['username'])) {
                                                                                                            echo "value=\"" . $cleans['username'] . "\"";
                                                                                                          } ?>>
        </div>
        <div class="form-group">
          <label class="SignupForm_label" for="mailaddress">メールアドレス</label>
          <input type="email" name="mailaddress" class="form-control" id="mailaddress" placeholder="eazymemo@memo.com" <?php if (!empty($cleans['mailaddress'])) {
                                                                                                                            echo "value =" . $cleans['mailaddress'];
                                                                                                                          } ?>>
        </div>
        <div class="form-group">
          <label class="SignupForm_label" for="password">パスワード</label>
          <input type="password" name="password" class="form-control" id="password" placeholder="********" <?php if (!empty($cleans['password'])) {
                                                                                                                echo "value =" . $cleans['password'];
                                                                                                              } ?>>
          <label class="text-mini"> ※8文字以上、英・数・記号が使えます</label>
        </div>
        <br>
        <p><button class="btn btn-lg btn-primary btn-block" type="submit" name="buttonSignUp" value="signUp">登録</button></p>
      </form>
    </div>
  </div>

  <?php
  } elseif ($pageChange === 1) {
    ////////////////////////////
    //////////登録完了ページ//////
    ////////////////////////////
    ?>
  <div class="text-center mb-4">
    <img class="" src="picture/EazyMemo_logo.png" alt="" width="300" height="100">
    <h3>EazyMemoへようこそ!</h3>
    <p>ユーザ登録が完了しました。</p>
  </div>
  <div class="col-2 mx-auto">
    <br>
    <button class="btn btn-outline-primary btn-block" type="submit" onclick="location.href='index.php'">トップへ</button>
  </div>
  <?php
  } ?>
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
</body>

</html>