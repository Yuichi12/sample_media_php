<?php

// 共通変数・関数ファイル読込み
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('トップページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ===========================================
// 画面表示処理開始
// ===========================================
// 画面表示用データ取得
// -------------------------------------------
// GETパラメータを取得
debug('GETパラメータ:' . print_r($_GET, true));

// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; // デフォルトは1ページ
// カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : 0; // デフォルトは「選択してください」
// ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : 1; // デフォルトは新着順
// キーワード検索
$word = (!empty($_GET['word'])) ? $_GET['word'] : ''; // デフォルトは空文字

// パラメータに不正な値が入っているかチェック
if (!is_numeric($currentPageNum)){
  error_log('エラー発生:ページパラメータに不正な値が入りました。');
  header("Location:index.php"); // トップページへ
  exit();
}
if (!is_numeric($category)){
  error_log('エラー発生:カテゴリーパラメータに不正な値が入りました。');
  header("Location:index.php"); // トップページへ
  exit();
}
if (!is_numeric($sort)){
  error_log('エラー発生:ソートパラメータに不正な値が入りました。');
  header("Location:index.php"); // トップページへ
  exit();
}
// 表示件数
$listSpan = 5;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan);
// DBから記事データを取得
$dbPostDataList = getPostList($currentMinNum, $category, $sort, $word);
// DBからカテゴリーデータを取得
$dbCategoryData = getCategory();

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>

  <div class="container mt-4">
    <div class="row">
      <aside class="col-md-3">
        <form action="" method="get">
          <ul class="list-group">
            <li class="list-group-item">
              <h4>カテゴリー</h4>
              <select name="c_id" class="form-control">
                <option value="0" <?php if (getFormData('c_id, true')==0) 'selected' ; ?>>選択してください</option>
                <?php foreach ($dbCategoryData as $key => $val){ ?>
                <option value="<?php echo sanitize($val['category_id']); ?>" <?php if (getFormData('c_id', true)==$val['category_id']) echo 'selected' ; ?>>
                  <?php echo sanitize($val['category_name']); ?>
                </option>
                <?php } ?>
              </select>
            </li>
            <li class="list-group-item">
              <h4>ソート</h4>
              <select name="sort" class="form-control">
                <option value="1" <?php if (getFormData('sort', true)==0 || getFormData('sort', true)==1) echo 'selected' ; ?>>新着順</option>
                <option value="2" <?php if (getFormData('sort', true)==2) echo 'selected' ; ?>>古い順</option>
              </select>
            </li>
            <li class="list-group-item">
              <button type="submit" class="btn btn-primary">Submit</button>
            </li>
          </ul>
        </form>
      </aside>


      <ul class="list-unstyled col-md-6">
        <?php if (!empty($dbPostDataList['data'])){ ?>
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

        <?php $pageNum = pagination2($currentPageNum, $dbPostDataList['total_page']); ?>
        <nav aria-label="Page navigation example">
          <ul class="pagination justify-content-center pagination">
            <?php if ($currentPageNum != 1){ ?>
            <li class="page-item">
              <a class="page-link" href="?p=1" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
              </a>
            </li>
            <?php } ?>
            <?php for ($i = $pageNum['minPageNum']; $i <= $pageNum['maxPageNum']; $i++){ ?>
            <li class="page-item <?php if ($currentPageNum == $i) echo 'active'; ?>"><a class="page-link" href="?p=<?php echo sanitize($i); ?>">
                <?php echo sanitize($i); ?></a></li>
            <?php } ?>
            <?php if ($currentPageNum != $pageNum['maxPageNum']){ ?>
            <li class="page-item">
              <a class="page-link" href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Next</span>
              </a>
            </li>
            <?php } ?>
          </ul>
        </nav>
      </ul>
    </div><!-- row -->
  </div><!-- container -->



  <?php require('footer.php'); ?>
