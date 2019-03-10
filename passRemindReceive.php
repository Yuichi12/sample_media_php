<?php

// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード再発行認証キー入力ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証はなし（ログインできない人が使う画面なので）

// SESSIONに認証キーがあるか確認、なければリダイレクト
if (empty($_SESSION['auth_key'])){
  debug('セッションに認証キーがありません。');
  header("Location:passRemindSend.php");
  exit();
}

// ===========================================
// 画面表示処理
// ===========================================
// post送信されていた場合
if (!empty($_POST)){
  debug('POST送信があります。');
  
  // 変数に認証キーを格納
  $auth_key = $_POST['token'];
  
  // 未入力チェック
  validRequired($auth_key, 'token');
  
  if (empty($err_msg)){
    debug('未入力チェックOKです。');
    
    // 固定長チェック
    validLength($auth_key, 'token');
    // 半角チェック
    validHalf($auth_key, 'token');
    
    if (empty($err_msg)){
      debug('バリデーションOKです。');
      
      if ($auth_key !== $_SESSION['auth_key']){
        debug('入力した認証キーが違います。');
        $err_msg['common'] = MSG15;
      }
      if (time() > $_SESSION['auth_key_limit']){
        debug('認証キーの有効期限が切れました。');
        $err_msg['common'] = MSG16;
      }
      
      if (empty($err_msg)){
        debug('認証OKです。');
        
        $pass = makeRandKey(); // 新しいパスワードを生成
        debug('生成したパスワード:' . print_r($pass, true));
        
        // 例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET password = :password WHERE email = :email AND delete_flg = 0';
          $data = array(':password' => password_hash($pass, PASSWORD_DEFAULT), ':email' => $_SESSION['auth_email']);
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          
          // クエリ成功の場合
          if ($stmt){
            debug('パスワードを更新しました。');
            
            // メールを送信
            $from = 'bakararedboy@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】';
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインしてください。

ログインページ：http://localhost:8888/twitter_practice/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

/////////////////////////////////////////////
カスタマーセンター
URL
E-mail
/////////////////////////////////////////////
EOT;
            sendMail($from, $to, $subject, $comment);
            
            // セッション削除
            session_unset();
            $_SESSION['msg_success'] = SUC03;
            debug('セッション変数の中身：' . print_r($_SESSION, true));
            
            header("Location:login.php"); // ログインページへ
            exit();
          } else{
            debug('クエリに失敗しました。');
            $err_msg['common'] = MSG07;
          }
        } catch (Exception $e){
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>

  <div class="container">
    <div class="row">
      <div class="col-md-6 offset-md-3 pt-5">
        <form action="" method="post" class="mt-5">
          <div class="area-msg">
            <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
          </div>

          <div class="form-group">
            <label for="authKey" class="<?php echo (!empty($err_msg['token'])) ? 'is-invalid' : ''; ?>">認証キー</label>
            <input type="text" name="token" value="<?php echo getFormData('token'); ?>" class="form-control">
            <div class="area-msg">
              <?php if (!empty($err_msg['token'])) echo $err_msg['token']; ?>
            </div>
          </div>

          <input type="submit" value="submit" class="btn btn-primary">
        </form>
        <a href="passRemindSend.php" class="mt-5 d-block">もう一度認証キーを送信する</a>
      </div><!-- col -->
    </div><!-- row -->
  </div><!-- container -->

  <?php require('footer.php'); ?>
