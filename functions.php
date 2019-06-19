<?php

//セッション
function require_unlogined_session()
{
    // セッション開始
    session_start();
    // ログインしていれば /main.php に遷移
    if (isset($_SESSION['name'])) {
        header('Location: main.php');
        exit;
    }
}

//セッション
function require_logined_session()
{
    // セッション開始
    session_start();
    // ログインしていなければ /login.php に遷移
    if (!isset($_SESSION['name'])) {
        header('Location: signout.php');
        exit;
    }
}

//エスケープ
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//未入力チェック
function nullCheck(&$errors, $str, $message)
{
    if (empty($str)) {
        return $errors[] = "[$message]が未入力です。";
    }
}
