<?php

// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ログインページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===========================================
// 画面表示処理開始
// ===========================================
// post送信されていた場合
if (!empty($_POST)){
  debug('POST送信があります。');
  debug('$_POSTの中身:' . print_r($_POST, true));
  
  // 変数にユーザー情報を格納
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;
  
  // emailの形式チェック
  validEmail($email, 'email');
  // emailの最大文字数チェック
  validMaxLen($email, 'email');
  
  // パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  // パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  // パスワードの最小文字数チェック
  validMinLen($pass, 'pass');
  
  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  
  if (empty($err_msg)){
    debug('バリデーションOKです。');
    debug('パスワードがマッチしているか確認します。');
    
    // 例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'SELECT password, user_id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email,);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      // クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
      debug('クエリ結果の中身:' . print_r($result, true));
      
      // パスワード照合
      if (!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワードがマッチしました。');
        
        // ログイン有効期限（デフォルトを１時間とする）
        $sesLimit = 60 * 60;
        // 最終ログイン日時を現在時刻に
        $_SESSION['login_date'] = time();
        
        // ログイン保持にチェックがある場合
        if ($pass_save){
          debug('ログイン保持にチェックがあります。');
          // ログイン有効期限を30日にしてセット
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else{
          debug('ログイン保持にチェックがありません。');
          // 次回からログイン保持しないので、ログイン有効期限を１時間後にセット
          $_SESSION['login_limit'] = $sesLimit;
        }
        // ユーザーIDをセッション変数に格納
        $_SESSION['user_id'] = $result['user_id'];
        
        debug('セッション変数の中身:' . print_r($_SESSION, true));
        debug('マイページへ遷移します。');
        header("Location:mypage.php"); // マイページへ
      } else{
        debug('パスワードがアンマッチです。');
        $err_msg['common'] = MSG09;
      }
    } catch (Exception $e){
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>


<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>

  <section class="container">
    <h3 class="container-title">ログイン</h3>
    <form action="" method="post" class="col-md-6 offset-md-3">
      <div class="area-msg">
        <?php echo sanitize(!empty($err_msg['common']) ? $err_msg['common'] : ''); ?>
      </div>

      <label for="basic-url" class="<?php echo (!empty($err_msg['email']) ? 'is-invalid' : ''); ?>">メールアドレス</label>
      <div class="input-group">
        <input type="text" name="email" value="<?php echo getFormData('email'); ?>" class="form-control <?php if (!empty($err_msg['email'])) echo 'is-invalid'; ?>" id="basic-url">
      </div>
      <div class="area-msg mb-3">
        <?php echo sanitize(!empty($err_msg['email']) ? $err_msg['email'] : ''); ?>
      </div>

      <label for="basic-url" class="<?php echo (!empty($err_msg['pass'])) ? 'is-invalid' : ''; ?>">パスワード</label>
      <div class="input-group">
        <input type="password" name="pass" value="<?php echo getFormData('pass'); ?>" class="form-control <?php echo (!empty($err_msg['pass'])) ? 'is-invalid' : ''; ?>" id="basic-url">
      </div>
      <div class="area-msg mb-3">
        <?php echo sanitize(!empty($err_msg['pass']) ? $err_msg['pass'] : ''); ?>
      </div>

      <div class="form-group form-check">
        <input type="checkbox" name="pass_save" class="form-check-input" id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">ログインしたままにする</label>
      </div>

      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <p class="text-center mt-5">パスワードを忘れた方は<a href="passRemindSend.php">こちら</a></p>
  </section>

  <?php require('footer.php'); ?>
