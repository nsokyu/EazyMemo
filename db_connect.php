<?php
///DB接続の共通処理

const DB_DSN = 'mysql:host=localhost;dbname=eazy_memo;charset=utf8'; //データソースネーム
const DB_USERNAME = 'root'; //ユーザーネーム
const DB_PASSWORD = 'root'; //パスワード

function db_connect()
{
    $dbh = new PDO(
      DB_DSN,
      DB_USERNAME,
      DB_PASSWORD,
      array(
      //例外を投げる
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
      //静的プレースホルダ (SQLインジェクション対策)
      PDO::ATTR_EMULATE_PREPARES=>false)
  );

    return $dbh;
}
