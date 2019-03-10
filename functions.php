<?php
// ===========================================
// ログ
// ===========================================
// ログを取るか
ini_set('log_errors', 'on');
// ログの出力ファイルを指定
ini_set('error_log', 'php.log');

// ===========================================
// デバッグ
// ===========================================
// デバッグフラグ
$debug_flg = true;
// デバッグログ関数
function debug($str){
  global $debug_flg;
  if ($debug_flg){
    error_log('デバッグ:' . $str);
  }
}

// ===========================================
// セッション準備・セッション有効期限を延ばす
// ===========================================
// セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/appA/");
// セッションIDをアプリ毎に分けるためにセッションの名前を変える（セキュリティ対策にもなる）
session_name('appA');
// ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
// ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

// ===========================================
// 画面表示処理開始ログ吐き出し関数
// ===========================================
function debugLogStart(){
  debug('画面表示処理開始<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
  debug('セッションID:' . session_id());
  debug('セッション変数の中身:' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ:' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン期限日時タイムスタンプ' . print_r($_SESSION['login_date'] + $_SESSION['login_limit'], true));
  }
}

// ===========================================
// 定数
// ===========================================
// エラーメッセージを定数に設定
// 基本的なバリデーション
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '255文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
// ログイン時のバリデーション
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
// プロフィール編集時のバリデーション
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
// パスワード変更時のバリデーション
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');

define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');

// ===========================================
// グローバル変数
// ===========================================
// エラーメッセージ格納用の変数
$err_msg = array();

// ===========================================
// バリデーション関数
// ===========================================

