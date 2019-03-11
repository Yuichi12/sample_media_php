<?php

// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('プロフィールページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ===========================================
// 画面表示処理
// ===========================================

// 画面表示用データ取得
// ===========================================
// GETパラメータを取得
// -------------------------------------------
// カレントページのGETパラメータを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; // デフォルトは1ページ
// ユーザーIDのGETパラメータを取得
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
// DBからユーザーデータを取得
$userData = getUser($u_id);
// パラメータに不正な値が入っていないかチェック
if (!is_int($currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました。' );
  header("Location:index.php");
  exit();
}
if (empty($userData)){
  error_log('エラー発生:指定ページに不正な値が入りました。');
  header("Location:index.php"); // トップページへ
  exit();
}
debug('取得したDBデータ:' . print_r($userData, true));

// 取得したユーザーデータが自分のものならマイページへ遷移させる
if (!empty($_SESSION['user_id'])){
  if ($userData['user_id'] === $_SESSION['user_id']){
    debug('自分のプロフィールなのでマイページへ遷移します。');
    header("Location:mypage.php"); // マイページへ
    exit();
  }
}
// 表示件数
$listSpan = 5;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan);

// 取得したユーザーの記事一覧をDBから取得する
$dbPostDataList = getUserPostList($currentMinNum, $u_id, $listSpan);
debug('取得した記事一覧情報:' . print_r($dbPostDataList, true));

// メッセージボタンが押された場合、メッセージボードを作ってmsg.phpに遷移する
debug('POST送信があるか' . print_r($_POST, true));

if (!empty($_POST['submit'])){
  debug('POST送信があります。');
  
  // ログイン認証 メッセージ送信はログインしてなければ出来ない
  require('auth.php');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // まず、ボードが既に作成されているか調べる
    $sql = 'SELECT bord_id FROM bords WHERE (user1_id = :1_id OR user2_id = :1_id) AND (user1_id = :2_id OR user2_id = :2_id) AND delete_flg = 0';
    $data = array(':1_id' => $_SESSION['user_id'], ':2_id' => $userData['user_id']);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    
    // クエリ成功の場合
    if ($stmt){
      // ボードが既に作られていればそのメッセージボードへ遷移する
      if ($stmt->rowCount()){
        $rst = $stmt->fetch(PDO::FETCH_ASSOC);
        header("Location:msg.php?m_id=" . $rst['bord_id']);
        exit();
      } else{
        // ボードがなければ新しく作る
        $sql = 'INSERT INTO bords (user1_id, user2_id, create_date) VALUES (:1_id, :2_id, :date)';
        $data = array(':1_id' => $_SESSION['user_id'], ':2_id' => $userData['user_id'], ':date' => date('Y-m-d H:i:s'));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if ($stmt){
          debug('メッセージボードを新しく作りました。');
          debug('メッセージボードに遷移します。');
          header("Location:msg.php?m_id=" . $dbh->lastInsertId());
          exit();
        }
      }
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php require('headWithStyle.php'); ?>


<body>
  <?php require('header.php'); ?>

  <?php require('profNav.php'); ?>
  <div class="container-fluid pt-4">
    <div class="row">

    <?php require('sidebarProf.php'); ?>

      <div class="col-md-6">
        <?php if (!empty($dbPostDataList)){ ?>
        <?php foreach ($dbPostDataList['data'] as $key => $val){ ?>
        <a href="postDetail.php<?php echo sanitize((!empty(appendGetParam())) ? appendGetParam() . '&p_id=' . $val['post_id'] : '?p_id=' . $val['post_id']); ?>" class="media mb-5">
          <img class="mr-3 img-thumbnail w-25" src="<?php echo sanitize(showImg($val['pic1'])); ?>" alt="Generic placeholder image">
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
    </div><!-- row -->

    <?php $pageNum = pagination2($currentPageNum, $dbPostDataList['total_page']); ?>
    <div class="d-flex justify-content-center">
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <?php if ($currentPageNum != 1){ ?>
          <li class="page-item">
            <a class="page-link" href="?p=1" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
              <span class="sr-only">Previous</span>
            </a>
          </li>
          <?php } ?>
          <?php for ($i = $pageNum['minPageNum']; $i <= $pageNum['maxPageNum']; $i++){ ?>
          <li class="page-item"><a class="page-link" href="?p=<?php echo $i ?>">
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
    </div>
  </div><!-- container -->

  <?php require('footer.php'); ?>
