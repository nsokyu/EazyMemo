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
      $stmt->bindValue(1, $cleans['memo']);
      $stmt->bindValue(2, $cleans['importance']);
      $stmt->bindValue(3, $_SESSION['memo_id']);
      $stmt->execute();
      //メモの保存完了メッセージ
      $errors[] = "メモが更新されました。";
    } catch (PDOException $e) {
      //デバック用
      $errors[] = 'DB接続エラー: ' . $e->getMessage();
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
  $stmt->bindValue(1, $_GET['id']);
  $stmt->execute();
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
  $errors[] = 'DB接続エラー: ' . $e->getMessage();
}


// トークンを発行する
$token = md5(uniqid(rand(), true));
// トークンをセッションに保存
$_SESSION['token'] = h($token);

?>

<!doctype html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
  <link rel="stylesheet" href="/css/common.css" type="text/css">
  <html lang="ja">
  <title>編集 - EazyMemo</title>

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
  <div class="container-fluid">
    <div class="row">
      <div class="col">
        <div class="text-center mb-4">
          <img class="" src="picture/EazyMemo_logo.png" alt="" width="450" height="150">
          <h4>メモの編集</h4>
        </div>
        <?php
        //入力、DBエラーがあれば、出力
        if (!empty($errors)) {
          echo '<ul class="text-center list-unstyled">';
          foreach ($errors as $message) {
            echo "<li class=''>";
            echo $message;
            echo "</li>";
          }
          echo "</ul>";
        }  ?>
        <!-- メモがあればフォームを表示する -->
        <?php if ($is_found === 1) : ?>

        <form class="form-memo" method="post">
          <div class="form-group">
            <textarea name="memo" rows="8" cols="100" wrap="hard" placeholder="ここにメモを入力"><?php echo $memo; ?></textarea><br>
          </div>
          <div class="form-group">
            <select class="" name="importance">
              <option value="0" <?php if ($importance == 0) {
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
          </div>
          <input type="hidden" name="token" value="<?php echo $token; ?>">
          <button class="btn btn-md btn-success" type="submit" name="buttonSave" value="save">メモを更新</button>
        </form>
        <br>
        <button class="btn btn-outline-primary" type="button" onclick="dialog_remove()">削除する</button>

        <?php endif; ?>
        <br>
        <br>
        <button class="btn btn-outline-primary" type="submit" onclick="location.href='main.php'">一覧へ戻る</button>
        <br>
      </div>
    </div>
  </div>
  <br>


  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
</body>

</html>