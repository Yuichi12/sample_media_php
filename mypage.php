<?php 

// 共通変数・関数ファイル読み込み
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('マイページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ===========================================
// 画面表示処理
// ===========================================
// ログイン認証
require('auth.php');

// 画面表示用データ取得
// ===========================================
$u_id = $_SESSION['user_id'];
// データベースから自分のユーザーデータを取得
$userData = getUser($u_id);
debug('ユーザーデータの中身:' . print_r($userData, true)); // 一時的なデバッグ

// GETパラメータを取得
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; // デフォルトは１ページ

// パラメータに不正な値が入っているかチェック
if (!is_numeric($currentPageNum)){
  error_log('エラー発生:ページパラメータに不正な値が入りました。');
  header("Location:mypage.php");
  exit();
}

// 表示件数
$listSpan = 5;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan);
// DBから対象ユーザーの記事データを取得
$dbPostDataList = getUserPostList($currentMinNum, $u_id);
debug('取得した一覧記事の中身:' . print_r($dbPostDataList, true));
?>


<?php require('headWithStyle.php'); ?>

<body>
  <?php require('header.php'); ?>
  <?php require('profNav.php'); ?>
  <div class="container-fluid">
    <div class="row">

    <?php require('sidebarProf.php'); ?>
      <div class="col-md-6">
        <?php if (!empty($dbPostDataList['data'])){ ?>
        <?php foreach ($dbPostDataList['data'] as $key => $val){ ?>
        <a href="postDetail.php<?php echo sanitize((!empty(appendGetParam())) ? appendGetParam() . '&p_id=' . $val['post_id'] : '?p_id=' . $val['post_id']) . '&mypage=1' ; ?>" class="media mb-5">
          <img class="mr-3 img-thmbnail w-25" src="<?php echo sanitize(showImg($val['pic1'])); ?>" alt="">
          <div class="media-body">
            <h5 class="mt-0 mb-1">
              <?php echo sanitize($val['post_title']); ?>
            </h5>
            <?php echo sanitize(mb_substr($val['post_content'], 0, 60)); ?>
          </div>
        </a>
        <?php } ?>
        <?php } ?>
      </div><!-- col -->

      <?php require('sidebar.php'); ?>
    </div><!-- row -->
    <?php $pageNum = pagination2($currentPageNum, $dbPostDataList['total_page']); ?>
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <?php if ($currentPageNum != 1){ ?>
        <li class="page-item">
          <a class="page-link" href="?p=1" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
            <span class="sr-only">Previous</span>
          </a>
        </li>
        <?php } ?>
        <?php for ($i = $pageNum['minPageNum']; $i <= $pageNum['maxPageNum']; $i++){ ?>
        <li class="page-item <?php if ($currentPageNum == $i) echo 'active'; ?>"><a class="page-link" href="?p=<?php echo $i ?>">
            <?php echo $i ?></a></li>
        <?php } ?>
        <?php if ($currentPageNum != $pageNum['maxPageNum']){ ?>
        <li class="page-item">
          <a class="page-link" href="?p=<?php echo $pageNum['maxPageNum']; ?>" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
            <span class="sr-only">Next</span>
          </a>
        </li>
        <?php } ?>
      </ul>
    </nav>
  </div><!-- contianer -->

  <?php require('footer.php'); ?>
