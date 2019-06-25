<?php
$cleans = array(); //エスケープ後配列
$errors = array(); //エラー出力用配列
$is_found = 0;     //メモがあったか

//共通関数
require_once('./functions.php');
require_once('./db_connect.php');
//セッションスタート
require_logined_session();

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

    //メモの編集
    //登録されていない場合は、エラー
    if (empty($errors) && (!empty($cleans['buttonSave']))) {
        try {
            //DB接続
            $dbh = db_connect();

            $user_id = $_SESSION['id'];
            $stmt = $dbh->prepare("UPDATE memos SET memo = ?, importance = ? WHERE user_id = $user_id AND id = ?");
            //バインド
            $stmt -> bindValue(1, $cleans['memo']);
            $stmt -> bindValue(2, $cleans['importance']);
            $stmt -> bindValue(3, $_SESSION['memo_id']);
            $stmt -> execute();
            //メモの保存完了メッセージ
            $errors[] = "メモが更新されました。";
        } catch (PDOException $e) {
            //デバック用
            $errors[] = 'DB接続エラー: '.$e -> getMessage();
        }
    }
}

//メモの表示
//登録されていない場合は、エラー
try {
    //DB接続
    $dbh = db_connect();

    $user_id = $_SESSION['id'];
    $stmt = $dbh->prepare("SELECT * FROM memos WHERE user_id = $user_id AND id = ?");
    $stmt -> bindValue(1, $_GET['id']);
    $stmt -> execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    //メモが見つからない、もしくは削除済みの場合
    if (!$row || $row['is_removed'] === 1) {
        $errors[] = 'お探しのメモは見つかりませんでした。';
        $is_found = 0;
    } else {
        //メモがあった場合
        //メモの内容を変数に格納
        $memo = h($row['memo']);
        $importance = h($row['importance']);
        $updateTime = h($row['updated_at']);
        //セッションに格納
        $_SESSION['memo_id'] = h($row['id']);
        $is_found = 1;
    }
} catch (PDOException $e) {
    //デバック用
    $errors[] = 'DB接続エラー: '.$e -> getMessage();
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
  <title>メモ帳 詳細</title>

  <script type="text/javascript">
    <!--
    function dialog_remove() {
      if (window.confirm('このメモを削除しますか？')) {
        location.href = 'memo_remove.php';
      }
    }
    -->
  </script>
</head>

<body>
  <div>
    <h1>メモ帳</h1>
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
  <!-- メモがあればフォームを表示する -->
  <?php  if ($is_found === 1): ?>

  <form class="" method="post">
    <textarea name="memo" rows="8" cols="100" wrap="hard" placeholder="ここにメモを入力"><?php echo $memo; ?></textarea><br>
    <select class="" name="importance">
      <option value="" <?php if ($importance == 0) {
      echo 'selected="selected"';
  } ?>>優先度</option>
      <option value="1" <?php if ($importance == 1) {
      echo 'selected="selected"';
  } ?>>(優先度) 大</option>
      <option value="2" <?php if ($importance == 2) {
      echo 'selected="selected"';
  } ?>>(優先度) 中</option>
      <option value="3" <?php if ($importance == 3) {
      echo 'selected="selected"';
  } ?>>(優先度) 小</option>
    </select><br>
    <input type="hidden" name="token" value="<?php echo $token;?>">
    <button type="input" name="buttonSave" value="save">メモを更新</button>
  </form>
  <br>
  <button type="button" onclick="dialog_remove()">削除</button>

  <?php endif; ?>
  <br>
  <br>
  <button type="submit" onclick="location.href='main.php'">一覧へ戻る</button>
  <br>
  <br>
  <br>
  <button type="submit" name="buttonSignOut" value="signOut" onclick="location.href='signout.php'">ログアウト</button>


</body>

</html>
