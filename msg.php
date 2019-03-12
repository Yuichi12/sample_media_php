<?php

// 共通変数・関数ファイル読み込み
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('個別メッセージツール');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===========================================
// 画面表示処理開始
// ===========================================
$partnerUserId = '';
$partnerUserInfo = '';
$myUserInfo = '';
$u_id = $_SESSION['user_id'];

// 画面表示用データ取得
// ===========================================
// GETパラメータを取得

$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
// DBから掲示板とメッセージデータを取得
$viewData = getMsgsAndBord($m_id);
debug('$viewDataの中身:' . print_r($viewData, true));
// パラメータに不正な値が入っているかチェック
if (empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました。');
  header("Location:mypage.php"); // マイページへ
  exit();
}
// 取得したメッセージデータが自分のものかチェックする
$dealUserIds[] = $viewData[0]['user1_id'];
$dealUserIds[] = $viewData[0]['user2_id'];
if (!in_array($_SESSION['user_id'], $dealUserIds)){
  error_log('エラー発生:指定ページに不正な値が入りました。');
  header("Location:mypage.php"); // マイページへ
  exit();
}
// viewDataから相手のユーザーIDを取り出す
debug('$dealUserIdsの中身:'. print_r($dealUserIds, true));
if (($key = array_search($_SESSION['user_id'], $dealUserIds)) !== false){
  unset($dealUserIds[$key]);
}
$partnerUserId = array_shift($dealUserIds);
// DBから取引相手のユーザー情報を取得
if (isset($partnerUserId)){
  $partnerUserInfo = getUser($partnerUserId);
}
debug('取得した相手のユーザーデータ:' . print_r($partnerUserInfo, true));
// 相手のユーザー情報が取れたかチェック
if (empty($partnerUserInfo)){
  error_log('エラー発生:相手のユーザー情報が取得できませんでした。');
  header("Location:mypage.php"); // マイページへ
  exit();
}
// DBから自分のユーザー情報を取得
$myUserInfo = getUser($u_id);
$userData = $myUserInfo;
debug('取得した自分のユーザーデータ:' . print_r($myUserInfo, true));
// 自分のユーザー情報が取れたかチェック
if (empty($myUserInfo)){
  error_log('エラー発生:自分のユーザ情報が取得できませんでした。');
  header("Location:mypage.php"); // マイページへ
  exit();
}

// post送信されていた場合
if (!empty($_POST)){
  debug('POST送信があります。');
  
  // ログイン認証
  require('auth.php');
  
  // バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  // 最大文字数チェック
  validMaxLen($msg, 'msg', 3000);
  // 未入力チェック
  validRequired($msg, 'msg');
  
  if (empty($err_msg)){
    debug('バリデーションOKです。');
    
    // 例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO messages (bord_id, send_date, sender_id, recipient_id, message, create_date) VALUES (:b_id, :send_date, :sender_id, :recipient_id, :message, :date)';
      $data = array(':b_id' => $m_id, ':send_date' => date('Y-m-d H:i:s'), ':sender_id' => $_SESSION['user_id'], ':recipient_id' => $partnerUserId, ':message' => $msg, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      // クエリ成功の場合
      if ($stmt){
        $_POST = array(); // postをクリア
        debug('連絡掲示板へ遷移します。');
        header("Location:" . $_SERVER['PHP_SELF'] . '?m_id=' . $m_id); // 自分自身に遷移する
      }
    } catch (Exception $e){
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>



<?php require('headWithStyle.php'); ?>
<?php require('header.php'); ?>
<?php require('profNav.php'); ?>
<div class="container-fluid">
  <div class="row">

  <?php require('sidebarProf.php'); ?>
    <div class="col-md-6">
      <div class="area-bord">
        <?php if (!empty($viewData)){ ?>
        <?php foreach ($viewData as $key => $val){ ?>
        <?php if($val['message']){ ?>
        <?php if (!empty($val['sender_id']) && $val['sender_id'] == $partnerUserId){ ?>
        <div class="msg-cnt msg-left">
          <div class="avatar-wrapper">
            <img src="<?php echo sanitize(showImg($partnerUserInfo['pic1'])); ?>" alt="" class="avatar">
          </div>
          <div class="row">
          <p class="msg-inrTxt col-md-8">
            <?php echo sanitize($val['message']); ?>
          </p>
          <div class="msg-date col-md-3">
            <?php echo sanitize($val['send_date']); ?>
          </div>
          </div>
        </div><!-- msg-left -->
        <?php } else{ ?>
        <div class="msg-cnt msg-right">
          <div class="avatar-wrapper">
            <img src="<?php echo sanitize(showImg($myUserInfo['pic1'])); ?>" alt="" class="avatar">
          </div>
          <div class="row">
          <p class="msg-inrTxt col-md-8 order-2">
            <?php echo sanitize($val['message']); ?>
          </p>
          <div class="msg-date col-md-3 order-1">
            <?php echo sanitize($val['send_date']); ?>
          </div>
          </div>
        </div><!-- msg-right -->
        <?php } ?>
        <?php } else{ ?>
          <p style="text-align:center">メッセージはまだありません。</p>
        <?php } ?>
        <?php } ?>
        <?php } ?>
      </div><!-- area-bord -->
      <div class="msg-send-area">
        <form action="" method="post">
          <textarea name="msg" cols="40" rows="5" class="js-count-target"></textarea>
          <div class="js-count-target"><span class="js-count-show">0</span>/300</div>
          <input type="submit" value="送信" class="btn btn-primary msg-send ml-auto">
        </form>
      </div>
    </div><!-- col -->
    <?php require('sidebar.php'); ?>
  </div><!-- row -->
</div><!-- container -->

<?php require('footer.php'); ?>