// バリデーション関数（未入力チェック）
function validRequired($str, $key){
  if ($str === ''){ // 金額フォームなどを考えると数値の０はokにし、空文字はダメにする
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

// バリデーション関数（Email形式チェック）
function validEmail($str, $key){
  if (!preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

// バリデーション関数（Email重複チェック）
function validEmailDup($email){
  global $err_msg;
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
    if (!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch(exception $e){
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

// バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
  if ($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

// バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
  if (mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

// バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 255){
  if (mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

// バリデーション関数（半角チェック）
function validHalf($str, $key){
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

// 電話番号形式チェック
function validTel($str, $key){
  if (!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}

// 郵便番号形式チェック
function validZip($str, $key){
  if (!preg_match("/^\d{7}$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}

// 半角数字チェック
function validNumber($str, $key){
  if (!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg = MSG17;
  }
}

// 固定長チェック
function validLength($str, $key, $len = 8){
  if (mb_strlen($str) !== $len){
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}

// パスワードチェック（パスワードのバリデーションをまとめたもの）
function validPass($str, $key){
  // 半角英数字チェック
  validHalf($str, $key);
  // 最大文字数チェック
  validMaxLen($str, $key);
  // 最小文字数チェック
  validMinLen($str, $key);
}

// selectboxチェック
function validSelect($str, $key){
  if (!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}

// エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if (!$err_msg[$key]){
    return $err_msg[$key];
  }
}

// ===========================================
// ログイン認証
// ===========================================
function isLogin(){
  // ログインしている場合
  if (!empty($_SESSION['login_date'])){
    debug('ログイン認証:ログイン済みユーザーです。');
    
    // 現在日時が最終ログイン日時+有効期限を超えたいた場合
    if ($_SESSION['login_date'] + $_SESSION['login_limit'] < time()){
      debug('ログイン有効期限オーバーです。');
      
      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    } else{
      debug('ログイン有効期限内です。');
      return true;
    }
  } else{
    debug('未ログインユーザーです。');
    return false;
  }
}

// ===========================================
// データベース
// ===========================================
// DB接続関数
function dbConnect(){
  // DBへの接続準備
  $dsn = 'mysql:dbname=twitter_practice;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成(DBへ接続)
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

// SQl実行関数
function queryPost($dbh, $sql, $data){
  // クエリー作成
  $stmt = $dbh->prepare($sql);
  // プレースホルダに値をセットし、SQL文を実行
  if (!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL:' . print_r($stmt, true));
    global $err_msg;
    $err_msg['common'] = MSG07;
    return false;
  }
  debug('クエリ成功。');
  return $stmt;
}

// 対象のユーザーIDのユーザー情報を取得
function getUser($u_id){
  debug('ユーザー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    
    // クエリ結果のデータを１レコード返却
    if ($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else{
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}

// ユーザーIDとポストIDから対象の記事情報を取得（記事IDだけだと偽装される可能性があるのでユーザーIDと合わせて一意の記事を取得する）
function getPost($u_id, $p_id){
  debug('記事情報を取得します。');
  debug('ユーザーID:' . print_r($u_id, true));
  debug('記事ID:' . print_r($p_id, true));
  
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM posts WHERE user_id = :u_id AND post_id = :p_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    
    if ($stmt){
    // クエリ結果のデータを１レコード返却
    return $stmt->fetch(PDO::FETCH_ASSOC);
    } else{
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
    // エラーメッセージは表示させないので$err_msgには何も格納しない
    // $err_msg['common'] = MSG07;
  }
}

// 先頭レコードの値とカテゴリ、ソートの値を受け取ってそれを元に記事のリストを取得する
function getPostList($currentMinNum = 0, $category = 0, $sort = 1, $search = '', $listSpan = 5){
  debug('記事リストを取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 先ずは件数用のSQL文作成
    $sql = 'SELECT post_id, p.update_time FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.user_id';
    if (!empty($category)) $sql .= ' WHERE category_id = :c_id';
    if (!empty($search)){
      $search = '%'. $search . '%';
      if (!empty($category)){
        $sql .= ' u.user_name LIKE :search OR p.post_title LIKE :search OR p.post_content LIKE :search';
      } else{
      $sql .= ' WHERE u.user_name LIKE :search OR p.post_title LIKE :search OR p.post_content LIKE :search';
      }
    }
    if (!empty($sort)){
      switch ($sort){
        case 1:
          $sql .= ' ORDER BY update_time DESC';
          break;
        case 2:
          $sql .= ' ORDER BY update_time ASC';
          break;
      }
    }
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) $stmt->bindValue(':c_id', $category, PDO::PARAM_INT);
    if (!empty($search)) $stmt->bindValue(':search', $search, PDO::PARAM_STR);
    $stmt->execute();
//    $data = array(':c_id' => (int)$category, ':search' => $search);
//    // クエリ実行
//    $stmt = queryPost($dbh, $sql, $data);
    if (!$stmt){
      return false;
    }
    $rst['total'] = $stmt->rowCount(); // 総レコード数
    $rst['total_page'] = ceil($rst['total']/$listSpan); // 総ページ数
    
    // ページング用のSQL文作成
    $sql = 'SELECT p.post_id, p.post_title, p.post_content, p.user_id, p.category_id, p.comment_table, p.pic1, p.pic2, p.pic3, p.create_date, p.update_time, u.user_name FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.user_id';
    if (!empty($category)) $sql .= ' WHERE category_id = :c_id';
    if (!empty($search)){
      if (!empty($category)){
        $sql .= ' u.user_name LIKE :search OR p.post_title LIKE :search OR p.post_content LIKE :search';
      } else{
      $sql .= ' WHERE u.user_name LIKE :search OR p.post_title LIKE :search OR p.post_content LIKE :search';
      }
    }
    if (!empty($sort)){
      switch ($sort){
        case 1:
          $sql .= ' ORDER BY update_time DESC';
          break;
        case 2:
          $sql .= ' ORDER BY update_time ASC';
          break;
      }
    }
    $sql .= ' LIMIT :listSpan OFFSET :currentMinNum';
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) $stmt->bindValue(':c_id', (int)$category, PDO::PARAM_INT);
    if (!empty($search)) $stmt->bindValue(':search', $search, PDO::PARAM_STR);
    $stmt->bindValue(':listSpan', (int)$listSpan, PDO::PARAM_INT);
    $stmt->bindValue(':currentMinNum', (int)$currentMinNum, PDO::PARAM_INT);
    $stmt->execute();
    // $sql .= ' LIMIT '.$listSpan.' OFFSET '.$currentMinNum;
    // $data = array(':c_id' => $category);
    // クエリ実行
    //$stmt = queryPost($dbh, $sql, $data);
    if (!$stmt){
      debug('クエリに失敗しました。');
      debug('失敗したSQL文:' . print_r($stmt, true));
      return false;
    } else{
      // クエリ結果の全レコードを格納
      debug('クエリ成功。');
      debug('クエリの中身:' . print_r($stmt, true));
      $rst['data'] = $stmt->fetchAll();
      debug('レコードの中身:' . print_r($rst,true));
      return $rst;
    }
    
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getSearchList($key_word = ''){
  debug('検索にヒットした記事リストを取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // まずは件数用のSQL文作成
    $sql = 'SELECT post_id FROM posts AS p LEFT JOIN users AS u ON p.user_id = u_user_id LEFT JOIN categories AS c ON p.category_id = c.category_id WHERE u.user_name LIKE %:search% OR p.post_title LIKE %:search% OR p.post_content LIKE %:search%';
    
    $data = array(':search' => $search);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if (!$stmt){
      return false;
    }
    $rst['total'] = $stmt->rowCount(); // 総レコード数
    $rst['total_page'] = ceil($rst['total']/$listSpan); // 総ページ数
    
    // ページング用のSQL文作成
    $sql = 'SELECT p.post_id, p.post_title, p.post_content, p.user_id, p.category_id, p.comment_table, p.pic1, p.pic2, p.pic3, p.create_date, p.update_time, u.user_name FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.user_id';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':search', $search, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt){
      debug('クエリ成功です。');
      // 取得した全レコードを返却
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else{
      debug('クエリに失敗しました。');
      debug('失敗したSQL:' . print_r($stmt, true));
      return false;
    }
} catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}

// ユーザー名、カテゴリー名も入った詳細な記事情報を取得する
function getPostDetail($p_id){
  debug('詳細な記事情報を取得します。');
  debug('記事ID:' . $p_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT p.post_id, p.post_title, p.post_content, p.user_id, p.category_id, p.comment_table, p.pic1, p.pic2, p.pic3, p.create_date, p.update_time, u.user_name, c.category_name FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.user_id LEFT JOIN categories AS c ON p.category_id = c.category_id WHERE p.post_id = :p_id AND p.delete_flg = 0 AND u.delete_flg = 0 AND c.delete_flg = 0';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':p_id', $p_id, PDO::PARAM_INT);
    $stmt->execute();
    // $data = array(':p_id' => $p_id);
    // $stmt = queryPost($dbh, $sql, $data);
    if ($stmt){
      debug('クエリ成功です。');
      // クエリ結果のデータを１レコード返却
      $rst = $stmt->fetch(PDO::FETCH_ASSOC);
      return $rst;
    } else{
      debug('クエリが失敗しました。');
      debug('失敗したSQL:' . print_r($stmt, true));
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}

// 指定のユーザーの記事一覧を取得する
function getUserPostList($currentMinNum, $u_id, $listSpan = 5){
  debug('対象のユーザーの記事一覧情報を取得します');
  debug('カレントページの先頭レコード:' . $currentMinNum);
  debug('ユーザーID:' . $u_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // まずは件数用のSQL文作成
    $sql = 'SELECT post_id FROM posts WHERE user_id = :u_id AND delete_flg = 0';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt){
      debug('クエリ成功です。');
      $rst['total'] = $stmt->rowCount();
      $rst['total_page'] = ceil($rst['total']/$listSpan);
    } else{
      debug('クエリに失敗しました。');
      debug('クエリに失敗したSQL:' . print_r($stmt, true));
      return false;
    }
    // ページネーション用のSQL文作成
    $sql = 'SELECT p.post_id, p.post_title, p.post_content, p.pic1, p.update_time, u.user_id, u.user_name FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.user_id WHERE p.user_id = :u_id AND p.delete_flg = 0 LIMIT :listSpan OFFSET :currentMinNum';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->bindValue(':listSpan', $listSpan, PDO::PARAM_INT);
    $stmt->bindValue(':currentMinNum', $currentMinNum, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt){
      debug('クエリ成功です');
      // クエリ結果の値を全レコード返却
      debug('クエリの中身:'. print_r($stmt,true));
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else{
      debug('クエリに失敗しました。');
      debug('失敗したSQL文:' . print_r($stmt, true));
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getMsgsAndBord($m_id){
  debug('msg情報を取得します。');
  debug('掲示板ID:' . $m_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT b.bord_id, b.user1_id, b.user2_id, b.create_date, m.message_id, m.sender_id, m.recipient_id, m.send_date, m.message FROM bords AS b LEFT JOIN messages AS m ON b.bord_id = m.bord_id WHERE b.bord_id = :b_id AND b.delete_flg = 0 AND (m.delete_flg = 0 OR m.delete_flg IS NULL)';
    $data = array(':b_id' => $m_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    
    if ($stmt){
      // 取得した全レコードを返却
      return $stmt->fetchAll();
    } else{
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}

// 対象のユーザーの掲示板リストとメッセージと相手のユーザー情報を取得する
function getUserMsgsAndBord($u_id, $currentMinNum = 0, $listSpan = 5){
  debug('対象のユーザーの掲示板リストを取得します。');
  debug('ユーザーID:' . $u_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // まず掲示板レコード取得
    $sql = 'SELECT bord_id, user1_id, user2_id, create_date, update_time FROM bords WHERE (user1_id = :u_id OR user2_id = :u_id) AND delete_flg = 0';
    $sql .= ' LIMIT :listSpan OFFSET :currentMinNum';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->bindValue(':listSpan', $listSpan, PDO::PARAM_INT);
    $stmt->bindValue(':currentMinNum', $currentMinNum, PDO::PARAM_INT);
    $stmt->execute();

    // クエリ成功の場合
    if ($stmt){
      $rst = $stmt->fetchAll();
    } else{
      return false;
    }
    // メッセージを取得
    if (!empty($rst)){
      foreach ($rst as $key => $val){
        $sql = 'SELECT * FROM messages WHERE bord_id = :b_id AND delete_flg = 0';
        $data = array(':b_id' => $val['bord_id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt){
          $rst[$key]['msg'] = $stmt->fetchAll();
        } else{
          return false;
        }
      }
    }
    // 相手のユーザー情報を取得
    if (!empty($rst)){
      foreach ($rst as $key => $val){
        $dealUserData[] = $val['user1_id'];
        $dealUserData[] = $val['user2_id'];
        debug('$dealUserDataの中身:'. $u_id . print_r($dealUserData, true)); // 一時的なデバッグ
        if (($key_id = array_search($u_id, $dealUserData)) !== false){
          debug('$key_idの中身:' . print_r($key_id, true)); // 一時的なデバッグ
          unset($dealUserData[$key_id]);
        }
        $partnerUserId = array_shift($dealUserData);
        $rst[$key]['partner_user'] = getUser($partnerUserId);
      }
    }
    return $rst;
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}


function getCategory(){
  debug('カテゴリー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM categories';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    
    if ($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else{
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}
// ===========================================
// メール送信
// ===========================================
function sendMail($from, $to, $subject, $comment){
  if (!empty($to) && !empty($subject) && !empty($comment)){
    // 文字化けしないように設定（お決まりパターン）
    mb_language("Japanese"); // 現在使っている言語を設定する
    mb_internal_encoding("UTF-8"); // 内部の日本語をどうエンコーディング（機会が分かる言葉へ変換）するかを設定
    
    // メールを送信（送信結果はtrueかfalseで返ってくる）
    $result = mb_send_mail($to, $subject, $comment, "From:".$from);
    // 送信結果を判定
    if ($result) {
      debug('メールを送信しました。');
    } else{
      debug('エラー発生:メールの送信に失敗しました。');
    }
  }
}

// ===========================================
// その他
// ===========================================
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str, ENT_QUOTES);
}
// フォーム入力保持
function getFormData($key, $flg = false){
  if ($flg){
    $method = $_GET;
  } else{
    $method = $_POST;
  }
  global $dbFormData;
  // ユーザーデータがある場合
  if (!empty($dbFormData[$key])){
    // フォームのエラーがある場合
    if (!empty($err_msg[$key])){
      // POSTにデータがある場合（もちろん普通はある）
      if (isset($method[$key])){
        return $method[$key];
      } else{
        // ない場合（基本ありえない）はDBの情報を表示
        return $dbFormData[$key];
      }
    } else {
      // POSTにデータがあり、DBの情報と違う場合
      if (isset($method[$key]) && ($method[$key] !== $dbFormData[$key])){
        return $method[$key];
      } else{
        // 変更がない場合
        return $dbFormData[$key];
      }
    }
  } else{
    // ユーザーデータがない場合
    if (isset($method[$key])){
      return $method[$key];
    }
  }
}

// sessionを１回だけ取得できる
function getSessionFlash($key){
  if (!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

// 認証キー生成
function makeRandKey($length = 8){
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; $i++) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}

// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報:' . print_r($file,true));
  
  if (isset($file['error']) && is_int($file['error'])){
    // 例外処理
    try {
      // バリデーション
      // $file['error']の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      // 「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている
      switch ($file['error']){
        case UPLOAD_ERR_OK: // OK
          break;
        case UPLOAD_ERR_NO_FILE: // ファイル未選択の場合
          throw new runtimeexception('ファイルが選択されていません。');
        case UPLOAD_ERR_INI_SIZE: // php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズを超過した場合
          throw new runtimeexception('ファイルサイズが大きすぎます。');
        default: // その他の場合
          throw new runtimeexception(('その他のエラーが発生しました。'));
      }
      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
        throw new runtimeexception('画像形式が未対応です。');
      }
      
      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { // ファイルを移動する
        throw new runtimeexception('ファイル保存時にエラーが発生しました。');
      }
      // 保存したファイルのパスのパーミッション（権限）を変更する
      chmod($path, 0644);
      
      debug('ファイルは正常にアップロードされました。');
      debug('ファイルパス:' .$path);
      return $path;
      
    } catch (RuntimeException $e){
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

// ページング
// $currentPageNum : 現在のページ
// $totalPageNum : 総ページ数
// $linnk : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ、かつ総ページ数が表示項目数以上なら、左にリンクを４個出す
  if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  }
  // 現在のページが、総ページ数の1ページ前、かつ総ページ数が表示項目数以上なら、左にリンク３個、右に１つ出す
  elseif ($currentPageNum === $totalPageNum - 1 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  }
  // 現在のページが、２ページ、かつ総ページ数が表示項目数以上なら、左にリンク１個、右に３個出す
  elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  }
  // 現在のページが、1ページ、かつ総ページ数が表示項目数以上なら、右にリンク４個出す
  elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $currentPageNum + 4;
  }
  // 総ページ数が表示項目数より少ない場合は、総ページ数をmax、ループのminを１に設定
  elseif ($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  }
  // それ以外は左にリンク２個、右に２個出す
   else {
     $minPageNum = $currentPageNum - 2;
     $maxPageNum = $currentPageNum + 2;
   }
 echo '<div class="c-pagination">';
  echo '<ul class="c-pagination-list">';
     if ($currentPageNum != 1){
    echo '<li class="c-list-item"><a href="?p=1">&lt;</a></li>';
     }
    for ($i = $minPageNum; $i <= $maxPageNum; $i++){
    echo '<li class="c-list-item ';
      if ($currentPageNum == $i){ echo 'is-active'; }
      echo '"><a href="?p='. $i.$link.'">'.$i.'</a></li>';
    }
    if ($currentPageNum != $maxPageNum){
    echo '<li class="c-list-item"><a href="?p='. $maxPageNum .'">&gt;</a></li>';
    }
  echo '</ul>';
echo '</div>';
}

// ページネーション
function pagination2($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが１、かつ総ページ数が表示項目数以上の場合、右にリンクを４個出す
  if ($currentPageNum == 1 && $totalPageNum >= $pageColNum){
    $minPageNum = 1;
    $maxPageNum = 5;
  }
  // 現在のページが２、かつ総ページ数が表示項目数以上の場合、左にリンクを１個、右に3個出す
  elseif($currentPageNum == 2 && $totalPageNum >= $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $currentPageNum + 3;
  }
  // 現在のページが総ページ数と同じ、かつ総ページ数が表示項目数以上の場合、左にリンクを４個出す
  elseif($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  }
  // 現在のページが総ページ数の１つ前、かつ総ページ数が表示項目数以上の場合、左にリンク３個、右に１つ出す
  elseif($currentPageNum == $totalPageNum -1 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  }
  // 総ページ数が表示項目数より少ない場合は最小を１に最大を総ページ数にする
  elseif($totalPageNum <= $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  }
  // それ以外の場合は左にリンク２個、右に２個出す
  else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }
  $pageNum = array('minPageNum' => $minPageNum, 'maxPageNum' => $maxPageNum);
  debug('$pageNumの中身:' . print_r($pageNum, true));
  return $pageNum;
}

// 画像表示用関数
function showImg($path){
  if (empty($path)){
    return 'dist/img/no-img.jpg';
  } else{
    return $path;
  }
}

// GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($del_key = array()){
  if (!empty($_GET)){
    $str = '?';
    foreach ($_GET as $key => $val){
      if (!in_array($key, $del_key, true)){
        $str .= $key . '=' . $val . '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}
?>
