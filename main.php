<?php
$cleans = array(); //エスケープ後配列
$errors = array(); //エラー出力用配列

//共通関数
require_once('./functions.php');
require_once('./db_connect.php');
//セッションスタート
require_logined_session();

if (isset($_SESSION['message'])) {
  $errors[] = $_SESSION['message']; //メッセージの引き継ぎ
}
unset($_SESSION['message']); //子ページで格納したセッションを初期化
unset($_SESSION['memo_id']); //子ページで格納したセッションを初期化

// セッションに入れておいたトークンを取得
$session_token = isset($_SESSION['token']) ? h($_SESSION['token']) : '';
// POSTの値からトークンを取得
$token = isset($_POST['token']) ? h($_POST['token']) : '';
// セッションに保存しておいたトークンの削除
unset($_SESSION['token']);

//セッションとPOSTのトークンが一致している場合、後続処理へ
if ($session_token === $token) {
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
}

// トークンを発行する
$token = md5(uniqid(rand(), true));
// トークンをセッションに保存
$_SESSION['token'] = h($token);

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
      <option value="" selected="selected" >優先度</option>
      <option value="1">(優先度) 大</option>
      <option value="2">(優先度) 中</option>
      <option value="3">(優先度) 小</option>
    </select><br>
    <input type="hidden" name="token" value="<?php echo h($token);?>">
    <button type="submit" name="buttonSave" value="save">保存</button>
  </form>
  <br>
  <br>

  <?php

  try {
      //DB接続
      $dbh = db_connect();

      $user_id = $_SESSION['id'];
      $stmt = $dbh->query("SELECT * FROM memos WHERE user_id = $user_id AND is_removed = 0 ORDER BY updated_at DESC"); ?>
  <article class="">
    <?php
    //メモを表示
    while ($row = $stmt->fetch()): ?>
    <p><a href="memo.php?id=<?php echo $row['id']; ?> "><?php echo $row['memo']; ?></a></p>
    <time><?php echo $row['updated_at']; ?></time>
    <hr>
    <?php endwhile; ?>
  </article>
  <?php
  } catch (PDOException $e) {
      //デバック用
      $errors[] = 'DB接続エラー: '.$e -> getMessage();
  }
?>


  <button type="submit" name="buttonSignOut" value="signOut" onclick="location.href='signout.php'">ログアウト</button>


</body>

</html>
