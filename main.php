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
        $stmt->bindValue(1, $_SESSION['id']);
        $stmt->bindValue(2, $cleans['memo']);
        $stmt->bindValue(3, $cleans['importance']);
        $stmt->execute();
        //メモの保存完了メッセージ
        $errors[] = "メモの保存が完了しました。";
      } else {
        //２回目以降のメモ登録時の処理
        $stmt = $dbh->prepare("INSERT INTO
              memos(user_id, id, memo, importance, created_at, updated_at)
            VALUE (?,?,?,?,NOW(),NOW() )");
        //バインド
        $stmt->bindValue(1, $_SESSION['id']);
        $stmt->bindValue(2, $row['id'] + 1);
        $stmt->bindValue(3, $cleans['memo']);
        $stmt->bindValue(4, $cleans['importance']);
        $stmt->execute();
        //メモの保存完了メッセージ
        $errors[] = "メモの保存が完了しました。";
      }
    } catch (PDOException $e) {
      //デバック用
      $errors[] = 'DB接続エラー: ' . $e->getMessage();
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
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
  <link rel="stylesheet" href="css/common.css" type="text/css">
  <html lang="ja">
  <title>ホーム - EazyMemo</title>
</head>

<body>

  <div class="container-fluid">
    <div class="row">

      <div class="col-md-10 order-md-last">
        <div class="text-center mb-4">
          <img class="" src="picture/EazyMemo_logo.png" alt="" width="450" height="150">
          <h4>ようこそ <?php echo h($_SESSION['name']); ?>さん!</h4>
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
        <div class="div-regulate">
          <form class="form-main" action="" method="post">
            <div class="form-group">
              <textarea class="form-control" name="memo" rows="10" cols="50" wrap="hard" placeholder="ここにメモを入力"></textarea><br>
            </div>
            <div class="form-group">
              <select class="form-control" name="importance">
                <option value="0" selected="selected">優先度</option>
                <option value="1">(優先度) 大</option>
                <option value="2">(優先度) 中</option>
                <option value="3">(優先度) 小</option>
              </select><br>
            </div>
            <input type="hidden" name="token" value="<?php echo h($token); ?>">
            <button class="btn btn-md btn-success btn-block" type="submit" name="buttonSave" value="save">保存</button>
          </form>


          <br>
          <br>

          <button class="btn btn-outline-primary" type="submit" name="buttonSignOut" value="signOut" onclick="location.href='signout.php'">ログアウト</button>
        </div>
      </div>

      <div class="col-md-2 order-md-first">
        <div class="list-group">
          <?php

          $user_id = $_SESSION['id'];
          if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) {
            $hpage = h($_GET['page']);
            $page = ($hpage - 1) * 10;
          } else {
            $hpage = 1;
            $page = 0;
          }

          try {
            //DB接続
            $dbh = db_connect();

            //メモの有効件数からページ数をを算出
            $stmt = $dbh->query("SELECT COUNT(*) FROM memos WHERE user_id = $user_id AND is_removed = 0");
            $count_memo = $stmt->fetch(PDO::FETCH_COLUMN);

            $count_page = intval(($count_memo + 10) / 10);

            //メモを10件取得
            $stmt = $dbh->prepare("SELECT * FROM memos WHERE user_id = $user_id AND is_removed = 0 ORDER BY updated_at DESC LIMIT ?,10");
            $stmt->bindValue(1, $page);
            $stmt->execute();

            //メモの表示
            echo "<br>";
            while ($row = $stmt->fetch()) : ?>
          <a href="memo.php?id=<?php echo $row['id']; ?> " class="list-group-item d-flex justify-content-between align-items-center"><?php $h_memo = h($row['memo']);
                                                                                                                                          //改行が含まれる場合は、先頭行のみ表示
                                                                                                                                          if (strstr($h_memo, "\r\n", true)) {
                                                                                                                                            //10文字以上は...を表示
                                                                                                                                            if (mb_strlen(strstr($h_memo, "\r\n", true)) <= 10) {
                                                                                                                                              echo strstr($h_memo, "\r\n", true) . "..";
                                                                                                                                            } else {
                                                                                                                                              echo mb_substr(strstr($h_memo, "\r\n", true), 0, 9) . "..";
                                                                                                                                            }
                                                                                                                                          } else {
                                                                                                                                            if (mb_strlen($h_memo) <= 10) {
                                                                                                                                              echo $h_memo;
                                                                                                                                            } else {
                                                                                                                                              echo mb_substr($h_memo, 0, 9) . "..";
                                                                                                                                            }
                                                                                                                                          }; ?>
            <span class="badge badge-light badge-pill"><?php switch ($row['importance']) {
                                                                //バッチには重要度を表示
                                                              case 1:
                                                                echo "大";
                                                                break;
                                                              case 2:
                                                                echo "中";
                                                                break;
                                                              case 3:
                                                                echo "小";
                                                                break;
                                                              default:
                                                                echo "-";
                                                                break;
                                                            }; ?></span></a>
          <time class="memo_time"><?php echo substr($row['updated_at'], 0, 16); ?></time>

          <?php endwhile;

            //ページネーション
            if ($count_memo > 0) {
              echo "<nav aria-label='ページ送りの実例'>";
              echo "<ul class='pagination'>";
              if (intval($hpage) === 1) {
                echo "<li class='page-item disabled'><a class='page-link'>前へ</a></li>";
              } else {
                $back_page = $hpage - 1;
                echo "<li class='page-item'><a class='page-link' href='main.php?page={$back_page}'>前へ</a></li>";
              }
              for ($i = 1; $i <= $count_page; $i++) {
                if (intval($hpage) === $i) {
                  echo "<li class='page-item disabled'><a class='page-link'>{$i}</a></li>";
                } else {
                  echo "<li class='page-item'><a class='page-link' href='main.php?page={$i}'>{$i}</a></li>";
                }
              }
              if (intval($hpage) === $count_page) {
                echo "<li class='page-item disabled'><a class='page-link'>次へ</a></li>";
              } else {
                $next_page = $hpage + 1;
                echo "<li class='page-item'><a class='page-link' href='main.php?page={$next_page}'>次へ</a></li>";
              }
              echo "</ul>";
              echo "</nav>";
            }

            //メモがない場合
            if ($count_memo === 0) {
              echo "<br>";
              echo "メモを保存するとここに表示されます";
            }
          } catch (PDOException $e) {
            //デバック用
            $errors[] = 'DB接続エラー: ' . $e->getMessage();
          }
          ?>
        </div>
      </div>


    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
</body>

</html>