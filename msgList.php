<?php

// 共通変数・関数ファイル読込み
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('メッセージリスト');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===========================================
// 画面表示処理開始
// ===========================================
// GETパラメータを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; // デフォルトは１ページ
// 表示数
$listSpan = 5;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan);
// ユーザーIDを変数に格納
$u_id = $_SESSION['user_id'];
// ユーザー情報を取得
$userData = getUser($u_id);
// 自分のメッセージリストを取得する
$dbMsgList = getUserMsgsAndBord($u_id, $currentMinNum);
debug('取得したメッセージリスト:' . print_r($dbMsgList, true)); // 一時的なデバッグ


?>
<?php require('headWithStyle.php'); ?>

<body>
  <?php require('header.php'); ?>
  <?php require('profNav.php'); ?>

  <div class="container">
    <div class="row">
      <section class="col-md-6 offset-md-3">
        <?php if (!empty($dbMsgList)){ ?>
        <?php foreach ($dbMsgList as $key => $val){ ?>
        <a href="msg.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&m_id=' . $val['bord_id'] : '?m_id=' . $val['bord_id']; ?>" class="media mb-5">
          <img class="mr-3 msg-img" src="<?php echo sanitize(showImg($val['partner_user']['pic1'])); ?>" alt="">
          <div class="media-body my-auto">
            <h5>
              <?php echo sanitize($val['partner_user']['user_name']); ?>
            </h5>
            <p class="mt-0 mb-1">
              <?php $msg = array_pop($val['msg']); ?>
              <?php echo sanitize($msg['message']); ?>
            </p>
            <div>
              <?php echo sanitize($msg['send_date']); ?>
            </div>
          </div>
        </a>
        <?php } ?>
        <?php } ?>
      </section>
      <?php require('sidebar.php'); ?>
    </div><!-- row -->
  </div><!-- container -->

  <?php require('footer.php'); ?>
