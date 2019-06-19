<?php
$cleans = array(); //エスケープ後配列
$errors = array(); //エラー出力用配列

//共通関数
require_once('./functions.php');
//セッションスタート
require_logined_session();

//入力チェック
if (!empty($_POST['buttonSave'])) {
    foreach ($_POST as $key => $value) {
        //エスケープ
        $cleans[$key] = h($value);
    }
}

//登録ボタンを押している場合
if (!empty($cleans['buttonSave'])) {
    //エラーチェック
    nullCheck($errors, $cleans['memo'], メモ);
}

//メモの登録
//登録されていない場合は、エラー
if (empty($errors) && (!empty($cleans['buttonSave']))) {
    try {
        //DB接続
        require_once('./db_connect.php');
        $dbh = db_connect();

        $user_id = $_SESSION['id'];
        $stmt = $dbh->query("SELECT * FROM memos WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch();

        //初めてのメモ登録か
        if (!$row) {
            //初めてのメモ登録時の処理
            $stmt = $dbh->prepare("INSERT INTO
              memos(user_id, id, memo, importance, created_at, updated_at)
            VALUE (?,1,?,?,NOW(),NOW() )");
            //バインド
            $stmt -> bindValue(1, $_SESSION['id']);
            $stmt -> bindValue(2, $cleans['memo']);
            $stmt -> bindValue(3, $cleans['importance']);
            $stmt -> execute();
            //メモの保存完了メッセージ
            $errors[] = "メモの保存が完了しました。";
        } else {
            //２回目以降のメモ登録時の処理
            $stmt = $dbh->prepare("INSERT INTO
            memos(user_id, id, memo, importance, created_at, updated_at)
          VALUE (?,?,?,?,NOW(),NOW() )");
            //バインド
            $stmt -> bindValue(1, $_SESSION['id']);
            $stmt -> bindValue(2, $row['id']+1);
            $stmt -> bindValue(3, $cleans['memo']);
            $stmt -> bindValue(4, $cleans['importance']);
            $stmt -> execute();
            //メモの保存完了メッセージ
            $errors[] = "メモの保存が完了しました。";
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
  <title>メモ帳 ホーム</title>
</head>

<body>
  <div>
    <h1>メモ帳</h1>
  </div>
  <div class="">
    <p>ようこそ <?php echo h($_SESSION['name']); ?>さん</p>
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
  <form class="" method="post">
    <textarea name="memo" rows="8" cols="100" wrap="hard" placeholder="ここにメモを入力"></textarea><br>
    <select class="" name="importance">
      <option value="">(重要度)</option>
      <option value="1">大</option>
      <option value="2">中</option>
      <option value="3">小</option>
    </select><br>
    <button type="input" name="buttonSave" value="save">保存</button>
  </form>

  <br>
  <button type="submit" name="buttonSignOut" value="signOut" onclick="location.href='signout.php'">ログアウト</button>


</body>

</html>
