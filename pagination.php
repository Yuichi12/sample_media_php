<?php
// ページング
// $currentPageNum : 現在のページ
// $totalPageNum : 総ページ数
// $linnk : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数

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
?>
<div class="c-pagination">
  <ul class="c-pagination-list">
    <?php if ($currentPageNum != 1){ ?>
    <li class="c-list-item"><a href="?p=1">&lt;</a></li>
    <?php } ?>
    <?php for ($i = $minPageNum; $i <= $maxPageNum; $i++){ ?>
    <li class="c-item-list"><a href="?p=<?php echo $i; ?>">
        <?php echo $i; ?></a></li>
    <?php } ?>
    <?php if ($currentPageNum != $maxPageNum){ ?>
    <li class="c-list-item"><a href="?p=<?php echo $maxPageNum; ?>">&gt;</a></li>
    <?php } ?>
  </ul>
</div>
