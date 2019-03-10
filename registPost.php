<?php

// 共通変数・関数ファイル読込み
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('記事登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===========================================
// 画面表示処理開始
// ===========================================

// 画面表示用データ取得
// ===========================================
// GETデータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから記事データを取得
$dbFormData = (!empty($p_id)) ? getPost($_SESSION['user_id'], $p_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
// DBからカテゴリーデータを取得
$category = getCategory();
debug('記事ID:' . $p_id);
debug('記事データ:' . print_r($dbFormData, true));
debug('カテゴリーデータ:' . print_r($category, true));

// パラメータ改ざんチェック
// ===========================================
// GETパラメータはあるが、改ざんされている（URLURLをいじくった）場合、正しい記事データが取れないのでマイページへ遷移させる
if (!empty($p_id) && empty($dbFormData)){
  debug('GETパラメータの記事IDが違います。マイページへ遷移します。');
  header("Location:mypage.php"); // マイページへ
}

// POST送信時処理
// ===========================================
if (!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報:' . print_r($_POST, true));
  debug('GET情報:' . print_r($_GET, true));
  debug('FILE情報:' . print_r($_FILES, true));

  // 変数にユーザー情報を格納
  $title = $_POST['title'];
  $category = $_POST['category_id'];
  $content = $_POST['content'];
  // 画像をアップロードし、パスを格納
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

  // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if (empty($dbFormData)){
    // 未入力チェック
    validRequired($title, 'title');
    // 最大文字数チェック
    validMaxLen($title, 'title');
    // セレクトボックスチェック
    validSelect($category, 'category_id');
    // 最大文字数チェック
    validMaxLen($content, 'content', 3000);
  } else{
    if ($dbFormData['title'] !== $title){
      // 未入力チェック
      validRequired($title, 'title');
      // 最大文字数チェック
      validMaxLen($title, 'title');
    }
    if ($dbFormData['category_id'] !== $category){
      // セレクトボックスチェック
      validSelect($category, 'category_id');
    }
    if ($dbFormData['content'] !== $content){
      // 最大文字数チェック
      validMaxLen($content, 'content', 3000);
    }
  }

  if (empty($err_msg)){
    debug('バリデーションOKです。');
    // 例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
      if ($edit_flg){
        debug('DB更新です。');
        $sql = 'UPDATE posts SET post_title = :title, post_content = :content, category_id = :category, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE post_id = :p_id AND user_id = :u_id';
        $data = array(':title' => $title, ':content' => $content, ':category' => $category, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);
      } else{
        debug('DB新規登録です。');
        $sql = 'INSERT INTO posts (post_title, post_content, user_id, category_id, pic1, pic2, pic3, create_date) VALUES (:title, :content, :user_id, :category_id, :pic1, :pic2, :pic3, :date)';
        $data = array(':title' => $title, ':content' => $content, ':user_id' => $_SESSION['user_id'], ':category_id' => $category, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL:' . $sql);
      debug('流し込みデータ:' . print_r($data, true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt){
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移します。');
        header("Location:mypage.php"); // マイページへ
        exit();
      }
    } catch(Exception $e){
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理狩猟 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php require('head.php'); ?>

<body>
  <?php require('header.php'); ?>
  <div class="container">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <form action="" method="post" enctype="multipart/form-data">
          <div class="area-msg">
            <?php echo sanitize(!empty($err_msg['common']) ? $err_msg['common'] : ''); ?>
          </div>

          <label for="basic-url" class="<?php if (!empty($err_msg['title'])) echo 'is-invalid'; ?>">
            記事のタイトル</label>
          <div class="input-group">
            <input type="text" name="title" class="form-control <?php echo (!empty($err_msg['title'])) ? 'is-invalid' : ''; ?>" value="<?php echo sanitize(getFormData('title')); ?>" id="basic-url" aria-describedby="basic-addon3">
          </div>
          <div class="area-msg">
            <?php echo sanitize((!empty($err_msg['title'])) ? $err_msg['title'] : ''); ?>
          </div>

          <label for="" class="<?php echo (!empty($err_msg['category_id'])) ? 'is-invalid' : ''; ?>">カテゴリー</label>
          <select name="category_id" class="form-control mb-4 w-25">
            <option value="0" <?php if (getFormData('category_id')==0) echo 'selected' ; ?>>選択してください</option>
            <?php if (!empty($category)){ ?>
            <?php foreach ($category as $key => $val){ ?>
            <option value="<?php echo sanitize($val['category_id']); ?>" <?php if (getFormData('category_id')==$val['category_id']) echo 'selected' ; ?>>
              <?php echo sanitize($val['category_name']) ?>
            </option>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="area-msg">
            <?php echo sanitize((!empty($err_msg['category_id'])) ? $err_msg['category_id'] : ''); ?>
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1" class="<?php echo (!empty($err_msg['content'])) ? 'is-invalid' : ''; ?>">投稿内容</label>
            <textarea name="content" class="form-control js-count-target <?php echo (!empty($err_msg['content'])) ? 'is-invalid' : ''; ?>" id="exampleFormControlTextarea1" rows="10"><?php echo sanitize(getFormData('content')); ?></textarea>
            <div class="text-right"><span class="js-count-show">0</span>/3000</div>
          </div>
          <div class="area-msg">
            <?php echo sanitize((!empty($err_msg['content'])) ? $err_msg['content'] : ''); ?>
          </div>

          <div class="imgDrop-container mr-5">
            画像１
            <label class="area-drop <?php if (!empty($err_msg['pic1'])) echo 'is-invalid'; ?>">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="pic1" class="input-file">
              <img src="<?php echo sanitize(getFormData('pic1')); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic1'))) echo 'display:none'; ?>">
              ドラッグ＆ドロップ
            </label>
            <div class="area-msg">
              <?php if (!empty($err_msg['pic1'])) echo sanitize($err_msg['pic1']); ?>
            </div>
          </div><!-- imgDrop-container -->

          <input class="btn btn-lg btn-outline-primary mb-5 align-bottom" type="submit" value="投稿する">
        </form>


        </form>
      </div>
    </div><!-- row -->
  </div><!-- container -->


  <?php require('footer.php'); ?>
