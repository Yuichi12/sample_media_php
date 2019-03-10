<?php

// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('記事詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ===========================================
// 画面表示処理
// ===========================================

// 画面表示用データ取得
// ===========================================
// 記事IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// マイページから来た場合のフラグ
$mypage_flg = (!empty($_GET['mypage'])) ? 'mypage.php' : 'index.php';
// DBから記事データを取得
$viewData = getPostDetail($p_id);
// パラメータに不正な値が入っているかチェック
if (empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました。');
  header("Location:index.php"); // トップページへ
  exit();
}
debug('取得したDBデータ:' . print_r($viewData, true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>



<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>

  <section class="container">
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <div class="row">
          <div class="post-header col-md-10">
            <a href="prof.php<?php echo sanitize((!empty(appendGetParam())) ?  appendGetParam() . '&u_id=' . $viewData['user_id'] :  '?u_id=' . $viewData['user_id']); ?>" class="post-author">
              <?php echo sanitize($viewData['user_name']); ?></a>
            <span class="post-date">
              <?php echo sanitize($viewData['update_time']); ?></span>
            <h3 class="post-title">
              <?php echo sanitize($viewData['post_title']); ?>
            </h3>
            <a href="index.php?c_id=<?php echo sanitize($viewData['category_id']); ?>" class="post-category">
              <?php echo sanitize($viewData['category_name']); ?></a>
          </div><!-- post-header -->
          <i class="like mt-5 fas fa-heart col-md-2 text-center js-click-like" data-postid="<?php echo sanitize($viewData['post_id']); ?>"></i>
        </div><!-- row -->
        <div class="post-body">
          <div class="post-image">
            <img src="<?php echo sanitize(showImg($viewData['pic1'])) ?>" alt="">
          </div>
          <div class="post-text">
            <?php echo sanitize($viewData['post_content']); ?>
          </div>
        </div>
        <?php debug('appendGetParamの中:' . print_r(appendGetParam(), true)); ?>
        <a href="<?php echo $mypage_flg . appendGetParam(array('p_id', 'mypage')); ?>">&lt; 記事一覧に戻る</a>
      </div><!-- col -->
    </div><!-- row -->
  </section>
  <?php require('footer.php'); ?>
