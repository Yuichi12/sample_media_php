<?php

// 共通変数・関数ファイル読込み
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('Ajax');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ===========================================
// Ajax処理
// ===========================================

// postがあり、ユーザーIDがあり、ログインしている場合
if (isset($_POST['postId']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信があります。');
  $p_id = $_POST['postId'];
  debug('記事ID:' . $p_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // レコードがあるか検索
    // likeなどの単語はsqlの命令文で使われているため、そのままでは使えないため、`（バッククウォート）で囲む
    $sql = 'SELECT * FROM favorites WHERE post_id = :p_id AND user_id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    
    // クエリ失敗の場合
    if (!$stmt){
      return false;
    }
    $resultCount = $stmt->rowCount();
    debug('$resultCountの中身:' . $resultCount);
    // レコードが一件でもある場合
    if (!empty($resultCount)){
      // レコードを削除する
      $sql = 'DELETE FROM favorites WHERE post_id = :p_id AND user_id = :u_id';
      $data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      // クエリ失敗の場合
      if (!$stmt){
        return false;
      }
    } else{
      // レコードを挿入する
      $sql = 'INSERT INTO favorites (post_id, user_id, create_date) VALUES (:p_id, :u_id, :date)';
      $data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}
debug('Ajax処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
