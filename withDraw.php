<?php

// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('退会ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===========================================
// 画面処理
// ===========================================
// post送信されていた場合
if (!empty($_POST)){
  debug('POST送信があります。');
  
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE user_id = :u_id';
    $sql2 = 'UPDATE posts SET delete_flg = 1 WHERE user_id = :u_id';
    $sql3 = 'UPDATE favorites SET delete_flg = 1 WHERE user_id = :u_id';
    
    // データ流し込み
    $data = array(':u_id' => $_SESSION['user_id']);
    // クエリ実行
    // トランザクション処理
    $dbh->beginTransaction();
    try {
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);
      
      if ($stmt1 === false || $stmt2 === false || $stmt3 === false){
        debug('トランザクション処理を開始します。');
        throw new exception('処理に失敗しました。');
      }
      $dbh->commit();
    } catch (Exception $e){
      // ロールバック
      $dbh->rollBack();
      // 外側のtryに例外を投げる
      throw $e;
    }
    
    // クエリ実行成功の場合(最悪usersテーブルのみ削除成功していれば良しとする)
    if ($stmt1){
      // セッション削除
      session_destroy();
      debug('セッション変数の中身:' . print_r($_SESSION, true));
      debug('トップページへ遷移します。');
      header("Location:index.php");
      exit();
    } else{
      debug('クエリが失敗しました。');
      $err_msg['common'] = MSG07;
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>

  <div class="container">
    <form class="withdraw-form card mx-auto my-auto" method="post" style="width: 18rem;">
      <div class="card-body">
        <h5 class="card-title">退会しますか？</h5>
        <p class="card-text">退会ボタンを押すと退会します。</p>
        <button name="submit" class="btn btn-primary btn-lg d-block mx-auto">退会する</button>
      </div>
    </form>
  </div>



  <?php require('footer.php'); ?>
