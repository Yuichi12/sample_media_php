<?php

// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('新規ユーザー登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

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
  $pass_re = $_POST['pass_re'];
  
  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  
  if (empty($err_msg)){
    
    // emailの形式チェック
    validEmail($email, 'email');
    // emailの最大文字数チェック
    validMaxLen($email, 'email');
    // emailの重複チェック
    validEmailDup($email);
    
    // パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    // パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    // パスワードの最小文字数チェック
    validMinLen($pass, 'pass');
    
    if (empty($err_msg)){
      
      // パスワードとパスワード再入力が合っているかチェック
      validMatch($pass, $pass_re, 'pass_re');
      
      if (empty($err_msg)){
        debug('バリデーションOKです。');
        debug('データベースにユーザー情報を登録します。');
        // 例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'INSERT INTO users (email, password, create_date) VALUES (:email, :pass, :date)';
          $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':date' => date('Y-m-d H:i:s'));
          
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          
          // クエリ成功の場合
          if ($stmt){
            // 新しくセッション変数に値を格納する
            // ログイン有効期限（デフォルトを１時間とする）
            $sesLimit = 60 * 60;
            // 最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            // ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();
            
            debug('セッション変数の中身:' . print_r($_SESSION, true));
            
            header("Location:mypage.php"); // マイページへ
            exit();
          }
        } catch (Exception $e){
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
} else {
  debug('POST送信がありません。');
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>



<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>

  <section class="container">
    <h3 class="container-title">ユーザー登録</h3>
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

      <label for="basic-url" class="<?php echo (!empty($err_msg['pass_re'])? 'is-invalid' : ''); ?>">パスワード（再入力）</label>
      <div class="input-group">
        <input type="password" name="pass_re" value="<?php echo getFormData('pass_re'); ?>" class="form-control <?php echo (!empty($err_msg['pass_re']) ? 'is-invalid' : ''); ?>" id="basic-url">
      </div>
      <div class="area-msg mb-3">
        <?php echo sanitize(!empty($err_msg['pass_re']) ? $err_msg['pass_re'] : ''); ?>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </section>

  <?php require('footer.php'); ?>
