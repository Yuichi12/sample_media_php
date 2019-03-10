<?php
// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード変更ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===========================================
// 画面表示処理開始
// ===========================================
// DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);

debug('取得したユーザー情報:' . print_r($userData, true));

// post送信されていた場合
if (!empty($_POST)){
  debug('POST送信があります。');
  
  // 変数にユーザー情報を格納
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];
  
  // 未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');
  
  if (empty($err_msg)){
    debug('未入力チェックOK');
    
    // 古いパスワードのバリデーションチェック
    validPass($pass_old, 'pass_pld');
    // 新しいパスワードのバリデーションチェック
    validPass($pass_new, 'pass_new');
    
    // 古いパスワードとDBパスワードを照合（DBに入っているデータと同じであれば、半角英数字チェックや最大文字数チェックは行わなくても問題ない）
    if (!password_verify($pass_old, $userData['password'])){
      debug('古いパスワードがDBと合っていません。');
      $err_msg['pass_old'] = MSG12;
    }
    
    // 新しいパスワードと古いパスワードが同じかチェック
    if ($pass_old === $pass_new){
      debug('新しいパスワードが古いパスワードと同じです。');
      $err_msg['pass_new'] = MSG13;
    }
    
    // パスワードとパスワード再入力が合っているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');
    
    if (empty($err_msg)){
      debug('バリデーションOKです。');
      
      // 例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET password = :password WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(':password' => password_hash($pass_new, PASSWORD_DEFAULT), ':u_id' => $_SESSION['user_id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        // クエリ成功の場合
        if ($stmt){
          debug('パスワードを更新しました。');
          $_SESSION['msg_success'] = SUC01;
          
          // メールを送信
          $username = ($userData['user_name']) ? $userData['user_name'] : '名無し';
          $from = 'bakararedboy@gmail.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知';
          // EOTはEndOfTextの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
          // EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
          $comment = <<<EOT
{$username}  さん
パスワードが変更されました。

///////////////////////////////////////
カスタマーセンター
url
E-mail
///////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          
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
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>
  <div class="container">
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <form action="" method="post" class="">
          <div class="area-msg">
            <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
          </div>

          <div class="form-group">
            <label for="oldPassword" class="<?php echo (!empty($err_msg['pass_old'])) ? 'is-invalid' : ''; ?>">古いパスワード</label>
            <input type="password" name="pass_old" class="form-control" id="oldPassword" aria-describedby="emailHelp" value="<?php echo getFormData('pass_old'); ?>">
            <div class="area-msg">
              <?php if (!empty($err_msg['pass_old'])) echo $err_msg['pass_old']; ?>
            </div>
          </div>


          <div class="form-group">
            <label for="newPassword" class="<?php echo (!empty($err_msg['pass_new'])) ? 'is-invalid' : ''; ?>">新しいパスワード</label>
            <input type="password" name="pass_new" class="form-control" id="newPassword" value="<?php echo getFormData('pass_new'); ?>">
            <div class="area-msg">
              <?php if (!empty($err_msg['pass_new'])) echo $err_msg['pass_new']; ?>
            </div>
          </div>


          <div class="form-group">
            <label for="newPasswordRe" class="<?php echo (!empty($err_msg['pass_new_re'])) ? 'is-invalid' : ''; ?>">新しいパスワード(再入力)</label>
            <input type="password" name="pass_new_re" class="form-control" id="newPasswordRe" aria-describedby="emailHelp" value="<?php echo getFormData('pass_new_re'); ?>">
            <div class="area-msg">
              <?php if (!empty($err_msg['pass_new_re'])) echo $err_msg['pass_new_re']; ?>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
  </div>
  <?php require('footer.php'); ?>
