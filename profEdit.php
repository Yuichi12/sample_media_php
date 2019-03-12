<?php

// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('プロフィール編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===========================================
// 画面表示処理開始
// ===========================================
// DBからユーザーデータを取得
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報:' . print_r($dbFormData, true));

// post送信されていた場合
if (!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報:' . print_r($_POST, true));
  debug('FILES情報:' . print_r($_FILES, true));
  
  // 変数にユーザー情報を格納
  $name = $_POST['name'];
  $email = $_POST['email'];
  $sex = $_POST['sex'];
  $birth = $_POST['birth'];
  $tel = (!empty($_POST['tel'])) ? $_POST['tel'] : 0;
  $zip = (!empty($_POST['zip'])) ? $_POST['zip'] : 0;
  $addr = $_POST['addr'];
  $prof = $_POST['profile'];
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  
  // DBの情報と入力情報が異なる場合にバリデーションを行う
  if ($dbFormData['user_name'] !== $name){
    // 名前の最大文字数チェック
    validMaxLen($name, 'name');
  }
  if ($dbFormData['email'] !== $email){
    // emailの最大文字数チェック
    validMaxLen($email, 'email');
    // emailの形式チェック
    validEmail($email, 'email');
    // emailの未入力チェック
    validRequired($email, 'email');
    // emailの重複チェック
    if (empty($err_msg['email'])){
    validEmailDup($email, 'email');}
  }
  if ((int)$dbFormData['tel'] !== $tel){
    // TEL形式チェック
    validTel($tel, 'tel');
  }
  if ((int)$dbFormData['zip'] !== $zip){
    // 郵便番号形式チェック
    validZip($zip, 'zip');
  }
  if ($dbFormData['addr'] !== $addr){
    // 住所の最大文字数チェック
    validMaxLen($addr, 'addr');
  }
  
  if (empty($err_msg)){
    debug('バリデーションOKです。');
    // 例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET user_name = :name, email = :email, sex = :sex, tel = :tel, birthday = :birth, zip = :zip, addr = :addr, pic1 = :pic1, pic2 = :pic2, profile = :prof WHERE user_id = :u_id';
      $data = array(':name' => $name, ':email' => $email, ':sex' => $sex, ':tel' => $tel, ':birth' => $birth, ':zip' => $zip, ':addr' => $addr, ':pic1' => $pic1, ':pic2' => $pic2, ':prof' => $prof, ':u_id' => $_SESSION['user_id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      
      // クエリ成功の場合
      if ($stmt){
        $_SESSION['msg_success'] = SUC02;
        debug('マイページへ遷移します。');
        header("Location:mypage.php");
        exit();
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
  <div class="container">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <form action="" method="post" class="" enctype="multipart/form-data">
          <div class="area-msg">
            <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
          </div>

          <div class="row">
            <div class="col-md-6"><label for="user-name" class="<?php echo (!empty($err_msg['name'])) ? 'is-invalid' : ''; ?>">ユーザーネーム</label>
              <div class="input-group w-75">
                <input type="text" name="name" class="form-control <?php echo (!empty($err_msg['name'])) ? 'is-invalid' : ''; ?>" value="<?php echo sanitize(getFormData('user_name')); ?>" id="user-name" aria-describedby="basic-addon3">
              </div>
              <div class="area-msg mb-3">
                <?php if (!empty($err_msg['name'])) echo $err_msg['name']; ?>
              </div>
            </div>

            <div class="col-md-6">
              <label for="basic-email" class="<?php echo (!empty($err_msg['email'])) ? 'is-invalid' : ''; ?>">メールアドレス</label>
              <div class="input-group w-75">
                <input type="text" name="email" class="form-control <?php echo (!empty($err_msg['email'])) ? 'is-invalid' : ''; ?>" value="<?php echo sanitize(getFormData('email')); ?>" id="basic-email" aria-describedby="basic-addon3">
              </div>
              <div class="area-msg mb-3">
                <?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="">性別</div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sex" id="inlineRadio1" value="1" <?php echo ((int)$dbFormData['sex'] === 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="inlineRadio1">男性</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sex" id="inlineRadio2" value="2" <?php echo ((int)$dbFormData['sex'] === 2) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="inlineRadio2">女性</label>
              </div>
              <div class="area-msg mb-3">
                <?php if (!empty($err_msg['sex'])) echo $err_msg['sex']; ?>
              </div>
            </div>

            <div class="col-md-6">
              <label for="basic-birth">誕生日</label>
              <div class="input-group w-50">
                <input type="date" name="birth" value="<?php echo sanitize(getFormData('birthday')); ?>" class="form-control" id="basic-birth" aria-describedby="basic-addon3">
              </div>
              <div class="area-msg mb-3">
                <?php if (!empty($err_msg['birth'])) echo $err_msg['birth']; ?>
              </div>
            </div>
          </div>

          <label for="basic-tel" class="<?php echo (!empty($err_msg['tel'])) ? 'is-invalid' : ''; ?>">TEL</label>
          <div class="input-group w-50">
            <input type="text" name="tel" class="form-control" id="basic-tel" value="<?php echo sanitize(getFormData('tel')); ?>" aria-describedby="basic-addon3">
          </div>
          <div class="area-msg mb-3">
            <?php if (!empty($err_msg['tel'])) echo $err_msg['tel']; ?>
          </div>

          <label for="basic-zip" class="<?php echo (!empty($err_msg['zip'])) ? 'is-invalid' : ''; ?>">郵便番号</label>
          <div class="input-group w-50">
            <input type="text" name="zip" class="form-control <?php echo (!empty($err_msg['zip'])) ? 'is-invlid' : ''; ?>" value="<?php echo sanitize(getFormData('zip')); ?>" id="basic-zip" onkeyup="AjaxZip3.zip2addr(this, '', 'addr', 'addr');" value="" aria-describedby="basic-addon3">
          </div>
          <div class="area-msg mb-3">
            <?php if (!empty($err_msg['zip'])) echo $err_msg['zip']; ?>
          </div>

          <label for="basic-addr" class="<?php echo (!empty($err_msg['addr'])) ? 'is-invalid' : ''; ?>">住所</label>
          <div class="input-group">
            <input type="text" name="addr" class="form-control <?php echo (!empty($err_msg['addr'])) ? 'is-invalid' : ''; ?>" value="<?php echo sanitize(getFormData('addr')); ?>" id="basic-addr" aria-describedby="basic-addon3">
          </div>
          <div class="area-msg mb-4">
            <?php if (!empty($err_msg['addr'])) echo $err_msg['addr']; ?>
          </div>

          <div class="row mb-5">
            <div class="imgDrop-container col-lg-6">
              アバター画像
              <label class="area-drop avatar">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic1" class="input-file">
                <img src="<?php echo sanitize(getFormData('pic1')); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic1'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['pic1'])) echo $err_msg['pic1']; ?>
              </div>
            </div><!-- imgDrop-container -->

            <div class="imgDrop-container col-lg-6">
              プロフィール背景画像
              <label class="area-drop profEdit">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic2" class="input-file">
                <img src="<?php echo sanitize(getFormData('pic2')); ?>" alt="" class="prev-img">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['pic2'])) echo $err_msg['pic2']; ?>
              </div>
            </div><!-- imgDrop-container -->
          </div><!-- row -->

          <div class="prof-text">
            <label for="basic-prof" class="prof-label mx-auto <?php echo (!empty($err_msg['profile'])) ? 'is-invalid' : ''; ?>">プロフィール文</label>
            <div class="input-group text-center">
              <textarea name="profile" class="js-count-target mx-auto <?php echo (!empty($err_msg['profile'])) ? 'is-invalid' : ''; ?>" id="basic-prof" cols="60" rows="10"><?php echo sanitize(getFormData('profile')); ?></textarea>
            </div>
            <div class="prof-count js-count-target mx-auto"><span class="js-count-show">0</span>/300</div>
            <div class="area-msg mb-3">
              <?php if (!empty($err_msg['profile'])) echo $err_msg['profile']; ?>
            </div>
          </div>

          <input class="btn btn-lg btn-outline-primary mb-5 mx-auto d-block" type="submit" value="編集する">

        </form>
      </div><!-- col -->
    </div><!-- row -->
  </div><!-- container -->
  <?php require('footer.php'); ?>
