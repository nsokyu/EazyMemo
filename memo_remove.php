<?php

//共通関数
require_once('./functions.php');
require_once('./db_connect.php');
//セッションスタート
require_logined_session();

try {
    //DB接続
    $dbh = db_connect();

    $user_id = $_SESSION['id'];
    $stmt = $dbh->prepare("UPDATE memos SET is_removed = 1 WHERE user_id = ? AND id = ?");
    //バインド
    $stmt -> bindValue(1, $_SESSION['id']);
    $stmt -> bindValue(2, $_SESSION['memo_id']);
    $stmt -> execute();
    //メインページに遷移
    $_SESSION['message'] = 'メモが削除されました。';
    header('Location: main.php');

    } catch (PDOException $e) {
    //デバック用
    $errors[] = 'DB接続エラー: '.$e -> getMessage();
}



?>
