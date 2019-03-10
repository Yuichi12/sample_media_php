<?php
// 共通変数・関数ファイルを読み込む
require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ログアウトページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

debug('ログアウトします。');
// セッションを削除（ログアウトする）
session_destroy();
debug('ログインページへ遷移します。');
// ログインページへ
header("Location:login.php");
exit();
